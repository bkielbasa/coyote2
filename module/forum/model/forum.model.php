<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Forum_Setting_Model extends Model
{
	private $setting = array();

	function __construct()
	{
		$this->setting = @unserialize($this->input->cookie('setting'));
	}

	public function setOrder($forumId, $order)
	{
		$this->setting[$forumId]['order'] = $order;
	}

	public function getOrder($forumId)
	{
		return isset($this->setting[$forumId]['order']) ? $this->setting[$forumId]['order'] : null;
	}

	public function setSort($forumId, $sort)
	{
		$this->setting[$forumId]['sort'] = $sort;
	}

	public function getSort($forumId)
	{
		return isset($this->setting[$forumId]['sort']) ? $this->setting[$forumId]['sort'] : null;
	}

	public function setPostSort($postSort)
	{
		$this->setting['postSort'] = $postSort;
	}

	public function getPostSort()
	{
		return isset($this->setting['postSort']) ? $this->setting['postSort'] : null;
	}

	public function setTopicsPerPage($topicsPerPage)
	{
		$this->setting['topicsPerPage'] = $topicsPerPage;
	}

	public function getTopicsPerPage()
	{
		return isset($this->setting['topicsPerPage']) ? $this->setting['topicsPerPage'] : null;
	}

	public function setPostsPerPage($postsPerPage)
	{
		$this->setting['postsPerPage'] = $postsPerPage;
	}

	public function getPostsPerPage()
	{
		return isset($this->setting['postsPerPage']) ? $this->setting['postsPerPage'] : null;
	}

	public function setTopicViewMode($topicViewMode)
	{
		$this->setting['topicViewMode'] = $topicViewMode;
	}

	public function getTopicViewMode()
	{
		return isset($this->setting['topicViewMode']) ? $this->setting['topicViewMode'] : null;
	}

	public function setForumViewMode($forumViewMode)
	{
		$this->setting['forumViewMode'] = $forumViewMode;
	}

	public function getForumViewMode()
	{
		return isset($this->setting['forumViewMode']) ? $this->setting['forumViewMode'] : null;
	}

	public function setUserTags($userTags)
	{
		$tag = &$this->load->model('tag');
		$this->setting['userTags'] = implode(', ', array_unique(explode(' ', $tag->filter($userTags))));
	}

	public function getUserTags()
	{
		return isset($this->setting['userTags']) ? $this->setting['userTags'] : null;
	}

	public function setForumVisibility($forumId, $flag)
	{
		if ($flag)
		{
			unset($this->setting[$forumId]['visibility']);
		}
		else
		{
			$this->setting[$forumId]['visibility'] = (bool) $flag;
		}
	}

	public function getForumVisibility($forumId)
	{
		return !isset($this->setting[$forumId]['visibility']);
	}

	public function save()
	{
		if (!headers_sent())
		{
			$this->output->setCookie('setting', serialize($this->setting), time() + Time::YEAR);
		}
	}
}

class Forum_Marking_Model extends Model
{
	protected $name = 'forum_marking';

	public function getReadForums($forumId, $postTime = null)
	{
		$query = $this->select('user.user_id, user_name, session_id, mark_time, user_ip, user_lastvisit')
					  ->leftJoin('user', 'user.user_id = forum_marking.user_id')
					  ->leftJoin('session', 'session_user_id = user.user_id')
					  ->where("forum_id = $forumId");

		if ($postTime)
		{
			$query->where('mark_time >= ' . $postTime);
		}

		return $query->fetchAll();
	}
}

class Forum_Auth_Model extends Model
{
	protected $name = 'forum_auth';
	protected $prefix = 'auth_';

	private $groupAuth = array();
	private $userAuth = array();

	public function getOptions()
	{
		$result = array();

		$auth = &$this->load->model('auth');
		foreach ($auth->getOptions() as $opt_id => $row)
		{
			if (strpos($row['option_text'], 'f_') !== false)
			{
				$result[$opt_id] = $row;
			}
		}

		return $result;
	}

	public function getGroupAuth($forumId, $groupId)
	{
		if (!$this->groupAuth)
		{
			$query = parent::fetch('auth_forum = ' . $forumId . ' AND auth_group = ' . $groupId);
			while ($row = $query->fetchAssoc())
			{
				$this->groupAuth[$row['auth_option']] = $row['auth_value'];
			}
		}

		return $this->groupAuth;
	}

