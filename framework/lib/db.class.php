<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Osluga wyjatku wyrzucanego w przypadku blednego polaczenia z DB
 */
class SQLCouldNotConnectException extends Exception
{
	function __construct($sql_server, $sql_login)
	{
		$message = "Could not connect to the database server: $sql_login@$sql_server <br />\n";
		$message .= "Maybe server does not work? Or password is wrong?\n";

		parent::__construct($message, 0);
	}
}

/**
 * Obsluga wyjatku wystepujacego w przypadku wystapienia bledu zwiazanego z blednym zapytaniem SQL
 */
class SQLQueryException extends Exception
{
	/**
	 * Zapytanie SQL
	 */
	protected $sql;
	/**
	 * Kod bledu
	 */
	protected $code;
	/**
	 * Komunikat bledu
	 */
	protected $message;

	public function setSqlQuery($sql)
	{
		$this->sql = $sql;
	}

	public function getSqlQuery()
	{
		return $this->sql;
	}

	public function setErrorCode($code)
	{
		$this->code = $code;
	}

	public function getErrorCode()
	{
		return $this->code;
	}

	public function setErrorMessage($message)
	{
		$this->message = $message;
	}

	public function getErrorMessage()
	{
		return $this->message;
	}

	function __construct($message, $code = 0, $sql = '')
	{
		$this->setErrorMessage($message);
		$this->setErrorCode($code);
		$this->setSqlQuery($sql);

		$message .= ' (errno: ' . $this->getErrorCode() . ')<br />';
		$message .= '<code>' . $this->getSqlQuery() . '</code>';

		parent::__construct($message, $this->getErrorCode());
	}
}

class SQLCouldNotSelectDbException extends Exception {}

/**
 * Interfejs drivera bazy danych
 */
interface IDB
{
	public function connect($sql_server, $sql_login, $sql_password, $database, $port = false);
	public function close();
	public function query($sql);
	public function begin();
	public function commit();
	public function rollback();
	public function lock();
	public function unlock();
	public function quote($value);
	public function version();
}

abstract class Db_Result implements ArrayAccess, Iterator, Countable
{
	/**
	 * Aktualna pozycja kursora w tablicy wynikow
	 */
	protected $position = 0;
	/**
	 * Liczba rekordow pobranyc w wyniku zapytania SELECT lub
	 * liczba rekordow uaktualnionych lub skasowanych
	 */
	protected $totalRows;
	/**
	 * Aktualne zapytanie SQL
	 */
	protected $sql;

	/*
	 * Nazwy funkcji PHP odpowiadajace pobieraniu danych konkretnego typu
	 */
	protected $methods = array(
			Db::FETCH_ROW			=>	'fetchRow',
			Db::FETCH_ASSOC			=>	'fetchAssoc',
			Db::FETCH_ARRAY			=>	'fetchArray',
			Db::FETCH_OBJ			=>	'fetchObject'
	);
	/**
	 * Domyslna metoda prezentacji wynikow
	 */
	protected $method				= Db::FETCH_ASSOC;

	/**
	 * Ustawia domyslna metoda zwracania danych
	 * Domyslnie formatem jest tablica asocjacyjna
	 * @param string $metch
	 * @return object
	 */
	public function setFetchMethod($method = Db::FETCH_ASSOC)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * Zwraca domyslny format zwracanych danych (tablica asocjacyjna, obiekt itp)
	 * @return string
	 */
	public function getFetchMethod()
	{
		return $this->method;
	}

	/**
	 * Pobranie danych otrzymanych w wyniku zapytania
	 * @param int $fetch Format danych
	 * @return mixed
	 */
	public function fetchAll($fetch = null)
	{
		if ($fetch == null)
		{
			$fetch = $this->getFetchMethod();
		}
		$rowset = array();

		while ($row = call_user_func(array(&$this, $this->methods[$fetch]), $this->result))
		{
			$rowset[] = $row;
		}
		return $rowset;
	}

