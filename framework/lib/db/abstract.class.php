<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

abstract class Db_Abstract
{
	protected $init = array(
        Db::DISTINCT     => false,
        Db::COLUMNS      => array(),
        Db::FROM         => array(),
		Db::INNER_JOIN   => array(),
		Db::LEFT_JOIN	 => array(),
		Db::RIGHT_JOIN   => array(),
        Db::WHERE        => array(),
        Db::GROUP        => array(),
        Db::HAVING       => array(),
        Db::ORDER        => array(),
        Db::LIMIT_COUNT  => null,
        Db::LIMIT_OFFSET => null,
        Db::FOR_UPDATE   => false
    );

	protected $join = array(
        Db::INNER_JOIN,
        Db::LEFT_JOIN,
        Db::RIGHT_JOIN
    );
	protected $sql = array();

	public function bind($sql, $args)
	{
		if (count($args) > 0)
		{
			$sql = str_replace('?', ($uniqid = uniqid()), $sql);

			foreach ($args as $bind)
			{
				if (($pos = strpos($sql, $uniqid)) === false)
				{
					break;
				}
				$bind = $this->quote($bind);
				$sql = substr_replace($sql, $bind, $pos, 13);
			}

			$sql = str_replace($uniqid, '', $sql);
		}

		return $sql;
	}

	/**
	 * Tworzenie fragmentu SQL zapytania
	 * @param string $query (mozliwe wartosci: UPDATE, INSERT)
	 * @param mixed $assoc_ary
	 */
	public function sqlBuildQuery($query, $assoc_ary)
	{
		/* deklaracja tablic, pola oraz wartosci */
		$fields = $values = array();

		if ($query == 'UPDATE')
		{
			foreach ($assoc_ary as $k => $v)
			{
				$values[] = "$k = " . $this->quote($v);
			}
			$sql = implode(', ', $values);
		}
		else
		{
			/* petla po elementach tablicy */
			foreach ($assoc_ary as $k => $v)
			{
				$fields[] = $k;
				$values[] = $this->quote($v);
			}
			/* utworz zapytanie */
			$sql = ' (' . implode(', ', $fields) . ') VALUES(' . implode(', ', $values) . ')';
		}
		return $sql;
	}

	/* Metoda realizuje masowe wstawianie rekordow w tabeli
	 * @param string $tbl_name Nazwa tabeli
	 * @param mixed $assoc_ary Tablica wartosci
	 * @return mixed
	 */
	public function multiInsert($tbl_name, $assoc_ary)
	{
		$ary = array();

		foreach ($assoc_ary as $key => $sql_ary)
		{
			$values = array();
			foreach ($sql_ary as $k => $v)
			{
				$values[] = $this->quote($v);
			}
			$ary[] = '(' . implode(',', $values) . ')';
		}
		$sql = 'INSERT INTO ' . $tbl_name . ' (' . implode(',', array_keys($assoc_ary[0])) . ') VALUES' . implode(',', $ary);
		return $this->query($sql);
	}

	/**
	 * Realizuje zapytanie INSERT do bazy danych
	 * @param string $tbl_name Nazwa tabeli
	 * @param mixed $assoc_ary Tablica asocjacyjna
	 * <code>
	 * $data = array('foo' => 'bar', 'id' => 5);
	 * $this->insert('coyote_foo', $data);
	 * </code>
	 */
	public function insert($tbl_name, $assoc_ary)
	{
		$sql = 'INSERT INTO ' . $tbl_name . ' ' . $this->sqlBuildQuery('INSERT', $assoc_ary);
		return $this->query($sql);
	}

	/**
	 * Realizuje zapytanie UPDATE do bazt danych
	 * @param string $tbl_name Nazwa tabeli
	 * @param mixed $assoc_ary Tablica asocjacyjna
	 * @param string $sql_where Warunek WHERE
	 * <code>
	 * $data = array('foo' => 'bar);
	 * $this->update('coyote_foo', $data, 'id = 5');
	 * </code>
	 */
	public function update($tbl_name, $assoc_ary, $sql_where = null)
	{
		$sql = 'UPDATE ' . $tbl_name . ' SET ' . $this->sqlBuildQuery('UPDATE', $assoc_ary);

		if ($sql_where)
		{
			if (is_array($sql_where))
			{
				$sql_where = implode(' AND ', $sql_where);
			}
			$sql .= " WHERE $sql_where";
		}
		return $this->query($sql);
	}