	public function setOption($forumId, $groupId, $optionId, $value)
	{
		$optionsData = $this->getGroupAuth($forumId, $groupId);

		$data = array(
			'auth_forum'			=> $forumId,
			'auth_group'			=> $groupId,
			'auth_option'			=> $optionId,
			'auth_value'			=> (bool)$value
		);
		if (!isset($optionsData[$optionId]))
		{
			$this->insert($data);
		}
		else
		{
			$this->update($data, "auth_forum = $forumId AND auth_group = $groupId AND auth_option = $optionId");
		}
	}

	public function getUserAuth($userId)
	{
		if (!$this->userAuth)
		{
			$sql = "SELECT auth_value,
						   auth_forum,
						   option_text
					FROM forum_auth, auth_group, auth_option
					WHERE user_id = $userId
							AND auth_group = group_id
								AND option_id = auth_option
					ORDER BY auth_value";
			$query = $this->db->query($sql);

			foreach ($query as $row)
			{
				$this->userAuth[$row['auth_forum']][$row['option_text']] = $row['auth_value'];
			}
		}

		return $this->userAuth;
	}

	public function get($option, $forumId = 0)
	{
		if ($forumId)
		{
			$auth = $this->getUserAuth(User::$id);
			return isset($auth[$forumId][$option]) ? $auth[$forumId][$option] : Auth::get($option);
		}
		else
		{
			return Auth::get($option);
		}
	}
}

class Forum_Reason_Model extends Model
{
	protected $name = 'forum_reason';
	protected $primary = 'reason_id';

	public function getReasons()
	{
		$query = $this->select('reason_id, reason_name')->get('forum_reason');
		return $query->fetchPairs();
	}
}

class Forum_Model extends Model
{
	const NORMAL = 0;
	const CATEGORY = 1;
	const LINK = 2;

	protected $name = 'forum';
	protected $primary = 'forum_id';
	protected $prefix = 'forum_';

	public $auth;
	public $marking;
	public $setting;
	public $reason;

	function __construct()
	{
		$this->auth = new Forum_Auth_Model;
		$this->marking = new Forum_Marking_Model;
		$this->setting = new Forum_Setting_Model;
		$this->reason = new Forum_Reason_Model;
	}

	public function getPageDepth($pageId)
	{
		return $this->db->fetchField("SELECT GET_DEPTH($pageId) AS depth", 'depth');
	}

	public function fetch($pageId, $depth = 0)
	{
		$user = &$this->load->model('user');

		$query = $this->db->select('forum.*, node.page_id, node.page_parent, node.page_subject, node.page_title, node.page_depth, forum_location.location_text, topic.topic_id, post_user, post_username, post_time, user_name, post.page_subject AS topic_subject, post_location.location_text AS topic_path')
						  ->from('forum, page AS node')
						  ->where('forum_page IN(SELECT pg.page_id FROM page_group pg WHERE pg.group_id IN(' . implode(',', $user->getGroups()) . '))')
						  ->where('node.page_id = forum_page')
						  ->where("child_id = node.page_id AND `length` > 0")
						  ->innerJoin('path', 'parent_id = ' . $pageId)
						  ->innerJoin('location AS forum_location', 'forum_location.location_page = node.page_id')
						  ->leftJoin('post AS post_data', 'post_data.post_id = forum_last_post_id')
						  ->leftJoin('user', 'user_id = post_user')
						  ->leftJoin('topic', 'topic_id = post_topic')
						  ->leftJoin('page AS post', 'post.page_id = topic_page')
						  ->leftJoin('location AS post_location', 'post_location.location_page = post.page_id')
						  ->order('node.page_matrix');

		$result = $forumSet = array();

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
		else
		{
			if (User::$id > User::ANONYMOUS)
			{
				$query->select('t_marking.mark_time AS topic_mark, f_marking.mark_time AS forum_mark');

				$query->leftJoin('topic_marking AS t_marking', 't_marking.user_id = ' . User::$id . ' AND t_marking.topic_id = topic.topic_id');
				$query->leftJoin('forum_marking AS f_marking', 'f_marking.forum_id = forum.forum_id AND f_marking.user_id = ' . User::$id);
			}
		}

		foreach ($query->fetchAll() as $row)
		{
			if (empty($row['forum_mark']))
			{
				$row['forum_mark'] = isset($tracking['f'][$row['forum_id']]) ? (int) base_convert($tracking['f'][$row['forum_id']], 36, 10) : (User::$id == User::ANONYMOUS ? $lastMark : User::data('regdate'));
			}

			if (empty($row['topic_mark']))
			{
				$topicId = base_convert($row['topic_id'], 10, 36);
				$row['topic_mark'] = isset($tracking['t'][$topicId]) ? (int) base_convert($tracking['t'][$topicId], 36, 10) : $row['forum_mark'];
			}

			$row['forum_unread'] = ($row['post_time'] > $row['forum_mark']);
			$row['topic_unread'] = ($row['post_time'] > $row['topic_mark'] && $row['post_time'] > $row['forum_mark']);

			$result[$row['page_id']] = $row;
			$branchId = $row['page_parent'];

			do
			{
				if (isset($result[$branchId]))
				{
					$parentRow = &$result[$branchId];

					$parentRow['forum_topics'] += $row['forum_topics'];
					$parentRow['forum_posts'] += $row['forum_posts'];

					if ($row['post_time'] > $parentRow['post_time'])
					{
						$parentRow['topic_unread'] = $row['topic_unread'];

						$parentRow['forum_last_post_id'] = $row['forum_last_post_id'];
						$parentRow['post_time'] = $row['post_time'];
						$parentRow['post_user'] = $row['post_user'];
						$parentRow['post_username'] = $row['post_username'];
						$parentRow['user_name'] = $row['user_name'];
						$parentRow['topic_subject'] = $row['topic_subject'];
						$parentRow['topic_path'] = $row['topic_path'];
						$parentRow['forum_mark'] = $row['forum_mark'];
						$parentRow['topic_mark'] = $row['topic_mark'];

						// jezeli na forum sa jakies nowe posty, to nalezy odznaczyc forum macierzyste jako nieprzeczytane!
						if ($row['forum_unread'])
						{
							$parentRow['forum_unread'] = true;
						}
					}

					if (isset($result[$parentRow['page_parent']]))
					{
						$branchId = $parentRow['page_parent'];
					}
					else
					{
						$branchId = null;
					}
				}
				else
				{
					$branchId = null;
				}
			}
			while ($branchId != null);
		}

		$isHidden = false;

		foreach ($result as $row)
		{
			if ($row['page_depth'] > $depth + 1)
			{
				if (isset($forumSet[$row['page_parent']]))
				{
					$forumSet[$row['page_parent']]['children'][] = $row;
				}
			}
			else
			{
				if ($row['forum_section'] && !$this->setting->getForumVisibility($row['forum_id']))
				{
					$isHidden = true;
				}
				else if ($row['forum_section'] && $this->setting->getForumVisibility($row['forum_id']))
				{
					$isHidden = false;
				}

				$row['is_hidden'] = $isHidden;
				$forumSet[$row['page_id']] = $row;
			}
		}

		return $forumSet;
	}

