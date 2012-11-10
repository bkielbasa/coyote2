<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa obslugi zapytania SQL
 */
class Sql_Query extends Sql_Query_Abstract
{
	/**
	 * ID polaczenia 
	 */
	protected $connection_id;
	/** 
	 * Instancja klasy profilera
	 */
	protected $profiler;
	/**
	 * Rezultat zapytania SQL
	 */
	protected $result;
	/**
	 * Aktualne zapytanie SQL
	 */
	protected $sql;

	/**
	 * Funkcja wysylajaca zapytanie do serwera SQL
	 */
	protected function query()
	{
		$this->result = @pg_query($this->sql, $this->connection_id);

		if (!$this->result)
		{ 
			$this->error();
		}
	}	

	/**
	 * Metoda pobiera rezultat zapytania w postacii postaci asocjacyjnej
	 * @param bool $result Wartosc TRUE oznacza, iz zwrocona bedzie wielowymiarowa tablica wartosci
	 * @return mixed
	 * @deprecated
	 */
	public function result($result = false)
	{
		$cache = class_exists('cache') ? core::get_instance()->cache : null;

		if ($cache)
		{ 
			if (isset($cache->sql_rowset[$this->result]))
			{ 
				return ($cache->sql_rowset[$this->result]);
			}
		}

		if ($result)
		{
			$rowset = array();
			while ($row = @pg_fetch_assoc($this->result))
			{
				$rowset[] = $row;
			}
			return ($rowset);
		}
		return @pg_fetch_assoc($this->result);
	}

	/**
	 * Metoda zwraca rezultat wykonania zapytania w postacii obiektu
	 * @deprecated
	 */
	public function result_object()
	{
		return @pg_fetch_object($this->result);
	}	

	/**
	 * Zwraca dane 
	 * @return mixed 
	 */
	public function fetchRow()
	{
		return @pg_fetch_row($this->result);
	}

	/** 
	 * Zwraca dane w postaci tablicy asocjacyjnej
	 * @return mixed
	 */
	public function fetchAssoc()
	{
		return @pg_fetch_assoc($this->result);
	}

	/**
	 * Zwraca dane w postaci obiektu
	 * @return mixed
	 */
	public function fetchObject()
	{
		return @pg_fetch_object($this->result);
	}

	/**
	 * Zwraca dane w postaci tablicy
	 * @return mixed
	 */
	public function fetchArray()
	{
		return @pg_fetch_array($this->result);
	}

	/**
	 * Zwraca ilosc rekordow z ostatniego zapytania
	 */
	public function rows()
	{
		return @pg_num_rows($this->result);
	}

	/** 
	 * Zwraca ilosc pol z ostatniego zapytania
	 */
	public function fields()
	{
		return @pg_num_fields($this->result);
	}

	/** 
	 * Zwraca ilosc rekordow uaktualnionych w ostatnim zapytaniu
	 */
	public function affected()
	{
		return @pg_affected_rows($this->connection_id);
	}

	/** 
	 * Zwolnienie rezultatu zapytania 
	 */
	public function free()
	{
		return @pg_free_result($this->result);
	}

	public function field($field)
	{
		$row = @pg_fetch_array($this->result, MYSQL_ASSOC);
		// cofniecie wskaznika wynikow 
		@pg_data_seek($this->result, 0);

		return $row[$field];
	}

	/** 
	 * Metoda generuje komunikat bledu, a nastepnie generuje odpowiedni wyjatek
	 */
	private function error()
	{
		$message  = '<b>MySQL error</b>: ' . @pg_last_error($this->connection_id) . '<br />';
		$message .= '<code>' . $this->sql . '</code>';

		Core::get_instance()->db->transaction('ROLLBACK');

		throw new SQLQueryException($message);
	}	
}

/**
 * Warstwa obslugi bazy danych
 */
class Db_Mysql implements IDB
{
	/**
	 * ID polaczenia z baza danych
	 */
	private $connection_id;
	/**
	 * Status transakcji (true - rozpoczeta)
	 */
	private $transaction;
	/**
	 * Instancja klasy SQL_Query
	 */
	public $query;

	/**
	 * Metoda laczenia z baza danych
	 * @param string $sql_server Host bazy danych
	 * @param string $sql_login Login bazy danych
	 * @param string $sql_password Haslo bazy danych
	 * @param string $database Nazwa bazy danych
	 * @param int $port Port (opcjonalnie)
	 */
	public function connect($sql_server, $sql_login, $sql_password, $database, $port = false)
	{
		/* sprawdzenie, czy php obsluguje mysql'a */
		if (!extension_loaded('pgsql'))
		{
			trigger_error('PostgreSQL is not supported in that PHP version', E_USER_ERROR);
		}		
		$connection_ary = array(
			'host=' . $sql_server,
			'dbname=' . $database,
			'user=' . $sql_login,
			'password=' . $sql_password
		);
		if ($port)
		{
			/* jezeli user okreslil port - dodajemy nr. portu do hosta */
			$connection_ary[] = 'port=' . $port;
		}	

		/* proba polaczenia sie - jezeli nie powiedzie sie sukcesem wygenerowany zostanie wyjatek */
		if (!$this->connection_id = @pg_connect(implode(' ', $connection_ary)))
		{
			throw new SQLCouldNotConnectException($sql_server, $sql_login);
		} 
		$this->query = new Sql_Query($this->connection_id);
	}

	/** 
	 * Zamyka polaczenie z baza danych
	 */
	public function close()
	{
		@pg_close($this->connection_id);
	}

	/**
	 * Zwraca wersje serwera 
	 */
	public function version()
	{
		$version = @pg_version();
		return $version['client'];
	}	
	
	/** 
	 * Metoda zarzadzania transakcja
	 * @param string $status (BEGIN, COMMIT, ROLLBACK)
	 */
	public function transaction($status = 'BEGIN')
	{
		$result = false;

		switch ($status)
		{
			/* rozpoczecie transakcji */
			case 'BEGIN':

				/* transakcja rozpoczeta */
				$this->transaction = true;
				/* wyslanie zapytania */
				$result = pg_query($this->connection_id, 'BEGIN');
			break;

			/* zaakceptowanie zmian */
			case 'COMMIT':

				/* transakcja zakonczona */
				$this->transaction = false;
				/* wyslanie zapytania */
				$result = pg_query($this->connection_id, 'COMMIT');
			break;

			/* cofniecie zmian */
			case 'ROLLBACK':

				if ($this->transaction)
				{
					/* transakcja zakonczona */
					$this->transaction = false;
					/* wyslanie zapytania */
					$result = pg_query($this->connection_id, 'ROLLBACK');
				}
			break;

			default:

			$result = true;
		}

		return $result;
	}

	/** 
	 * Zwraca ID ostatnio wstawionego rekordu
	 */
	public function nextId()
	{
		if (preg_match("/^INSERT[\t\n ]+INTO[\t\n ]+([a-z0-9\_\-]+)/is", $this->query->sql, $tablename))
		{
			$sql = "SELECT currval('" . $tablename[1] . "_seq') AS last_value";
			$temp_q_id = @pg_query($this->connection, $sql);

			if (!$temp_q_id)
			{
				return false;
			}

			$temp_result = @pg_fetch_assoc($temp_q_id, NULL);
			@pg_free_result($query_id);

			return ($temp_result) ? $temp_result['last_value'] : false;
		}
	}
}
?>