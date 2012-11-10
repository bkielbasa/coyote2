<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Topic_View_Model extends Model
{
	protected $name = 'topic_view';
	protected $primary = 'topic_id';

	public function update($topicId)
	{
		$sql = "INSERT INTO topic_view VALUES($topicId, 1) ON DUPLICATE KEY UPDATE topic_view = topic_view + 1";
		$this->db->query($sql);
	}
}

class Topic_Marking_Model extends Model
{
	protected $name = 'topic_marking';

	public function getReadTopics($topicId)
	{
		$query = $this->select('user.user_id, user_name, session_id, mark_time, user_ip, user_lastvisit')
					  ->leftJoin('user', 'user.user_id = topic_marking.user_id')
					  ->leftJoin('session', 'session_user_id = user.user_id')
					  ->where("topic_id = $topicId");

		return $query->fetchAll();
	}
}

class Topic_Model extends Model
{
	const NORMAL			=		1;
	const ANNOUCEMENT		=		2;
	const STICKY			=		3;

	protected $name = 'topic';
	protected $prefix = 'topic_';
	protected $primary = 'topic_id';

	public $view;
	public $marking;

	function __construct()
	{
		$this->view = new Topic_View_Model;
		$this->marking = new Topic_Marking_Model;
	}

	public function fetch($where = null, $order = null, $count = null, $limit = null)
	{
		$query = $this->db->select('
									topic.*,
									page.page_subject AS topic_subject,
									location.location_text AS topic_path,
									u1.user_name AS u1_name,
									u2.user_name AS u2_name,
									p1.post_user AS p1_user,
									p1.post_username AS p1_username,
									p1.post_time AS p1_time,
									p2.post_user AS p2_user,
									p2.post_username AS p2_username,
									p2.post_time AS p2_time,
									parent.page_subject AS forum_subject
									');

		$query->from('topic');

		$query->innerJoin('page', 'page_id = topic_page');
		$query->innerJoin('location', 'location.location_page = page_id');
		$query->innerJoin('post AS p1', 'p1.post_id = topic_first_post_id');
		$query->innerJoin('post AS p2', 'p2.post_id = topic_last_post_id');
		$query->leftJoin('user u1', 'u1.user_id = p1.post_user');
		$query->leftJoin('user u2', 'u2.user_id = p2.post_user');
		$query->leftJoin('forum', 'forum.forum_id = topic_moved_id');
		$query->leftJoin('page AS parent', 'parent.page_id = forum.forum_page');

		if (User::$id > User::ANONYMOUS)
		{
			$query->select('t_marking.mark_time AS topic_mark, f_marking.mark_time AS forum_mark');

			$query->leftJoin('topic_marking AS t_marking', 't_marking.user_id = ' . User::$id . ' AND t_marking.topic_id = topic.topic_id');
			$query->leftJoin('forum_marking AS f_marking', 'f_marking.user_id = ' . User::$id . ' AND f_marking.forum_id = topic.topic_forum');
		}

		if ($where)
		{
			if (is_string($where))
			{
				$query->where($where);
			}
			elseif (is_array($where))
			{
				foreach ($where as $condition)
				{
					$query->where($condition);
				}
			}
		}

		if ($order)
		{
			$query->order($order);
		}
		if ($count || $limit)
		{
			$query->limit($count, $limit);
		}

		return $query;
	}

	/**
	 * Wyswietla nowe tematy z danego forum
	 * Metoda wykorzystywana do wyswietlania naglowkow Atom
	 * @param int $forumId		ID forum
	 * @param int $limit		Limit wyswietlanych naglowkow
	 * @return object
	 */
	public function getRecentlyTopics($forumId, $limit)
	{
		$query = $this->db->select('topic.*,
									page.page_subject AS topic_subject,
									location_text AS topic_path,
									post_username,
									post_user,
									post_time,
									post_edit_time,
									text_content AS post_text,
									user_name'
									);

		$query->from('topic');

		if ($forumId)
		{
			$query->where("topic_forum = $forumId");
		}
		else
		{
			$user = &$this->load->model('user');
			$query->where('topic_page IN(SELECT pg.page_id FROM page_group pg WHERE pg.group_id IN(' . implode(',', $user->getGroups()) . '))');

			$query->select('fp.page_subject AS forum_subject');
			$query->leftJoin('forum', 'forum_id = topic_forum');
			$query->leftJoin('page AS fp', 'fp.page_id = forum_page');
		}

		$query->innerJoin('page', 'page.page_id = topic_page');
		$query->innerJoin('location', 'location_page = page.page_id');
		$query->innerJoin('post', 'post_id = topic_first_post_id');
		$query->innerJoin('post_text', 'text_id = post_text');
		$query->leftJoin('user', 'user_id = post_user');
		$query->limit($limit);
		$query->order('topic_id DESC');

		return $query;
	}

	public function getSearchData($pageIds)
	{
		if (!is_array($pageIds))
		{
			$pageIds = array($pageIds);
		}

		$query = $this->fetch('topic_page IN(' . implode(',', $pageIds) . ')');
		$query = $query->select('fp.page_subject AS forum_subject, fl.location_text AS forum_location')
						->innerJoin('forum AS f', 'f.forum_id = topic.topic_forum')
						->innerJoin('page AS fp', 'fp.page_id = f.forum_page')
						->innerJoin('location AS fl', 'fl.location_page = fp.page_id')
						->order('FIND_IN_SET(topic_page, "' . implode(',', $pageIds) . '") ASC')
						->get();

		return $this->applyTopicMark($query);
	}

	private function applyTopicMark($query)
	{
		$result = array();
		$post = &$this->load->model('post');

		if (User::$id == User::ANONYMOUS)
		{
			$tracking = unserialize($this->input->cookie->tracking);

			if (!isset($tracking['l']))
			{
				$tracking['l'] = base_convert(time(), 10, 36);
				$this->output->setCookie('tracking', serialize($tracking), time() + 31536000);
			}

			$lastMark = base_convert($tracking['l'], 36, 10);
		}

		foreach ($query as $row)
		{
			if (User::$id == User::ANONYMOUS)
			{
				$topicId = base_convert($row['topic_id'], 10, 36);
				$row['topic_mark'] = isset($tracking['t'][$topicId]) ? (int) base_convert($tracking['t'][$topicId], 36, 10) : 0;

				if (isset($tracking['f'][$row['topic_forum']]))
				{
					$lastMark = base_convert($tracking['f'][$row['topic_forum']], 36, 10);
				}
			}
			else
			{
				$lastMark = isset($row['forum_mark']) ? $row['forum_mark'] : User::data('regdate');
			}
			/*
			 * Jezeli data napisania ostatniego posta jest pozniejsza
			 * niz data odznaczenia forum jako przeczytanego...
			 * ORAZ
			 * data napisania ostatniego postu jest pozniejsza niz data
			 * ostatniego "czytania" tematu...
			 * ODZNACZ JAKO NOWY
			 */
			$row['topic_unread'] = $row['p2_time'] > $lastMark && $row['p2_time'] > $row['topic_mark'];

			$result[$row['topic_id']] = $row;
		}

		return $result;
	}

	public function isUnread($topicId)
	{
		$topicId = (int) $topicId;

		if (User::$id > User::ANONYMOUS)
		{
			$markTime = $this->db->select('mark_time')->where("topic_id = $topicId AND user_id = " . User::$id)->get('topic_marking')->fetchField('mark_time');
		}
		else
		{
			$tracking = unserialize($this->input->cookie('tracking'));

			if (!isset($tracking['l']))
			{
				$tracking['l'] = base_convert(time(), 10, 36);
				$this->output->setCookie('tracking', serialize($tracking), time() + 31536000);
			}
			$topicId = base_convert($topicId, 10, 36);

			$markTime = isset($tracking['t'][$topicId]) ? (int) base_convert($tracking['t'][$topicId], 36, 10) : 0;
		}

		return $markTime;
	}

	public function markRead($topicId, $forumId, $markTime)
	{
		$forum = &$this->load->model('forum');
		$forumMarkTime = $forum->isUnread($forumId);

		if (($topicMarkTime = $this->isUnread($topicId)) < $markTime
			&& $forumMarkTime < $markTime)
		{
			if (User::$id > User::ANONYMOUS)
			{
				$sql = "INSERT INTO topic_marking (forum_id, topic_id, user_id, mark_time) VALUES($forumId, $topicId, " . User::$id . ", $markTime) ON DUPLICATE KEY UPDATE mark_time = $markTime, forum_id = $forumId";
				$this->db->query($sql);
			}
			else
			{
				$topicId = base_convert($topicId, 10, 36);

				$tracking = unserialize($this->input->cookie('tracking'));
				$tracking['t'][$topicId] = base_convert($markTime, 10, 36);
				$tracking['tf'][$forumId][$topicId] = true;

				$serialize = serialize($tracking);
				if (strlen($serialize) >= 1900)
				{
					$minValue = min($tracking['t']);

					if ($minValue)
					{
						unset($tracking['t'][array_search($minValue, $tracking['t'])]);
					}

					$serialize = serialize($tracking);
				}

				$this->output->setCookie('tracking', $serialize, time() + 31536000);
			}
			$unread = true;

			if ($forumMarkTime < $markTime)
			{
				$post = &$this->load->model('post');
				$unread = $post->isUnread($forumId, $forumMarkTime);
			}

			if (!$unread)
			{
				$forum->markRead($forumId);
			}
		}

		return ($topicMarkTime ? $topicMarkTime : $forumMarkTime);
	}

	public static function getStatus(&$row)
	{
		if ($row['topic_announcement'])
		{
			$class = 'topic-icon-announcement';
		}
		elseif ($row['topic_sticky'])
		{
			$class = 'topic-icon-sticky';
		}
		else
		{
			if ($row['topic_lock'])
			{
				$class = 'topic-icon-lock';
			}
			else
			{
				$class = 'topic-icon-normal';
			}
		}

		return $row['topic_unread'] ? ($class . '-new unread') : $class;
	}

	public function getNextId()
	{
		$sql = 'SHOW TABLE STATUS LIKE ?';
		$query = $this->db->query($sql, $this->name);

		return $query->fetchField('Auto_increment');
	}

	public function delete($ids)
	{
		if (!is_array($ids))
		{
			$ids = array($ids);
		}

		$postsId = $this->db->select('post_id')->from('post')->in('post_topic', $ids)->fetchCol();
		$query = $this->db->select('attachment_file')->from('post_attachment')->in('attachment_post', $postsId)->get();

		foreach ($query as $row)
		{
			@unlink('store/forum/' . $row['attachment_file']);
		}

		$post = &$this->getModel('post');
		$post->solr->delete($postsId); // usuniecice postow z indeksu solr

		/**
		 * Triggery w bazie danych dokonaja niezbednych operacji po usunieciu
		 * tematu
		 */
		parent::delete('topic_id IN(' . implode(',', $ids) . ')');
	}

	/**
	 * @param int $topicId		ID tematu
	 * @param int $pageIdFrom	ID strony do ktorej przypisany jest temat
	 * @param int $forumId		ID forum do ktorego ma byc przeniesiony temat
	 */
	public function move($topicId, $pageIdFrom, $forumId)
	{
		$page = &$this->load->model('page');

		$pageIdTo   = $this->db->select('forum_page')
							   ->from('forum')
							   ->where("forum_id = $forumId")
							   ->fetchField('forum_page');

		/*
		 * Odpowiedni trigger dokona przeniesienia postow oraz uaktualnienia
		 * licznika postow/tematow na forach
		 */
		$this->update(array('topic_forum' => $forumId), "topic_id = $topicId");
		$page->move($pageIdFrom, $pageIdTo)->free();

		$post = &$this->getModel('post');
		$post->solr->indexByTopic($topicId);
	}
}
?>