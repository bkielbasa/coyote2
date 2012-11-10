<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Filter_Model extends Model
{
	protected $name = 'filter';
	
	public function getFilterList()
	{
		$result = array();

		$query = $this->select()->get();
		foreach ($query as $row)
		{
			$result[$row['filter_id']] = $row['filter_description'];
		}

		return $result;
	}
}
?>