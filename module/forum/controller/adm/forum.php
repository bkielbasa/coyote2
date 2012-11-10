<?php
/**
 * @package 4programmers.net
 * @version $Id$
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

set_time_limit(0);

class Forum_Controller extends Adm
{
	function main()
	{
		$forum = &$this->getModel('forum');

		$query = $forum->getList(false)->get();
		$depth = 0;
		$depths = array();

		$this->forumList = array();

		foreach ($query as $row)
		{
			$depths[] = $row['page_depth'];
		}
		$depth = @min($depths);

		foreach ($query as $row)
		{
			$this->forumList[$row['forum_id']] = str_repeat('&nbsp;', 5 * ($row['page_depth'] - $depth)) . $row['page_subject'];
		}

		$this->authList = array();

		$auth = &$this->getModel('auth');
		foreach ($auth->getOptions() as $key => $row)
		{
			if (strpos($row['option_text'], 'f_') !== false)
			{
				$this->authList[$key] = $row;
			}
		}
		$group = &$this->getModel('group');

		$this->groupList = array();
		foreach ($group->fetch()->fetch() as $row)
		{
			$this->groupList[$row['group_id']] = $row['group_name'];
		}
		$this->optionList = array();

		if ($this->get->forumId && $this->get->groupId)
		{
			$permission = $forum->find($this->get->forumId)->fetchField('forum_permission');
			$permission = unserialize($permission);

			if ($this->input->isPost())
			{
				foreach ($this->authList as $key => $value)
				{
					$permission[$this->get->groupId][$key] = isset($this->post->permission[$key]);
				}

				$forum->update(array('forum_permission' => serialize($permission)), 'forum_id = ' . $this->get->forumId);
				$this->session->message = 'Uprawnienia zostały zaktualizowane';
			}

			foreach ($this->authList as $key => $value)
			{
				$this->optionList[$key] = array(

					'label'			=> $value['option_label'],
					'value'			=> isset($permission[$this->get->groupId][$key]) ? $permission[$this->get->groupId][$key] : $value['option_default']

				);
			}
		}

		return true;
	}

	public function reason($id = 0)
	{
		$id = (int) $id;

		$forum = &$this->getModel('forum');
		$this->reason = $forum->reason->fetchAll();

		if ($this->input->isPost())
		{
			if (isset($this->post->delete))
			{
				$delete = $this->post->delete;
				$forum->reason->delete('reason_id IN(' . implode(',', array_map('intval', $delete)) . ')');

				$this->redirect('adm/Forum/Reason');
			}
		}

		$this->filter = new Filter_Input;
		$result = array();

		if ($id)
		{
			$result = $forum->reason->find($id)->fetchAssoc();
		}

		if ($this->input->isPost())
		{
			if (!isset($this->post->delete))
			{
				$data['validator'] = array(

					'name'			=> array(
												array('notempty')
									),
					'content'		=> array(
												array('string', true)
									)
				);
				$data['filter'] = array(

					'name'			=> array('htmlspecialchars')
				);
				$this->filter->setRules($data);

				if ($this->filter->isValid($_POST))
				{
					load_helper('array');
					$data = array_key_pad($this->filter->getValues(), 'reason_');

					if (!$this->post->id)
					{
						$forum->reason->insert($data);
					}
					else
					{
						$forum->reason->update($data, 'reason_id = ' . $this->post->id);
					}

					$this->redirect('adm/Forum/Reason');
				}
			}
		}

		return View::getView('adm/forumReason', $result);
	}

	public function comment()
	{
		$post = &$this->getModel('post');
		Sort::setDefaultSort('comment_id', Sort::DESC);

		$start = (int) $this->get['start'];

		$query = $post->comment->select('comment_id, comment_time, comment_text, comment_user, post_id, user_name, location_text, page_subject')
							   ->innerJoin('post', 'post_id = comment_post')
							   ->innerJoin('topic', 'topic_id = post_topic')
							   ->innerJoin('page', 'page_id = topic_page')
							   ->innerJoin('location', 'location_page = topic_page')
							   ->innerJoin('user', 'user_id = comment_user')
							   ->order(Sort::getSortAsSQL())
							   ->limit($start, 50);

		$this->comment = $query->fetchAll();

		$this->totalItems = $post->comment->count();
		$this->totalPages = ceil($this->totalItems / 50);
		$this->pagination = new Pagination('', $this->totalItems, 50, $start);

		return true;
	}

	public function stat()
	{
		$user = &$this->getModel('user');
		$this->topPostUser = $user->select()->order('user_post DESC')->limit(10)->fetchAll();

		$post = &$this->getModel('post');
		$topic = &$this->getModel('topic');
		$this->topCommentUser = $post->comment->select('user.*, COUNT(comment_id) AS count')->innerJoin('user', 'user_id = comment_user')->group('comment_user')->order('count DESC')->limit(10)->fetchAll();

		$this->topMonths = $post->select("MONTH(FROM_UNIXTIME(post_time)) AS month, YEAR(FROM_UNIXTIME(post_time)) AS year, COUNT(post_id) AS count")->group("year, month")->order('count DESC')->limit(10)->fetchAll();

		$this->diffDays = $post->select('DATEDIFF("' . date('Y-m-d') . '",  FROM_UNIXTIME(post_time)) AS diff')->order('post_id')->limit(1)->fetchField('diff');
		$this->commentdiffDays = $post->comment->select('DATEDIFF("' . date('Y-m-d') . '",  FROM_UNIXTIME(comment_time)) AS diff')->order('comment_id')->limit(1)->fetchField('diff');

		$this->postCount = $post->select('COUNT(*)')->from('post')->fetchField('COUNT(*)');
		$this->topicCount = $topic->count();
		$this->commentCount = $post->comment->count();

		$this->avgPost = $this->postCount / $this->diffDays;
		$this->avgTopic = $this->topicCount / $this->diffDays;
		$this->avgComment = $this->commentCount / $this->commentdiffDays;

		$sql = 'SELECT page_subject, (SELECT COUNT(*) FROM post WHERE post_forum = forum_id) / (SELECT DATEDIFF(NOW(), FROM_UNIXTIME(post_time)) FROM post WHERE post_forum = forum_id ORDER BY post_id LIMIT 1) AS value
				FROM forum
				INNER JOIN page ON page_id = forum_page
				ORDER BY value DESC';

		$this->avgPerForum = $this->db->query($sql)->fetchAll();

		$sql = 'SELECT user_name, COUNT(*) AS count
				FROM post
				INNER JOIN user ON user_id = post_user
				WHERE post_user > 0 AND post_time > UNIX_TIMESTAMP(NOW() - INTERVAL 30 DAY)
				GROUP BY post_user
				ORDER BY COUNT(*) DESC
				LIMIT 10';

		$this->mostActiveUsers = $this->db->query($sql)->fetchAll();

		return true;
	}
}
?>