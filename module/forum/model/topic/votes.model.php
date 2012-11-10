<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Topic_Votes_Model extends Topic_Abstract_Model
{
	public function load()
	{
		$topicIds = array();

		if (!$this->getOmitSticky())
		{
			$query = $this->db->select('topic_id')->from('topic');

			if ($this->getForumId())
			{
				$query->where('topic_forum = ' . $this->getForumId());
			}
			$query->where('topic_sticky = 1');
			$query->order($this->getSort());

			$topicIds = $query->fetchCol('topic_id');
		}

		$conditions = array();

		if ($this->getForumId())
		{
			$conditions[] = 'topic_forum = ' . $this->getForumId();
		}

		$conditions[] = 'topic_vote >= 0';

		if ($this->getTags())
		{
			$tag = &$this->getModel('tag');
			$tagId = (int) $tag->select('tag_id')->where('tag_text = ' . $this->db->quote($this->getTags()))->fetchField('tag_id');

			$conditions[] = 'topic.topic_page IN(SELECT page_id FROM page_tag WHERE tag_id = ' . $tagId . ')';
		}
		if ($this->getOmitNotAllowed())
		{
			$user = &$this->load->model('user');
			$conditions[] = 'topic_page IN(SELECT pg.page_id FROM page_group pg WHERE pg.group_id IN(' . implode(',', $user->getGroups()) . '))';
		}

		$count = $this->db->select('COUNT(*)')->from('topic');
		if ($conditions)
		{
			$count->where($conditions);
		}
		$this->totalItems = $this->getTotalItemsFromCache($count);
		$this->totalItems += count($topicIds);

		$query = $this->db->select('topic_id')->from('topic')->where($conditions);

		$query->order($this->getSort())->limit($this->getOffset(), $this->getLimit());
		$topicIds = array_merge($topicIds, $query->fetchCol('topic_id'));

		$conditions = array();

		if (!$topicIds)
		{
			return array();
		}

		$query = $this->fetch($topicIds, $conditions, 'FIND_IN_SET(topic.topic_id, "' . implode(',', $topicIds) . '") ASC');
		$query = $query->get();

		$this->saveTopicViewMode('votes');
		return $this->applyTopicMark($query);
	}
}
?>