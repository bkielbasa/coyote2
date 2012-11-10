<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Obsluga cache APC
 */
class Cache_Apc implements Cache_Interface
{
	protected $prefix;
	
	function __construct()
	{
		if (!extension_loaded('apc'))
		{
			trigger_error('APC is not available');
		}
	}
	
	/**
	 * Ustawia prefix dla cache (wowczas kilka projektow moze dzielic ten sam katalog cache)
	 * @param string $prefix
	 */	
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}
	
	/**
	 * Zwraca prefix
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}
	
	/**
	 * @deprecated
	 */
	public function get($id)
	{
		return $this->load($id);
	}
	
	/**
	 * @deprecated
	 */
	public function put($id, $data, $lifetime = 0)
	{
		return $this->save($id, $data, $lifetime);
	}

	/**
	 * Pobranie danych z tablicy
	 * @param string $id Nazwa klucza
	 * @return string
	 */
	public function load($id)
	{
		$data = @apc_fetch($this->getPrefix() . '_' . $id);
		return !$data ? null : $data;
	}

	/**
	 * Umieszczenie danych w cache
	 * @param string $id ID danych umieszczonych w cache
	 * @param mixed $value Dane do zapisu
	 * @param int $lifetime Czas po ktorym dane zapisane w pliku zostana przedawnione
	 */
	public function save($id, $value, $lifetime = 0)
	{
		return @apc_store($this->getPrefix() . '_' . $id, $value, $lifetime);
	}
	
	public function start($id)
	{
		$data = $this->load($id);

		if ($data)
		{
			echo $data;
			return true;
		}

		ob_start();
		return false;
	}

	public function end($id, $lifetime = 0)
	{
		$content = ob_get_contents();
		ob_flush();

		$this->save($id, $content, $lifetime);
	}

	/**
	 * Usuniecie zmiennej znajdujacej sie w cache
	 * @param string $id Nazwa klucza
	 * @param int $mode Tryb usuwania. 
	 * 
	 * @example
	 * <code>
	 * $this->remove('foo*', Cache::PATTERN); // usunie klucze foobar, foofoo itp
	 * $this->remove('foo', Cache::ID); // usunie tylko klucz id
	 * $this->remove('', Cache::ID); // usunie caly cache
	 * </code>
	 * @return bool
	 */
	public function remove($id = '', $mode = Cache::ID)
	{
		$result = true;
		if ($mode == Cache::ID)
		{		
			if (!$id)
			{
				$result = @apc_clear_cache('user');
			}
			else
			{
				$result = @apc_delete($this->getPrefix() . '_' . $id);
			}
		}
		elseif ($mode == Cache::PATTERN)
		{
			$apc = new APCIterator('user');
			$regexp = $this->getPrefix() . '_' . str_replace('*', '.*', $id);
			
			foreach ($apc as $key => $value)
			{
				if (preg_match('#' . $regexp . '#i', $key))
				{
					@apc_delete($key);
				}
			}						
		}
		
		return $result;
	}

	/**
	 * @deprecated
	 */
	public function destroy($id = '')
	{
		return $this->remove($id);
	}

	/**
	 * Metoda sprawdza, czy zmienna o podanej nazwie znajduje sie w cache
	 * @param string $id Nazwa zmiennej
	 */
	public function exists($id)
	{
		if (function_exists('apc_exists'))
		{
			return (bool) @apc_exists($this->getPrefix() . '_' . $id);
		}
		else
		{
			return @apc_fetch($this->getPrefix() . '_' . $id) ? true : false;
		}
	}
}

?>