<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Warstwa obslugi bazy danych poprzez driver mysql
 */
class Db_Mysql extends Db_Abstract implements IDB
{
	/**
	 * ID polaczenia z baza danych
	 */
	protected $connection_id;
	/**
	 * Status transakcji (true - rozpoczeta)
	 */
	protected $transaction;
	/**
	 * Instancja profilera SQL
	 */
	protected $profiler;

	/**
	 * Konstruktora klasy - sprawdzenie czy w PHP dostepna jest odpowiednia biblioteka
	 */
	function __construct()
	{
		/* sprawdzenie, czy php obsluguje mysql'a */
		if (!extension_loaded('mysql'))
		{
			trigger_error('MySQL is not supported in that PHP version', E_USER_ERROR);
		}
		
		// utworzenie instancji profilera
		$this->profiler = new Db_Profiler;		
	}
	
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
		/* jezeli user okreslil port - dodajemy nr. portu do hosta */
		$sql_server .= ( $port ? ':' . $port : '' );

		/* proba polaczenia sie - jezeli nie powiedzie sie sukcesem wygenerowany zostanie wyjatek */
		if (!$this->connection_id = @mysql_connect($sql_server, $sql_login, $sql_password) )
		{
			throw new SQLCouldNotConnectException($sql_server, $sql_login);
		} 

		/*
		   proba wybrania bazy danych - jezeli sie nie powiedzie -
		   proba zalozenia bazy i podlaczenia sie - jesli sie nie powiedzie - wyswietlamy blad
		*/
		if (!@mysql_select_db($database, $this->connection_id))
		{
			if (!@mysql_query('CREATE DATABASE ' . $database, $this->connection_id))
			{				
				throw new SQLCouldNotSelectDbException('Could not create database');
			}
			if (!@mysql_select_db($database, $this->connection_id))
			{
				throw new SQLCouldNotSelectDbException('Could not create database');
			}
		} 
	}

	/**
	 * Meta sprawdza czy nawiazane jest polacznie z baza danych. Jezeli zostalo
	 * przerwane - probuje je odnowic. 
	 * @return bool True w przypadku, gdy jest polaczenie - False, gdy go brak
	 */
	public function ping()
	{
		return @mysql_ping($this->connection_id);
	}

	/**
	 * Zwolnienie zasobow, zamkniecie polaczenia z serwerem
	 */
	function __destruct()
	{
		$this->close();
	}

	/**
	 * Ustawienie kodowania znakow dla bazy danych
	 * @param string $charset
	 */
	public function setCharset($charset)
	{
		/**
		 * Ustawienie kodowania znakow bazy danych
		 */
		@mysql_query('SET NAMES ' . $charset);		
	}

	/** 
	 * Zamyka polaczenie z baza danych
	 */
	public function close()
	{
		@mysql_close($this->connection_id);
	}

	/**
	 * Wysyla zapytanie do bazy danych oraz zwraca rezultat w postaci obiektu
	 * Mysql_Result
	 * @param string $sql Zapytanie SQL
	 * @return object
	 */
	public function query($sql)
	{		
		// dodanie zapytania do profilera
		// zwracane jest QueryID (qid)
		$qid = $this->profiler->start($sql);

		$result = @mysql_query($sql, $this->connection_id);
		if (!$result)
		{
			throw new SQLQueryException(@mysql_error($this->connection_id), @mysql_errno($this->connection_id), $sql);
		}
		$this->profiler->stop($qid);

		// zwrocenie obiektu klasy Mysql_Result
		return new Mysql_Result($result, $sql, $this->connection_id);
	}

	/**
	 * Zwraca wersje serwera 
	 */
	public function version()
	{
		return @mysql_get_server_info();
	}	

	/**
	 * Rozpoczecie transakcji
	 */
	public function begin()
	{
		$this->transaction = true;
		@mysql_query('BEGIN', $this->connection_id);
	}

	/**
	 * Akceptacja transakcji
	 */
	public function commit()
	{
		if ($this->transaction)
		{
			$this->transaction = false;
			@mysql_query('COMMIT', $this->connection_id);
		}
	}

	/**
	 * Cofniecie transakcji
	 */
	public function rollback()
	{
		if ($this->transaction)
		{
			$this->transaction = false;
			@mysql_query('ROLLBACK', $this->connection_id);
		}
	}
	

	/** 
	 * Blokowanie tabel podanych w parametrze. 
	 * W parametrach podajemy liste tabel oddzielona przecinkami
	 * @example
	 * <code>
	 * $this->lock('foo WRITE', 'bar READ');
	 * </code>
	 * @return bool True lub False 
	 */
	public function lock()
	{
		$args = func_get_args();
		return @mysql_query('LOCK TABLES ' . implode(', ', $args), $this->connection_id);
	}

	/**
	 * Odblokowuje tabele zablokowane w danym watku
	 * @return bool True lub False
	 */
	public function unlock()
	{
		return @mysql_query('UNLOCK TABLES', $this->connection_id);
	}

	/** 
	 * Zwraca ID ostatnio wstawionego rekordu
	 */
	public function nextId()
	{
		return @mysql_insert_id($this->connection_id);
	}

	/** 
	 * Walidacja danych, metoda wykonuje rzutowanie na okreslony typ danych 
	 * W przypadku lancucha znakow, dodaje znak \ przed znakami specjalnymi
	 * @param $value
	 * @return mixed
	 */
	public function quote($value)
	{
		if (is_null($value))
		{
			return 'NULL';
		}
		else if (is_string($value))
		{
			return '"' . @mysql_real_escape_string($value, $this->connection_id) . '"';
		}
		else if (is_bool($value))
		{
			return (int)$value;
		}
		else if (is_float($value))
		{
			return (float)$value;
		}
		else
		{
			return (int)$value;
		}
	}
}

class Mysql_Result extends Db_Result
{
	function __construct($result, $sql, $connection_id)
	{
		$this->result = $result;
		$this->sql = $sql;
		
		if (is_resource($result))
		{
			$this->totalRows = @mysql_num_rows($this->result);
		}
		elseif (is_bool($result))
		{
			$this->nextId = @mysql_insert_id($connection_id);
			$this->totalRows = @mysql_affected_rows($connection_id);
		}
	}

	/**
	 * Zwolnienie zasobow przy usunieciu klasy przez GC
	 */
	function __destruct()
	{
		$this->free();
	}

	/** 
    * Zwolnienie rezultatu zapytania 
    */
    public function free()
    {
		if (is_resource($this->result))
		{
			return @mysql_free_result($this->result);
		}
    }	

	public function seek($offset)
	{
		if ($offset < @mysql_num_rows($this->result))
		{
			return @mysql_data_seek($this->result, $offset);
		}
		else
		{
			return false;
		}
	}

	public function fetchRow()
	{
		return @mysql_fetch_row($this->result);
	}

	public function fetchAssoc()
	{
		return @mysql_fetch_assoc($this->result);
	}

	public function fetchArray()
	{
		return @mysql_fetch_array($this->result);
	}

	public function fetchObject()
	{		
		return @mysql_fetch_object($this->result);
	}
}

?>