	/**
	 * Pobranie danych otrzymanych w wyniku zapytania
	 * @param int $fetch Format danych
	 * @deprecated
	 * @return mixed
	 */
	public function fetch($fetch = null)
	{
		return $this->fetchAll($fetch);
	}

	abstract function seek($offset);
	abstract function fetchRow();
	abstract function fetchAssoc();
	abstract function fetchArray();
	abstract function fetchObject();

	/**
	 * Zwraca wartosc danego pola aktualnego wiersza
	 * @param string $field Nazwa pola
	 * @return mixed
	 */
	public function fetchField($field)
	{
		$row = $this->current();
		return $row[$field];
	}

	/**
	 * Zwraca tablice klucz => wartosc aktualnego wiersza
	 * @return mixed
	 */
	public function fetchPair()
	{
		$result = $this->fetchRow();

		return array(
			$result[0]	=> $result[1]
		);
	}

	/**
	 * Zwraca tablice asocjacyjna w postacii klucz => wartosc danego zbioru
	 * Jezeli dany klucz istnieje w tablicy, zostanie nadpisany
	 * @return mixed
	 */
	public function fetchPairs()
	{
		$result = array();
		while ($row = $this->fetchRow())
		{
			$result[$row[0]] = $row[1];
		}

		return $result;
	}

	/**
	 * Z kazdego rekordu zbioru danych odczytuje wartosc danej kolumny i przypisuje do tablicy
	 * W parametrze mozna przekazac nazwe kolumny, ktorej wartosc ma byc pobierana
	 * @param string|null $column
	 * @return mixed
	 */
	public function fetchCol($column = null)
	{
		$result = array();
		while ($row = $this->fetchArray())
		{
			$result[] = $column != null ? $row[$column] : $row[0];
		}

		return $result;
	}

	/**
	 * Implementacja ArrayAccess
	 */
	public function offsetGet($offset)
	{
		if (!$this->seek($offset))
		{
			return false;
		}
		return call_user_func(array(&$this, $this->methods[$this->method]));
	}

	/**
	 * Implementacja ArrayAccess
	 */
	public function offsetExists($offset)
	{
		return $this->seek($offset);
	}

	/**
	 * Implementacja ArrayAccess
	 */
	final public function offsetSet($offset, $value)
	{
		throw new Exception('Database result is read only');
	}

	/**
	 * Implementacja ArrayAccess
	 */
	final public function offsetUnset($offset)
	{
		throw new Exception('Database result is read only');
	}

	/**
	 * Implementacja Iterator
	 */
	public function current()
	{
		return $this->offsetGet($this->position);
	}

	/**
	 * Implementacja Iterator
	 */
	public function rewind()
	{
		$this->seek($this->position = 0);
		return $this;
	}

	/**
	 * Implementacja Iterator
	 */
	public function next()
	{
		$this->seek(++$this->position);
		return $this;
	}

	/**
	 * Implementacja Iterator
	 */
	public function prev()
	{
		$this->seek(--$this->position);
		return $this;
	}

	/**
	 * Implementacja Iterator
	 */
	public function key()
	{
		return $this->position;
	}

	/**
	 * Implementacja Iterator
	 */
	public function valid()
	{
		return $this->offsetExists($this->position);
	}

	/**
	 * Implementacja Countable
	 */
	public function count()
	{
		return $this->totalRows;
	}

	public function getTotalRows()
	{
		return $this->totalRows;
	}

	public function getAffected()
	{
		return $this->totalRows;
	}
}

/**
 * Klasa zwierajca metody obslugi i tworzenia zapytan SQL
 */
class Db
{
	const FETCH_ROW = 1;
	const FETCH_ASSOC = 2;
	const FETCH_ARRAY = 3;
	const FETCH_OBJ = 4;

	const DISTINCT       = 'distinct';
    const COLUMNS        = 'columns';
    const FROM           = 'from';
    const WHERE          = 'where';
	const IN			 = 'in';
    const GROUP          = 'group';
    const HAVING         = 'having';
    const ORDER          = 'order';
    const LIMIT_COUNT    = 'limitcount';
    const LIMIT_OFFSET   = 'limitoffset';
    const FOR_UPDATE     = 'forupdate';

