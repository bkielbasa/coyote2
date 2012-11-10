<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Obsluga cache eAcceleratora
 */
class Cache_EAccelerator implements Cache_Interface
{
	private $prefix;
	
	function __construct()
	{
		if (!extension_loaded('eaccelerator'))
		{
			trigger_error('eAccelerator is not available');
		}
		ini_set('eaccelerator.admin_allowed_path', Config::getBasePath());
		ini_set('eaccelerator.admin_allowed_path', Config::getRootPath());
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
	 * Pobranie danych z tablicy
	 * @param string $id Nazwa klucza
	 * @return string
	 * @deprecated
	 */
	public function get($id)
	{
		return $this->load($id);
	}
	
	/**
	 * Pobranie danych z tablicy
	 * @param string $id Nazwa klucza
	 * @return string
	 */
	public function load($id)
	{
		return eaccelerator_get($this->getPrefix() . '_' . $id);
	}	

	/**
	 * Umieszczenie danych w cache
	 * @param string $id ID danych umieszczonych w cache
	 * @param mixed $value Dane do zapisu
	 * @param int $lifetime Czas po ktorym dane zapisane w pliku zostana przedawnione
	 * @deprecated
	 */
	public function put($id, $value, $lifetime = 0)
	{
		return $this->save($id, $data, $lifetime);		
	}
	
	/**
	 * Umieszczenie danych w cache
	 * @param string $id ID danych umieszczonych w cache
	 * @param mixed $value Dane do zapisu
	 * @param int $lifetime Czas po ktorym dane zapisane w pliku zostana przedawnione
	 */
	public function save($id, $data, $lifetime)
	{
		return eaccelerator_put($this->getPrefix() . '_' . $id, $value, $lifetime);		
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
		$data = ob_get_contents();
		ob_flush();

		$this->save($id, $data, $lifetime);
	}

	/**
	 * Usuniecie zmiennej znajdujacej sie w cache
	 * @param string $var Nazwa klucza
	 * @deprecated
	 */
	public function destroy($id = '')
	{
		return $this->remove($id);
	}

	/**
	 * Usuniecie zmiennej znajdujacej sie w cache
	 * @param string $id Nazwa klucza
	 */
	public function remove($id = '', $mode = Cache::ID)
	{
		if (!$id)
		{
			return eaccelerator_clear();
		}
		eaccelerator_rm($this->getPrefix() . '_' . $id);
	}

	/**
	 * Metoda sprawdza, czy zmienna o podanej nazwie znajduje sie w cache
	 * @param string $id Nazwa zmiennej
	 */
	public function exists($id)
	{
		$data = eaccelerator_list_keys();
		return isset($data[$this->getPrefix() . '_' . $id]);
	}
}

?>