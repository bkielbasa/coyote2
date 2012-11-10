<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

abstract class Model extends Context
{
	/**
	 * Pobiera wybrane kolumny z tabeli i zwraca obiekt klasy DB
	 * @param string $cols Wybrane kolumny (domyslnie *)
	 * @return mixed 
	 */
	public function select($cols = Db::SQL_WILDCARD)
	{ 
		return $this->db->select($cols)->from($this->name);
	}
	
	/**
	 * Realizuje zapytanie INSERT na tabeli
	 * @param mixed $assoc_ary Tablica asocjacyjna
	 */
	public function insert($assoc_ary)
	{
		return $this->db->insert($this->name, $assoc_ary);
	}
	
	/**
	 * Realizuje zapytanie INSERT dodajac jednoczesnie wiele rekordow w tabeli
	 * Np. INSERT INTO foo (foo,bar) VALUES('foo', 'bar'), ('foo1', 'bar')
	 * @param array $assoc_ary
	 */	
	public function multiInsert($assoc_ary)
	{
		return $this->db->multiInsert($this->name, $assoc_ary);
	}

	/**
	 * Realizuje zapytanie UPDATE do bazt danych
	 * @param mixed $assoc_ary Tablica asocjacyjna 
	 * @param string $where Warunek WHERE
	 * <code>
	 * $data = array('foo' => 'bar);
	 * $this->update($data, 'id = 5'); 
	 * </code>
	 */
	public function update($assoc_ary, $where = null)
	{
		return $this->db->update($this->name, $assoc_ary, $where);
	}

	/**
	 * Realizuje zapytanie DELETE do bazt danych
	 * @param string $here Warunek WHERE
	 * <code>
	 * $this->delete('id = 5'); 
	 * </code>
	 */
	public function delete($where = null)
	{
		return $this->db->delete($this->name, $where);
	}

	/**
	 * Buduje zapytanie SELECT.
	 *
	 * UWAGA! Dane nie sa filtrowane pod katem SQL Injection. 
	 * Zaklada sie ze dane zostaly filtrowane wczesniej
	 * @param string $col Nazwa kolumny na ktorej zostanie zbudowany warunek WHERE
	 * @param mixed $value Wartosci 
	 * <code>
	 * $this->locate('foo', array('bar', 'foobar'); 
	 * // SELECT * FROM `table` WHERE foo IN("bar", "foobar"); 
	 * </code>
	 * @return mixed
	 */
	public function locate($col, array $value)
	{
		foreach ($value as $key => $arg)
		{
			$value[$key] = $this->db->quote($arg);
		}
		$from = array($this->name); 
		$query = $this->select(isset($this->col) ? $this->col : DB::SQL_WILDCARD)->in($col, $value);

		if (isset($this->reference))
		{
			foreach ($this->reference as $row)
			{ 
				$from[] = $row['table'];
				$query->where($row['col'] . ' = ' . $row['refCol']);
			}
		}
		$query->from($from);

		return $query->get();		
	}

	/**
	 * Realizuje pobranie wybranych rekordow na podstawie klucza primary key
	 * <code>
	 * $this->find(1, 2); 
	 * // pobierze rekordy ktorych primary key = 1 lub 2
	 * </code>
	 * @return mixed
	 */
	public function find()
	{
		if (!$this->primary)
		{
			throw new Exception('Primary key is not set');
		}
		if (func_num_args() == 1)
		{
			$args = func_get_arg(0);

			if (!is_array($args))
			{
				$args = array($args);
			}
		}
		else
		{
			$args = func_get_args();
		}
		return $this->locate($this->primary, $args);
	}
	
	/**
	 * Metoda wywoluje metode count() z klas Db
	 * Zlicza ilosc rekordow w tabeli
	 * @return int
	 */
	public function count()
	{
		return $this->db->count($this->name);
	}

	/** 
	 * Zwraca ID ostatnio wstawionego rekordu
	 * @return int
	 */
	public function nextId()
	{
		return $this->db->nextId();
	}

	/**
	 * Realizuje pobranie wybranych rekordow na podstawie wybranej kolumny
	 * <code>
	 * $this->getByName('Adam', 'Admin'); 
	 * // pobierze rekordy ktorych klucz "name" jest rowny Adam lub Admin
	 * </code>
	 * @return mixed
	 */
	public function __call($method, $arg)
	{
		if (strpos($method, 'getBy') !== false)
		{ 
			$col = $this->prefix . strtolower(substr($method, 5));
			if (is_array($arg[0]))
			{
				$arg = $arg[0];
			}

			return $this->locate($col, $arg);
		}
		throw new Exception('Call to undefinied function ' . $method);
	}

	/**
	 * Zwraca wszystkie rekordy z tabeli
	 * @return mixed
	 */
	public function fetchAll()
	{
		return $this->db->select()->from($this->name)->fetchAll();
	}

	/**
	 * Pobiera dane z tabeli (zapytanie SELECT)
	 * @param string $where Warunek WHERE
	 * @param string $order Kolejnosc sortowania
	 * @param int $count Numer wiersza z ktorego beda pobierane dane
	 * @param int $limit Limit pobieranych wierszy
	 * <code>
	 * $this->fetch('user_id < 10', 'user_id DESC', 10, 10); 
	 * // SELECT * FROM `tabela` WHERE user_id < 5 ORDER BY user_id DESC LIMIT 10, 10;
	 * </code>
	 * @return mixed
	 */
	public function fetch($where = null, $order = null, $count = null, $limit = null)
	{
		$from = array($this->name);

		$sql = $this->db->select(isset($this->col) ? $this->col : DB::SQL_WILDCARD);
		if (isset($this->reference))
		{
			foreach ($this->reference as $row)
			{ 
				$from[] = $row['table'];
				$sql->where($row['col'] . ' = ' . $row['refCol']);
			}
		}
		$sql->from($from);

		if ($where != null)
		{
			$sql->where($where);
		}
		if ($order != null)
		{
			$sql->order($order);
		}
		if ($count != null || $limit != null)
		{ 
			$sql->limit($count, $limit);
		}
		return $sql->get();
	}	
}
?>