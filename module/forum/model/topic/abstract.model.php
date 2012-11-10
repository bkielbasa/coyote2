<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

abstract class Topic_Abstract_Model extends Model
{
	protected $forumId;
	protected $topicIds;
	protected $tags;
	protected $sort;
	protected $offset;
	protected $limit;
	protected $userId;
	protected $omitSticky = false;
	protected $omitNotAllowed = false;
	protected $totalItems;

	public function setForumId($forumId)
	{
		$this->forumId = (int) $forumId;
	}

	public function getForumId()
	{
		return $this->forumId;
	}

	public function setTopicIds($topicIds)
	{
		$this->topicIds = $topicIds;
	}

	public function getTopicIds()
	{
		return $this->topicIds;
	}

	public function setTags($tags)
	{
		$this->tags = $this->load->model('tag')->filter($tags);
	}

	public function getTags()
	{
		return $this->tags;
	}

	public function setSort($sort)
	{
		$this->sort = $sort;
	}

	public function getSort()
	{
		return $this->sort;
	}

	public function setOffset($offset)
	{
		$this->offset = (int) $offset;
	}

	public function getOffset()
	{
		return $this->offset;
	}

	public function setLimit($limit)
	{
		$this->limit = (int) $limit;
	}

	public function getLimit()
	{
		return $this->limit;
	}

	public function setUserId($userId)
	{
		$this->userId = (int) $userId;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function setOmitSticky($flag)
	{
		$this->omitSticky = (bool) $flag;
	}

	public function getOmitSticky()
	{
		return $this->omitSticky;
	}

	public function setOmitNotAllowed($flag)
	{
		$this->omitNotAllowed = (bool) $flag;
	}

	public function getOmitNotAllowed()
	{
		return $this->omitNotAllowed;
	}

	public function fetch($topicIds = array(), $where = null, $order = null)
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
									p2.post_id AS p2_id,
									p_move.page_subject AS forum_moved_subject
									');

		$query->from('topic');

		$query->innerJoin('page', 'page_id = topic_page');
		$query->innerJoin('location', 'location.location_page = page_id');
		$query->innerJoin('post AS p1', 'p1.post_id = topic_first_post_id');
		$query->innerJoin('post AS p2', 'p2.post_id = topic_last_post_id');
		$query->leftJoin('user u1', 'u1.user_id = p1.post_user');
		$query->leftJoin('user u2', 'u2.user_id = p2.post_user');
		$query->leftJoin('forum AS f_move', 'f_move.forum_id = topic_moved_id');
		$query->leftJoin('page AS p_move', 'p_move.page_id = f_move.forum_page');

		if (!$this->getForumId())
		{
			$query->select('p_parent.page_subject AS forum_subject, l_parent.location_text AS forum_location');

			$query->leftJoin('forum AS f_parent', 'f_parent.forum_id = topic_forum');
			$query->leftJoin('page AS p_parent', 'p_parent.page_id = f_parent.forum_page');
			$query->leftJoin('location AS l_parent', 'l_parent.location_page = p_parent.page_id');
		}

		if (User::$id > User::ANONYMOUS)
		{
			$query->select('t_marking.mark_time AS topic_mark, f_marking.mark_time AS forum_mark');

			$query->leftJoin('topic_marking AS t_marking', 't_marking.user_id = ' . User::$id . ' AND t_marking.topic_id = topic.topic_id');
			$query->leftJoin('forum_marking AS f_marking', 'f_marking.user_id = ' . User::$id . ' AND f_marking.forum_id = topic.topic_forum');
		}
		$query->in('topic.topic_id', $topicIds);

		if ($where)
		{
			$query->where($where);
		}

		if ($order)
		{
			$query->order($order);
		}

		return $query;
	}

	public function getTotalItems()
	{
		return $this->totalItems;
	}

	protected function getTotalItemsFromCache($query)
	{
		$sql = 'sql_' . $this->module->getId('forum') . '_' . md5((string) $query);
		if (!isset($this->cache->$sql))
		{
			$this->cache->save($sql, ($value = $query->fetchField('COUNT(*)')));
		}
		else
		{
			$value = $this->cache->load($sql);
			$query->reset();
		}

		return $value;
	}

	protected function applyTopicMark($query)
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

	protected function saveTopicViewMode($viewMode)
	{
		$forum = &$this->getModel('forum');
		$forum->setting->setTopicViewMode($viewMode);
		$forum->setting->save();
	}

	abstract public function load();

}
?>