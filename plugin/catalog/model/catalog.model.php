<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Catalog_Model extends Model
{
	public function getChildren($pageId, $sort = null, $count = null, $limit = null)
	{
		$query = $this->db
					  ->select('SQL_CALC_FOUND_ROWS page_subject, page_title, page_time, page_edit_time, location_text, text_content')
					  ->from('page')
					  ->leftJoin('location', 'location_page = page_id')
					  ->leftJoin('page_text', 'text_id = page_text')
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

	public function getFoundRows()
	{
		return (int) $this->db->query('SELECT FOUND_ROWS() AS totalItems')->fetchField('totalItems');
	}

	public function getCategories($pageId)
	{
		$query = $this->db->select('page_subject, page_title, location_text, location_children')
						  ->from('page')
						  ->leftJoin('location', 'location_page = page_id')
						  ->where('page_parent = ' . $pageId)
						  ->where('location_children > 0')
						  ->order('page_subject');

		return $query;
	}
}
?>