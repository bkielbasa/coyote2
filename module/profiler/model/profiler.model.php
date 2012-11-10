<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Profiler_Sql_Model extends Model
{
	protected $name = 'profiler_sql';
	
	public function insert($sql, $time)
	{
		parent::insert(array(
			'sql_id'			=> md5($sql),
			'sql_query'			=> $sql,
			'sql_time'			=> number_format($time, 5)
			)
		);		
	}
	
	/**
	 * Metoda pobiera informacje o czasie generowania poszczegolnych stron
	 * W przypadku wiekszej ilosci danych, to zapytanie moze byc czasochlonne
	 * Metoda nie jest powiazana z metoda fetch() z klasy Model, nie dziedziczy po niej
	 */
	public function fetch($where = null, $order = null, $count = null, $limit = null)
	{
		$sql = 'SELECT SQL_CALC_FOUND_ROWS
					   sql_query,
					   COUNT(*) AS sql_count,
					   AVG(sql_time) AS sql_time
				FROM profiler_sql
				GROUP BY sql_id';

		if ($order != null)
		{
			$sql .= ' ORDER BY ' . $order;
		}
		if ($count != null || $limit != null)
		{
			$sql .= ' LIMIT ' . $count . ', ' . $limit;
		}
		return $this->db->query($sql);

	}
}

class Profiler_Model extends Model
{
	protected $name = 'profiler';
	public $sql;
	
	function __construct()
	{
		$this->sql = new Profiler_Sql_Model;
	}

	/**
	 * Metoda pobiera informacje o czasie generowania poszczegolnych stron
	 * W przypadku wiekszej ilosci danych, to zapytanie moze byc czasochlonne
	 * Metoda nie jest powiazana z metoda fetch() z klasy Model, nie dziedziczy po niej
	 */
	public function fetch($where = null, $order = null, $count = null, $limit = null)
	{
		$sql = 'SELECT SQL_CALC_FOUND_ROWS
					   profiler_page,
					   COUNT(*) AS profiler_count,
					   AVG(profiler_time) AS profiler_time,
					   AVG(profiler_sql) AS profiler_sql,
					   profiler_time - profiler_sql AS profiler_php
				FROM profiler
				GROUP BY profiler_page';

		if ($order != null)
		{
			$sql .= ' ORDER BY ' . $order;
		}
		if ($count != null || $limit != null)
		{
			$sql .= ' LIMIT ' . $count . ', ' . $limit;
		}
		return $this->db->query($sql);

	}
}
?>