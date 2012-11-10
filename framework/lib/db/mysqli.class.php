<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Warstwa obslugi bazy danych poprzez driver mysqli
 */
class Db_Mysqli extends Db_Abstract implements IDB
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
		if (!extension_loaded('mysqli'))
		{
			trigger_error('MySQLi is not supported in that PHP version', E_USER_ERROR);
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
		if (!$this->connection_id = @mysqli_connect($sql_server, $sql_login, $sql_password) )
		{
			throw new SQLCouldNotConnectException($sql_server, $sql_login);
		}

		/*
		   proba wybrania bazy danych - jezeli sie nie powiedzie -
		   proba zalozenia bazy i podlaczenia sie - jesli sie nie powiedzie - wyswietlamy blad
		*/
		if (!@mysqli_select_db($this->connection_id, $database))
		{
			if (!@mysqli_query($this->connection_id, 'CREATE DATABASE ' . $database))
			{				
				throw new SQLCouldNotSelectDbException('Could not create database');
			}
			if (!@mysqli_select_db($this->connection_id, $database))
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
		return @mysqli_ping($this->connection_id);
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
		@mysqli_set_charset($this->connection_id, $charset);
	}

	/** 
	 * Zamyka polaczenie z baza danych
	 */
	public function close()
	{
		@mysqli_close($this->connection_id);
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

		$result = @mysqli_query($this->connection_id, $sql);
		if (!$result)
		{
			throw new SQLQueryException(@mysqli_error($this->connection_id), @mysqli_errno($this->connection_id), $sql);
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
		return @mysqli_get_server_info();
	}	

	/**
	 * Rozpoczecie transakcji
	 */
	public function begin()
	{
		$this->transaction = true;
		@mysqli_autocommit($this->connection_id, false);
	}

	/**
	 * Akceptacja transakcji
	 */
	public function commit()
	{
		if ($this->transaction)
		{
			$this->transaction = false;
			
			@mysqli_commit($this->connection_id);
			@mysqli_autocommit($this->connection_id, true);
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
			@mysqli_rollback($this->connection_id);
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
		return @mysqli_query($this->connection_id, 'LOCK TABLES ' . implode(', ', $args));
	}

	/**
	 * Odblokowuje tabele zablokowane w danym watku
	 * @return bool True lub False
	 */
	public function unlock()
	{
		return @mysqli_query($this->connection_id, 'UNLOCK TABLES');
	}

	/** 
	 * Zwraca ID ostatnio wstawionego rekordu
	 */
	public function nextId()
	{
		return @mysqli_insert_id($this->connection_id);
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
			return '"' . @mysqli_real_escape_string($this->connection_id, $value) . '"';
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
		
		if (is_object($result))
		{
			$this->totalRows = mysqli_num_rows($this->result);
		}
		elseif (is_bool($result))
		{
			$this->nextId = @mysqli_insert_id($connection_id);
			$this->totalRows = @mysqli_affected_rows($connection_id);
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
		return @mysqli_free_result($this->result);
    }	

	public function seek($offset)
	{
		return @mysqli_data_seek($this->result, $offset);
	}

	public function fetchRow()
	{
		return @mysqli_fetch_row($this->result);
	}

	public function fetchAssoc()
	{
		return @mysqli_fetch_assoc($this->result);
	}

	public function fetchArray()
	{
		return @mysqli_fetch_array($this->result);
	}

	public function fetchObject()
	{		
		return @mysqli_fetch_object($this->result);
	}
}

?>