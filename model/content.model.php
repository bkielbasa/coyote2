<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Content_Model extends Model
{
	protected $name = 'content';
	protected $primary = 'content_id';
	protected $prefix = 'content_';

	public function getContentList()
	{
		$query = $this->select()->get();
		$result = array();

		foreach ($query as $row)
		{
			$result[$row['content_id']] = $row['content_type'];
		}
		return $result;
	}
}
?>