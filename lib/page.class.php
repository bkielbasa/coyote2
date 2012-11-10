<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Page extends Context
{
	const HTML			=			1;
	const PLAIN			=			2;
	const CSS			=			3;
	const XML			=			4;
	const JAVASCRIPT	=			5;
	const XHTML			=			6;

	private $connector;

	private static $ommitDelete = false;
	private static $ommitUnpublished = false;
	private static $enableRedirect = false;
	private static $enable404 = false;

	function __construct(Connector_Abstract $connector)
	{
		$this->connector = $connector;
	}

	function __destruct()
	{
		unset($this->connector);
	}

	public function __save()
	{
		$result = true;

		try
		{
			$this->db->begin();

			$this->trigger('onBeforeSave');
			$this->connector->save();
			$this->trigger('onAfterSave');

			$this->db->commit();
		}
		catch (Exception $e)
		{
			$this->db->rollback();
			$result = false;

			Log::add($e->getMessage(), E_ERROR);
		}

		return $result;
	}

	public function __move($parentId)
	{
		$result = true;

		try
		{
			$this->db->begin();

			$this->trigger('onBeforeMove');
			$this->connector->move($parentId);
			$this->trigger('onAfterMove');

			$this->db->commit();
		}
		catch (Exception $e)
		{
			$this->db->rollback();
			$result = false;

			Log::add($e->getMessage(), E_ERROR);
		}

		return $result;
	}

	public function __delete()
	{
		$result = true;

		try
		{
			$this->db->begin();
			$this->trigger('onBeforeDelete');

			if (!$this->connector->getChildren())
			{
				$this->connector->delete();
			}
			else
			{
				$query = $this->db->select()
								  ->from('page')
								  ->innerJoin('path', "parent_id = " . $this->getId())
								  ->innerJoin('connector', 'connector_id = page_connector')
								  ->where("page_id = child_id")
								  ->order('page_matrix DESC')
								  ->get();

				foreach ($query as $row)
				{
					$className = 'Connector_' . $row['connector_class'];
					$connector = new $className($row);

					$connector->delete();
					unset($connector);
				}
			}

			$this->trigger('onAfterDelete');

			$this->db->commit();
		}
		catch (Exception $e)
		{
			$this->db->rollback();
			$result = false;

			Log::add($e->getMessage(), E_ERROR);
		}

		return $result;
	}

	private function trigger($method)
	{
		if (method_exists($this->connector, $method))
		{
			call_user_func(array(&$this->connector, $method));
		}
	}

	public function __call($method, $args)
	{
		if (method_exists($this, '__' . $method))
		{
			$result = call_user_func_array(array(&$this, '__' . $method), $args);

			return $result;
		}
		elseif (!method_exists($this->connector, $method))
		{
			throw new Exception('Metoda ' .  $method . ' nie istnieje w łączniku ' . get_class($this->connector));
		}
		else
		{
			return call_user_func_array(array(&$this->connector, $method), $args);
		}
	}

	public static function setOmmitDelete($flag)
	{
		self::$ommitDelete = (bool) $flag;
	}

	public static function setOmmitUnpublished($flag)
	{
		self::$ommitUnpublished = (bool) $flag;
	}

	public static function setEnableRedirect($flag)
	{
		self::$enableRedirect = (bool) $flag;
	}

	public static function isRedirectEnabled()
	{
		return self::$enableRedirect;
	}

	public static function setEnable404($flag)
	{
		self::$enable404 = (bool) $flag;
	}

	public static function is404Enabled()
	{
		return self::$enable404;
	}

	/**
	 * Metoda umozliwia "zaladowanie" informacji o stronie, z bazy danych
	 * Wartosc $value moze byc lancuchem lub liczba, w zaleznosci od tego,
	 * czy chcemy znalezc strone po sciezce, czy ID strony
	 * @param int|value		$value
	 */
	public static function load($value)
	{
		$query = self::loadPage($value);

		// strona nie zostala znaleziona
		if (!count($query))
		{
			if (self::isRedirectEnabled() && is_string($value))
			{
				$db = &Core::getInstance()->load->model('page');

				if (!$path = $db->hasMoved($value))
				{
					if (self::is404Enabled())
					{
						$query = self::load404();
					}
				}
				else
				{
					Url::redirect($path, 301);
				}
			}
			else
			{
				if (self::is404Enabled())
				{
					$query = self::load404();
				}
			}

		}
		else
		{
			if (self::$ommitDelete || self::$ommitUnpublished)
			{
				$data = $query->fetchAssoc();

				if (self::$ommitDelete && $data['page_delete'])
				{
					$query = false;
					unset($data);
				}

				if (self::$ommitUnpublished && !@$data['page_publish'])
				{
					$query = false;
					unset($data);
				}

				if (false === $query)
				{
					if (self::is404Enabled())
					{
						$query = self::load404();
					}
				}
			}
		}

		if (!$query || !count($query))
		{
			return false;
		}
		else
		{
			if (!isset($data))
			{
				$data = $query->fetchAssoc();
			}
			$className = 'Connector_' . $data['connector_class'];

			return new Page(new $className($data));
		}
	}

	public static function load404()
	{
		return self::loadPage('404');
	}

	public static function loadError($error)
	{
		return self::loadPage((string) $error);
	}

	private static function loadPage($value)
	{
		$db = &Core::getInstance()->load->model('page');

		if (is_int($value))
		{
			$query = $db->getById($value);
		}
		elseif (is_string($value))
		{
			$query = $db->getByPath($value);
		}
		else
		{
			throw new Exception('Nieprawidłowa ścieżka do strony lub ID');
		}

		return $query;
	}

	public function __toString()
	{
		return get_class($this->connector);
	}
}
?>