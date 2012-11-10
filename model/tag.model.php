<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Tag_Model extends Model
{
	protected $name = 'tag';
	protected $primary = 'tag_id';

	public function filter($value)
	{
		$filter = new Filter_Tag;
		return $filter->filter($value);
	}

	public function insert($pageId, $tags)
	{
		$this->db->delete('page_tag', "page_id = $pageId");

		if (!is_array($tags))
		{
			$tags = explode(' ', $this->filter($tags));
		}

		$tags = array_map('trim', array_unique($tags));
		$sqlArr = array();

		if ($tags)
		{
			$query = $this->select('tag_text, tag_id')->in('tag_text', array_map(array('Text', 'quote'), $tags))->get();
			$tagsId = $query->fetchPairs();
		}

		foreach ($tags as $tag)
		{
			if ($tag = trim($tag))
			{
				if (!isset($tagsId[$tag]))
				{
					$sql = 'INSERT INTO tag (tag_text) VALUES(?)';
					$this->db->query($sql, $tag);

					$tagId = $this->db->nextId();
				}
				else
				{
					$tagId = $tagsId[$tag];
				}

				$sqlArr[] = array(
					'page_id'			=> $pageId,
					'tag_id'			=> $tagId
				);
			}
		}

		if ($sqlArr)
		{
			$sqlArr = array_slice($sqlArr, 0, 5);
			$this->db->multiInsert('page_tag', $sqlArr);
		}
	}

	public function getPageTags($pageId)
	{
		$query = $this->db->select('t.tag_text, t.tag_weight')
						  ->from('page_tag tt')
						  ->innerJoin('tag t', 't.tag_id = tt.tag_id')
						  ->where("tt.page_id = $pageId")
						  ->order('t.tag_weight DESC');

		$words = $query->fetchPairs();
		$result = array();

		if ($words)
		{
			$maxWeight = max(array_values($words));

			foreach ($words as $word => $weight)
			{
				$result[$word] = array(
					'weight'		=> $weight,
					'size'			=> str_replace(',', '.', max(9, round(($weight * 100) / $maxWeight) * 0.2))
				);
			}
		}

		return $result;
	}

	public function getTags($pageIds)
	{
		$query = $this->db->select('t.tag_text, tt.page_id')
						  ->from('page_tag tt')
						  ->innerJoin('tag t', 't.tag_id = tt.tag_id')
						  ->in('tt.page_id', (array) $pageIds)
						  ->get();

		$result = array();

		foreach ($query as $row)
		{
			$result[$row['page_id']][] = $row['tag_text'];
		}

		return $result;
	}

	public function getWeight(array $tags)
	{
		$result = array();
		$tags = array_map('trim', $tags);

		$query = $this->select('tag_text, tag_weight')
					  ->in('tag_text', array_map(array('Text', 'quote'), $tags))
					  ->get();

		$weight = $query->fetchPairs();

		foreach ($tags as $tag)
		{
			if (isset($weight[$tag]))
			{
				$result[$tag] = $weight[$tag];
			}
			else
			{
				$result[$tag] = 0;
			}
		}

		return $result;
	}

}

?>