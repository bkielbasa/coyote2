<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Report_Model extends Model
{
	protected $name = 'report';
	protected $prefix = 'report_';
	protected $primary = 'report_id';

	public function fetch($where = null, $order = null, $limit = null, $count = null)
	{
		$query = $this->select()->leftJoin('user', 'user_id = report_user')->leftJoin('page', 'page_id = report_page');
		
		if ($where)
		{
			$query->where($where);
		}
		if ($order)
		{
			$query->order($order);
		}
		if ($limit || $count)
		{
			$query->limit($limit, $count);
		}

		return $query;
	}
}
?>