<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Dostarcza informacje zwiazane z danym zapytaniem (profiler)
 */
class Db_Profiler_Query
{
	private $query;
	private $md5;

	function __construct($query)
	{
		$this->query = $query;

		$this->md5 = md5($query);
		$this->start();
	}

	private function start()
	{
		Benchmark::start($this->md5);
	}

	public function stop()
	{ 
		Benchmark::stop($this->md5);
	}

	public function getQuery()
	{
		return $this->query;
	}

	public function getElapsedTime()
	{
		return Benchmark::elapsed($this->md5);
	}
}

/**
 * Profiler DB
 */
class Db_Profiler 
{
	/**
	 * Prywatne pole przechowuje instancje klasy
	 */
	private static $instance;
	/**
	 * Tablica zawiera instancje klasy Db_Profiler_Query
	 */
	private $query_arr = array();
	/**
	 * Pole okresla, czy profiler jest wlaczony czy nie
	 */
	private $enable; 

	function __construct()
	{
		self::$instance = &$this;	

		// odczytanie informacji, czy profiler jest wlaczony.
		// jezeli nie, ale system dziala w trybie DEBUG -- wymus wlaczenie
		if (!$this->enable = Config::getItem('core.sqlProfiler'))
		{
			if (defined('DEBUG') && DEBUG)
			{
				$this->enable = true;
			}
		} 
	}

	/**
	 * Uaktywnia profiler
	 */
	public function enable()
	{
		$this->enable = true;
	}

	/**
	 * Dezaktywuje profiler
	 */
	public function disable()
	{
		$this->enable = false;
	}

	/**
	 * Rozpoczyna pomiar czasu
	 * @param string $query Zapytanie SQL
	 * @return int ID 
	 */
	public function start($query)
	{
		if (!$this->enable)
		{
			return;
		}
		$this->query_arr[] = new Db_Profiler_Query($query);
		// przesuniecie na koniec tablicy
		end($this->query_arr);

		return key($this->query_arr);
	}

	/**
	 * Zatrzymuje pomiar czasu
	 */
	public function stop($query_id)
	{
		if (!$this->enable)
		{
			return;
		}
		$this->query_arr[$query_id]->stop();		
	}

	/**
	 * Zwraca informacje dot. zapytan
	 * @return mixed
	 */
	public function get()
	{ 
		return $this->query_arr;
	}

	/**
	 * Zwraca ilosc zapytan
	 * @return int
	 */
	public function getTotalNumQueries()
	{
		return sizeof($this->query_arr);
	}

	/**
	 * Metoda zwraca instancje klasy
	 * @return mixed
	 */
	public static function &getInstance()
	{
		return self::$instance;
	}
}

?>