	/**
	 * Realizuje zapytanie DELETE do bazt danych
	 * @param string $tbl_name Nazwa tabeli
	 * @param string $sql_where Warunek WHERE
	 * <code>
	 * $this->delete('coyote_foo', 'id = 5');
	 * </code>
	 */
	public function delete($tbl_name, $sql_where = null)
	{
		if (!$sql_where)
		{
			$sql = "TRUNCATE $tbl_name";
		}
		else
		{
			if (is_array($sql_where))
			{
				$sql_where = implode(' AND ', $sql_where);
			}
			$sql = "DELETE FROM $tbl_name WHERE $sql_where";
		}
		return $this->query($sql);
	}

	/**
	 * Zwraca ilosc rekordow znajdujacych sie w tabeli
	 * @param string $tbl_name Nazwa tabeli
	 * @return int
	 */
	public function count($tbl_name)
	{
		$sql = "SELECT COUNT(*) as total_page
				FROM $tbl_name";
		return ($this->query($sql)->fetchField('total_page'));
	}

	public function select($cols = Db::SQL_WILDCARD)
	{
		if (!is_array($cols))
		{
			$cols = explode(',', $cols);
		}
		$this->sql[Db::COLUMNS] = array_merge($this->sql[Db::COLUMNS], $cols);

		return $this;
	}

	public function from($from)
	{
		if (!is_array($from))
		{
			$from = array($from);
		}
		$this->sql[Db::FROM] += $from;

		return $this;
	}

	public function innerJoin($tbl, $condition)
	{
		$this->_join($tbl, $condition, Db::INNER_JOIN);

		return $this;
	}

	public function leftJoin($tbl, $condition)
	{
		$this->_join($tbl, $condition, Db::LEFT_JOIN);

		return $this;
	}

	public function rightJoin($tbl, $condition)
	{
		$this->_join($tbl, $condition, Db::RIGHT_JOIN);

		return $this;
	}

	private function _join($tbl, $condition, $join)
	{
		$this->sql[$join][] = array($tbl => $condition);
	}

	public function where($where)
	{
		$args = array();

		if (func_num_args() > 1)
		{
			$args = func_get_args();
			$where = array_shift($args);
		}
		else
		{
			if (is_array($where))
			{
				foreach ($where as $condition)
				{
					$this->where($condition);
				}

				return $this;
			}
		}
		$this->sql[Db::WHERE][] = $this->bind($where, $args);

		return $this;
	}

	public function in($in, array $array)
	{
		$this->where($in . ' IN(' . implode(',', $array) . ')');

		return $this;
	}

	public function like($like, $value)
	{
		$this->where($like . ' LIKE ' . $this->quote($value));

		return $this;
	}

	public function order($order)
	{
		$this->sql[Db::ORDER][] = $order;

		return $this;
	}

	public function group($group)
	{
		$this->sql[Db::GROUP][] = $group;

		return $this;
	}

	public function having($having)
	{
		$this->sql[Db::HAVING] = $having;

		return $this;
	}

	public function limit($count, $limit = 0)
	{
		if (!$limit)
		{
			$limit = $count;
			$count = 0;
		}
		$this->sql[Db::LIMIT_OFFSET] = max(0, $count); // walidacja
		$this->sql[Db::LIMIT_COUNT] = max(0, $limit);

		return $this;
	}

	private function _get_columns($sql)
	{
		$sql .= ' ' . implode(', ', $this->sql[Db::COLUMNS]);

		return $sql;
	}

	protected function _get_from($sql)
	{
		$from = array();

		$sql .= ' ' . Db::SQL_FROM;
		foreach ($this->sql[Db::FROM] as $prefix => $tbl)
		{
			if (!is_int($prefix))
			{
				$from[] = $tbl . ' ' . $prefix;
			}
			else
			{
				$from[] = $tbl;
			}
		}
		$sql .= ' (' . implode(', ', $from) . ')';

		return $sql;
	}

	protected function _get_join($sql, $type)
	{
		foreach ((array)@$this->sql[$type] as $row)
		{
			while (list($tbl, $cnd) = each($row))
			{
				$sql .= ' ' . $type . ' ' . $tbl . ' ON ' . $cnd;
			}
		}

		return $sql;
	}