	public function isUnread($forumId)
	{
		if (User::$id > User::ANONYMOUS)
		{
			$markTime = $this->db->select('mark_time')->where("forum_id = $forumId AND user_id = " . User::$id)->get('forum_marking')->fetchField('mark_time');
			if (!$markTime)
			{
				$markTime = User::data('regdate');
			}
		}
		else
		{
			$tracking = unserialize($this->input->cookie('tracking'));

			if (!isset($tracking['l']))
			{
				$tracking['l'] = base_convert(time(), 10, 36);
				$this->output->setCookie('tracking', serialize($tracking), time() + 31536000);
			}

			$markTime = isset($tracking['f'][$forumId]) ? (int) base_convert($tracking['f'][$forumId], 36, 10) : base_convert($tracking['l'], 36, 10);
		}

		return $markTime;
	}

	public function markRead($forumId)
	{
		if (User::$id > User::ANONYMOUS)
		{
			$sql = "INSERT INTO forum_marking (forum_id, user_id, mark_time) VALUES($forumId, " . User::$id . ", " . time() . ") ON DUPLICATE KEY UPDATE mark_time = " . time();
			$this->db->query($sql);

			$this->db->delete('topic_marking', "forum_id = $forumId AND user_id = " . User::$id);
		}
		else
		{
			$tracking = unserialize($this->input->cookie('tracking'));

			foreach ((array) @$tracking['tf'][$forumId] as $topicId => $null)
			{
				unset($tracking['t'][$topicId]);
			}
			unset($tracking['tf'][$forumId]);

            $tracking['f'][$forumId] = base_convert(time(), 10, 36);
            $this->output->setCookie('tracking', serialize($tracking), time() + 31536000);
		}
	}

	public function getList($ommitNotAllowed = true)
	{
		$query = $this->db->select('forum_id, page_subject, page_title, page_depth, location_text')
						  ->from('forum, page')
						  ->innerJoin('location', 'location_page = page_id');

		if ($ommitNotAllowed)
		{
			$user = &$this->load->model('user');
			$query->where('forum_page IN(

				SELECT pg.page_id FROM page_group pg WHERE pg.group_id IN(' . implode(',', $user->getGroups()) . ')
			)');
		}
		$query->where('page_id = forum_page');
		$query->order('page_matrix');

		return $query;
	}

