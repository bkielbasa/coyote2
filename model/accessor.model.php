<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Accessor_Model extends Model
{
	protected $name = 'accessor';
	protected $prefix = 'accessor_';

	public function fetchAccessors(&$content)
	{
		$accessor_arr = array();

		preg_match_all('/\[\[(.*?)(\|(.*?))*\]\]/i', $content, $matches);
		if ($matches[0])
		{
			$path = new Path;

			for ($i = 0, $limit = sizeof($matches[0]); $i < $limit; $i++)
			{
				$element = array();
				foreach (explode('/', $matches[1][$i]) as $part)
				{
					$element[] = $path->encode($part);
				}

				$accessor_arr[] = implode('/', $element);
			}
			$accessor_arr = array_unique($accessor_arr);
		}
		return $accessor_arr;
	}

	public function fetch($ids)
	{
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		$sql = 'SELECT page_id,
					   page_subject,
					   page_title,
					   location_text
				FROM accessor, page
				INNER JOIN location ON location_page = page_id
				WHERE accessor_from IN(' . implode(', ', $ids) . ')
						AND page_id = accessor_to';
		return $this->db->query($sql);
	}

	public function insert($id, &$accessor_arr)
	{
		$this->db->delete('broken', "broken_from = $id");
		$this->db->delete('accessor', "accessor_from = $id");

		if (!$accessor_arr)
		{
			return;
		}

		$quote_arr = array_map(array('Text', 'quote'), $accessor_arr);
		$path_arr = array();

		$query = $this->db->select('location_page, LOWER(location_text) AS location_text')->from('location')->where('location_text IN(' . implode(',', $quote_arr) . ')')->get();
		while ($row = $query->fetchAssoc())
		{
			$path_arr[$row['location_page']] = $row['location_text'];
		}

		$accessor_lower_arr = array_map(array('Text', 'toLower'), $accessor_arr);
		$sql_arr = array();

		foreach (array_keys(array_diff($accessor_lower_arr, $path_arr)) as $index)
		{
			$sql_arr[] = array(
					'broken_from'	=> $id,
					'broken_path'	=> $accessor_arr[$index]
			);
		}
		if ($sql_arr)
		{
			$this->db->multiInsert('broken', $sql_arr);
		}
		$sql_arr = array();

		foreach (array_keys(array_intersect($path_arr, $accessor_lower_arr)) as $index)
		{
			$sql_arr[] = array(
					'accessor_from'		=> $id,
					'accessor_to'		=> $index
			);
		}
		if ($sql_arr)
		{
			$this->db->multiInsert('accessor', $sql_arr);
		}
	}
}
?>