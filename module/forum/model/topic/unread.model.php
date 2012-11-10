<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Topic_Unread_Model extends Topic_Abstract_Model
{
	public function load()
	{
		$conditions = array();

		if ($this->getTopicIds())
		{
			$conditions[] = 'topic.topic_id IN(' . implode(',', $this->getTopicIds()) . ')';
		}
		if ($this->getForumId())
		{
			$conditions[] = 'topic_forum = ' . $this->getForumId();
		}

		if (User::$id > User::ANONYMOUS)
		{
			$conditions[] = '(topic_last_post_time > IFNULL(f_marking.mark_time, 0) AND topic_last_post_time > IFNULL(t_marking.mark_time, 0))';
		}

		$query = $this->fetch($conditions, $this->getSort(), $this->getOffset(), $this->getLimit());
		$query = $query->get();

		$count = $this->db->select('COUNT(*)')->from('topic');

		if ($conditions)
		{
			$count->where($conditions);
		}

		$this->totalItems = $count->fetchField('COUNT(*)');

		return $this->applyTopicMark($query);
	}
}
?>