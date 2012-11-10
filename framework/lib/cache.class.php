<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Obsluga Cache
 */
class Cache
{
	const ID			=			1;
	const PATTERN		=			2;
	
	/**
	 * Instancja klasy adaptera
	 */
	private $adapter;

	/**
	 * Tworzenie instancji klasy cache
	 * @param object|string $adapter Instancja adaptera cache lub nazwa klasy adaptera
	 */
	function __construct($adapter = null, $prefix = null)
	{
		if (!$adapter)
		{
			if (Config::getItem('cache.adapter'))
			{
				$className = 'Cache_' . Config::getItem('cache.adapter');
				$adapter = new $className;
			}
			else
			{
				$adapter = new Cache_File;
			}
		}
		elseif (is_string($adapter))
		{
			if (strpos($adapter, 'Cache_') === false)
			{
				$adapter = 'Cache_' . $adapter;
			}
			$adapter = new $adapter;
		}
		$this->setAdapter($adapter);
		
		if ($prefix === null)
		{
			$prefix = Config::getItem('cache.prefix');
		}
		$this->setPrefix($prefix);
	}

	/**
	 * Ustawienie adaptera dla cache
	 * @param object $adapter
	 */
	public function setAdapter(Cache_Interface $adapter)
	{
		$this->adapter = $adapter;
	}
	
	public function __call($method, $args)
	{
		return call_user_func_array(array(&$this->adapter, $method), $args);
	}

	public function __get($id)
	{
		return $this->adapter->load($id);
	}

	public function __set($id, $value)
	{
		return $this->adapter->save($id, $value);
	}

	public function __isset($id)
	{
		return $this->adapter->exists($id);
	}

	public function __unset($id)
	{
		$this->adapter->remove($id);
	}
}
?>