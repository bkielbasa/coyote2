<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Db_PDO implements IDB
{
	protected $driver = 'mysql';
	protected $pdo;

	public function setDriver($driver)
	{
		$this->driver = $driver;
	}

	public function connect($sql_server, $sql_login, $sql_password, $database, $port = false)
	{
		if (!extension_loaded('pdo'))
		{
			trigger_error('PDO is not supported in that PHP version', E_USER_ERROR);
		}	

		$dns = "{$this->driver}:dbname={$database};host={$sql_server}";
		if ($port)
		{
			$dns .= ';port=' . $port;
		}

		try
		{
			$this->pdo = new PDO($dns, $sql_login, $sql_password);
		}
		catch (PDOException $e)
		{
			throw new SQLCouldNotConnectException($sql_server, $sql_login);
		}
	}

	public function close()
	{
	}

	public function query($sql)
	{
		try
		{
			$result = $this->pdo->query($sql);
		}
		catch (PDOException $e)
		{
			throw new SQLQueryException($e->getMessage());
		}
		return new Pdo_Result($result, $sql);
	}

	public function begin()
	{
		$this->pdo->beginTransaction();
		$this->transaction = true;
	}

	public function commit()
	{
		$this->pdo->commit();
		$this->transaction = false;
	}

	public function rollback()
	{
		$this->pdo->rollBack();
		$this->transaction = false;
	}


	public function lock()
	{
	}

	public function unlock()
	{
	}

	public function quote($value)
	{
		return $this->pdo->quote($value);
	}

	public function version()
	{
		return $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
	}
}


class Pdo_Result extends Db_Result
{
	protected $result;
	protected $sql;
	protected $totalRows;
	protected $affected;
	protected $nextId;

	function __construct($result, $sql)
	{
		$this->sql = $sql;
		$this->result = $result;

		if (is_object($result))
		{
			if (preg_match('/^SELECT|PRAGMA|EXPLAIN/i', $sql))
			{
				$this->totalRows = $this->totalRows();
			}
			elseif (preg_match('/^DELETE|INSERT|UPDATE/i', $sql))
			{
				$this->affected = $result->rowCount();
			}
		}
	}

	private function totalRows()
	{
		$count = 0;
		while ($this->result->fetch())
		{
			++$count;
		}
		$this->result->execute();

		return $count;
	}

	public function seek($offset)
	{

	}

	public function fetchRow()
	{

	}

	public function fetchAssoc()
	{
		return $this->result->fetch(PDO::FETCH_ASSOC);
	}

	public function fetchArray()
	{
		return $this->result->fetch(PDO::FETCH_NUM);
	}

	public function fetchObject()
	{		
		return $this->result->fetch(PDO::FETCH_OBJ);
	}
}
?>