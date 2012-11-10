<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Blog_Model extends Model
{
	public function getChildren($pageId, $sort = null, $count = null, $limit = null)
	{
		$query = $this->db->select('page_id, page_subject, page_title, page_time, page_time, location_text, text_content, user_id, user_name, user_photo, cache_content')
						  ->from('page')
						  ->leftJoin('location', 'location_page = page_id')
						  ->leftJoin('page_text', 'text_id = page_text')
						  ->leftJoin('user', 'user_id = text_user')
						  ->leftJoin('page_cache', 'cache_page = page_id')
						  ->where('page_parent = ' . $pageId)
						  ->where('location_children = 0')
						  ->where('page_publish = 1 AND page_delete = 0');

		if ($this->module->isPluginEnabled('comment'))
		{
			$query->select('(

				SELECT COUNT(comment_id)
				FROM `comment`
				WHERE comment_page = page_id
				) AS page_comment
			');
		}

		if ($sort !== null)
		{
			$query->order($sort);
		}
		if ($count !== null && $limit !== null)
		{
			$query->limit($count, $limit);
		}

		return $query->get();
	}

	public function count($pageId)
	{
		return (int) $this->db->select('COUNT(*)')->from('page')->where('page_parent = ' . $pageId)->leftJoin('location', 'location_page = page_id')->where('location_children = 0')->where('page_publish = 1 AND page_delete = 0')->fetchField('COUNT(*)');
	}

}
?>