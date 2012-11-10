<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

interface Cache_Interface
{	
	/**
	 * Ustawia prefix dla cache (wowczas kilka projektow moze dzielic ten sam katalog cache)
	 * @param string $prefix
	 */	
	public function setPrefix($prefix);
	/**
	 * Zwraca prefix
	 * @return string
	 */
	public function getPrefix();
	/**
	 * Wywolanie tej metody skutkuje rozpoczeciem procesu cachowania
	 * @param $id	Identyfikator zasobu
	 */
	public function start($id);
	/**
	 * Wywolanie metody konczy proces cachowania i powoduje zapis rezulatu do pliku
	 * @param string $id Unikalne ID zasobu
	 * @param int $lifetime Czas (w sekundach) po ktorym zasob zostanie przedawniony
	 */
	public function end($id, $lifetime = 0);
	/**
	 * Pobranie danego zasobu z cache
	 * @param $id	Nazwa (identyfikator)
	 */
	public function load($id);
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
	public function remove($id = '', $mode = Cache::ID);
	/**
	 * Zapisanie danych do cache
	 * @param string $id	Identyfikator zasobu
	 * @param mixed $data	Dane do skladowania
	 * @param int $lifetime	Czas waznosci cache (w sekundach)
	 */
	public function save($id, $data, $lifetime = 0);
	/**
	 * Metoda sprawdza, czy zmienna o podanej nazwie znajduje sie w cache
	 * @param string $id Nazwa identyfikatora
	 */
	public function exists($id);
}
?>