	protected function _get_innerjoin($sql)
	{
		return $this->_get_join($sql, Db::INNER_JOIN);
	}

	protected function _get_leftjoin($sql)
	{
		return $this->_get_join($sql, Db::LEFT_JOIN);
	}

	protected function _get_rightjoin($sql)
	{
		return $this->_get_join($sql, Db::RIGHT_JOIN);
	}

	protected function _get_where($sql)
	{
		$sql .= ' ' . Db::SQL_WHERE;
		$sql .= ' ' . implode(' AND ', $this->sql[Db::WHERE]);

		return $sql;
	}

	protected function _get_order($sql)
	{
		$sql .= ' ' . Db::SQL_ORDER_BY;
		$sql .= ' ' . implode(', ', $this->sql[Db::ORDER]);

		return $sql;
	}

	private function _get_group($sql)
	{
		$sql .= ' ' . Db::SQL_GROUP_BY;
		$sql .= ' ' . implode(', ', $this->sql[Db::GROUP]);

		return $sql;
	}

	private function _get_having($sql)
	{
		$sql .= ' ' . Db::SQL_HAVING;
		$sql .= ' ' . $this->sql[Db::HAVING];

		return $sql;
	}

	protected function _get_limitcount($sql)
	{
		$sql .= ' LIMIT';
		$sql .= ' ' . $this->sql[Db::LIMIT_OFFSET] . ', ';
		$sql .= ' ' . $this->sql[Db::LIMIT_COUNT];
		return $sql;
	}

	/**
	 * Buduje zapytanie SQL
	 * @return string
	 */
	protected function buildQuery()
	{
		$sql = Db::SQL_SELECT;

		foreach (array_keys($this->init) as $key)
		{
			$method = '_get_' . str_replace(' ', '', $key);
			if (method_exists($this, $method) && $this->sql[$key])
			{
				$sql = $this->$method($sql);
			}
		}

		return $sql;
	}

	/**
	 * Metoda generuje zapytanie SQL na podstawie dostarczonych skladowych
	 */
	public function get($tbl_name = null)
	{
		if ($tbl_name != null)
		{
			$this->from($tbl_name);
		}

		$sql = $this->buildQuery();
		$this->reset();

		return $this->query($sql);
	}

	/**
	 * Zwraca wartosc aktualnego zapytania (konwersja obiektu do postacii zapytania SQL)
	 */
	public function __toString()
	{
		return $this->buildQuery();
	}

	/**
	 * Zeruje skladowe klasy (przywraca ich domyslna wartosc
	 */
	public function reset()
	{
		$this->sql = $this->init;
	}

	/**
	 * Pobranie danych otrzymanych w wyniku zapytania
	 * @param int $fetch Format danych
	 * @deprecated
	 * @return mixed
	 */
	public function fetch($fetch = null)
	{
		return $this->get()->fetchAll($fetch);
	}

	/**
	 * Pobranie danych otrzymanych w wyniku zapytania
	 * @param int $fetch Format danych
	 * @return mixed
	 */
	public function fetchAll($fetch = null)
	{
		return $this->get()->fetchAll($fetch);
	}

	/**
	 * Zwraca tablice klucz => wartosc aktualnego wiersza
	 * @return mixed
	 */
	public function fetchPair()
	{
		return $this->get()->fetchPair();
	}

	/**
	 * Zwraca tablice asocjacyjna w postacii klucz => wartosc danego zbioru
	 * Jezeli dany klucz istnieje w tablicy, zostanie nadpisany
	 * @return mixed
	 */
	public function fetchPairs()
	{
		return $this->get()->fetchPairs();
	}

	/**
	 * Z kazdego rekordu zbioru danych odczytuje wartosc danej kolumny i przypisuje do tablicy
	 * W parametrze mozna przekazac nazwe kolumny, ktorej wartosc ma byc pobierana
	 * @param string|null $column
	 * @return mixed
	 */
	public function fetchCol($column = null)
	{
		return $this->get()->fetchCol($column);
	}

	public function fetchRow()
	{
		return $this->get()->fetchRow();
	}

	public function fetchAssoc()
	{
		return $this->get()->fetchAssoc();
	}

	public function fetchArray()
	{
		return $this->get()->fetchArray();
	}

	public function fetchObject()
	{
		return $this->get()->fetchObject();
	}

	public function fetchField($field)
	{
		return $this->get()->fetchField($field);
	}
}
?>