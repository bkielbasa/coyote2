<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Search_Queue_Model extends Model
{
	protected $name = 'search_queue';

	/**
	 * Dodaje strone do kolejki indeksowania
	 * @param int $pageId	Id strony
	 */
	public function addToQueue($pageId)
	{
		$sql = 'INSERT INTO search_queue (page_id, timestamp) VALUES(' . $pageId . ', ' . time() . ') ON DUPLICATE KEY UPDATE timestamp = ' . time();
		$this->db->query($sql);
	}

	public function fetch($where = null, $order = null, $limit = null, $count = null)
	{
		$query = $this->db->select('page.*, timestamp')->from('search_queue q')->group('page_id');
		$query->innerJoin('page', 'page.page_id = q.page_id');

		if ($where != null)
		{
			$query->where($where);
		}
		if ($order !== null)
		{
			$query->order($order);
		}
		if ($limit !== null || $count !== null)
		{
			$query->limit($limit, $count);
		}

		return $query;
	}

	public function getTotalItems()
	{
		return $this->select('COUNT(DISTINCT page_id) AS totalItems')->fetchField('totalItems');
	}
}

class Search_Top10_Model extends Model
{
	protected $name = 'search_top10';

	public function update($query)
	{
		$sql = 'INSERT INTO search_top10 (top10_query) VALUES(?) ON DUPLICATE KEY UPDATE top10_weight = top10_weight + 1';
		$this->db->query($sql, $query);
	}
}

class Search_Model extends Model
{
	protected $name = 'search';
	protected $prefix = 'search_';

	/**
	 * Zwraca tablice z informacja o aktywnym mechanizmie indeksowania lub FALSE
	 * w przypadku, gdy mechanizm jest wylaczony
	 */
	public function getEnabledSearch()
	{
		$query = $this->select()->where('search_default = 1 AND search_enable = 1')->get();

		if (!count($query))
		{
			return false;
		}
		else
		{
			return $query->fetchAssoc();
		}
	}

}
?>