	public function getHtmlList($ommitNotAllowed = true)
	{
		$query = $this->getList($ommitNotAllowed)->get();
		$depth = 0;
		$result = array();
		$depths = array();

		foreach ($query as $row)
		{
			$depths[] = $row['page_depth'];
		}

		if ($depths)
		{
			$depth = @min($depths);
		}

		foreach ($query as $row)
		{
			$result[$row['location_text']] = str_repeat('&nbsp;', 5 * ($row['page_depth'] - $depth)) . $row['page_subject'];
		}

		return $result;
	}

	public function getAuth($option, $forumId = null)
	{
		static $groups = array();
		static $permission = array();

		if (!$groups)
		{
			if (User::$id == User::ANONYMOUS)
			{
				$groups[] = 1;
			}
			else
			{
				$user = &$this->getModel('user');
				$groups = $user->getGroups();
			}

			$query = $this->select('forum_id, forum_permission')->get();

			foreach ($query as $row)
			{
				$permission[$row['forum_id']] = unserialize($row['forum_permission']);
			}
		}
		$result = false;

		if (!$forumId)
		{
			$result = Auth::get($option);
		}
		else
		{
			if (empty($permission[$forumId]))
			{
				$result = Auth::get($option);
			}
			else
			{
				$optionId = null;

				foreach (Auth::getOptions() as $row)
				{
					if ($option == $row['option_text'])
					{
						$optionId = $row['option_id'];
					}
				}
				if ($optionId === null)
				{
					return $result;
				}

				foreach ($permission[$forumId] as $groupId => $row)
				{
					if (in_array($groupId, $groups))
					{
						if (!isset($row[$optionId]))
						{
							$result = Auth::get($option);
							break;
						}
						else if ($row[$optionId])
						{
							$result = true;
							break;
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Metoda zwraca informacje o kategorii forum
	 *
	 * @param $forumId      ID forum z tabeli forum
	 * @return array
	 */
	public function getForum($forumId)
	{
		return $this->db->select()
						->from('forum')
						->innerJoin('page', 'page_id = forum_page')
						->innerJoin('location', 'location_page = page_id')
						->where('forum_id = ?', $forumId)
						->fetchAssoc();
	}

	/**
	 * @deprecated
	 */
	/*private function move($mode, $forumId)
	{
		$data = $this->select()->where("forum_id = $forumId")->get()->fetchAssoc();

		$sql = 'SELECT forum_id,
					   forum_subject,
					   left_id,
					   right_id
				FROM forum
				WHERE forum_parent = ' . $data['forum_parent'] . '
					AND ' . ($mode == 'up' ? ('right_id < ' . $data['right_id'] . ' ORDER BY right_id DESC') : ('left_id > ' . $data['left_id'] . ' ORDER BY left_id ASC')) . '
				LIMIT 1';
		$query = $this->db->query($sql);
		$target = $query->fetchAssoc();

		if (!$target)
		{
			return false;
		}

		if ($mode == 'up')
		{
			$leftId = $target['left_id'];
			$rightId = $data['right_id'];

			$diffUp = $data['left_id'] - $target['left_id'];
			$diffDown = $data['right_id'] + 1 - $data['left_id'];

			$moveUpLeft = $data['left_id'];
			$moveUpRight = $data['right_id'];
		}
		else
		{
			$leftId = $data['left_id'];
			$rightId = $target['right_id'];

			$diffUp = $data['right_id'] + 1 - $data['left_id'];
			$diffDown = $target['right_id'] - $data['right_id'];

			$moveUpLeft = $data['right_id'] + 1;
			$moveUpRight = $target['right_id'];
		}

		$this->db->lock('forum WRITE');
		$sql = "UPDATE forum
				SET left_id = left_id + CASE
					WHEN left_id BETWEEN {$moveUpLeft} AND {$moveUpRight} THEN -{$diffUp}
					ELSE {$diffDown}
				END,
				right_id = right_id + CASE
					WHEN right_id BETWEEN {$moveUpLeft} AND {$moveUpRight} THEN -{$diffUp}
					ELSE {$diffDown}
				END
				WHERE
					left_id BETWEEN {$leftId} AND {$rightId}
					AND right_id BETWEEN {$leftId} AND {$rightId}";
		$this->db->query($sql);
		$this->db->unlock();

		return true;
	}

	public function down($forumId)
	{
		return $this->move('down', $forumId);
	}

	public function up($forumId)
	{
		return $this->move('up', $forumId);
	}*/
}
?>