    const INNER_JOIN     = 'inner join';
    const LEFT_JOIN      = 'left join';
    const RIGHT_JOIN     = 'right join';

    const SQL_WILDCARD   = '*';
    const SQL_SELECT     = 'SELECT';
    const SQL_FROM       = 'FROM';
    const SQL_WHERE      = 'WHERE';
	const SQL_IN		 = 'IN';
    const SQL_DISTINCT   = 'DISTINCT';
    const SQL_GROUP_BY   = 'GROUP BY';
    const SQL_ORDER_BY   = 'ORDER BY';
    const SQL_HAVING     = 'HAVING';
    const SQL_FOR_UPDATE = 'FOR UPDATE';
    const SQL_AND        = 'AND';
    const SQL_AS         = 'AS';
    const SQL_OR         = 'OR';
    const SQL_ON         = 'ON';
    const SQL_ASC        = 'ASC';
    const SQL_DESC       = 'DESC';

	private $adapter;

	function __construct($adapter)
	{
		Load::loadFile('lib/db/abstract.class.php');
		if ((include_once("lib/db/{$adapter}.class.php")) === false)
		{
			throw new Exception("Adapter $adapter does not exists");
		}
		$class = "Db_$adapter";
		$this->adapter = new $class;

		if (!$this->adapter instanceof IDB)
		{
			throw new Exception("Class $class must implements IDB interface");
		}
		$this->reset();
	}

	/**
	 * Metoda wykonuje zapytanie
	 * @param string $sql
	 * @return mixed
	 */
	public function query($sql)
	{
		if (func_num_args() > 1)
		{
			$args = func_get_args();
			$sql = array_shift($args);

			$sql = $this->adapter->bind($sql, $args);
		}
		return $this->adapter->query($sql);
	}

	public function fetchAll($sql, $fetchMethod = null)
	{
		return $this->query($sql)->fetchAll($fetchMethod);
	}

	/**
	 * Zwraca tablice klucz => wartosc aktualnego wiersza
	 * @param string $sql Zapytanie SQL
	 * @return mixed
	 */
	public function fetchPair($sql)
	{
		return $this->query($sql)->fetchPair();
	}

	/**
	 * Zwraca tablice asocjacyjna w postacii klucz => wartosc danego zbioru
	 * Jezeli dany klucz istnieje w tablicy, zostanie nadpisany
	 * @param string $sql Zapytanie SQL
	 * @return mixed
	 */
	public function fetchPairs($sql)
	{
		return $this->query($sql)->fetchPairs();
	}

	/**
	 * Z kazdego rekordu zbioru danych odczytuje wartosc danej kolumny i przypisuje do tablicy
	 * W parametrze mozna przekazac nazwe kolumny, ktorej wartosc ma byc pobierana
	 * @param string $sql Zapytanie SQL
	 * @param string|null $column
	 * @return mixed
	 */
	public function fetchCol($sql, $column = null)
	{
		return $this->query($sql)->fetchCol($column);
	}

	public function fetchRow($sql)
	{
		return $this->query($sql)->fetchRow();
	}

	public function fetchAssoc($sql)
	{
		return $this->query($sql)->fetchAssoc();
	}

	public function fetchArray($sql)
	{
		return $this->query($sql)->fetchArray();
	}

	public function fetchObject($sql)
	{
		return $this->query($sql)->fetchObject();
	}

	public function fetchField($sql, $field)
	{
		return $this->query($sql)->fetchField($field);
	}

	/**
	 * Magic function
	 * Uzywana do wywolan metod z adaptera
	 */
	public function __call($method, $options)
	{
		if (!method_exists($this->adapter, $method))
		{
			throw new Exception("Method $method does not exists in Db driver");
		}
		return call_user_func_array(array($this->adapter, $method), $options);
	}

	public function __get($field)
	{
		return $this->adapter->$field;
	}
}

?>