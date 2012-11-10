<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa wyswietalaca zawartosc regionow
 */
class Region
{
	/**
	 * Stala okreslajaca, iz region nie bedzie cachowany w ogole
	 */
	const NO_CACHE = 0; 
	/**
	 * Stala okreslajaca, ze region bedzie cachowany tylko dla osob niezalogowanych
	 */
	const ANONYMOUS_CACHE = 1;
	/**
	 * Stala okreslajaca, iz region bedzie cachowany dla kazdego
	 */
	const ALL_CACHE = 2;
	/**
	 * Tablica zawierajaca nazwy regionow oraz informacje z nim zwiazane
	 */
	private static $region = array();

	/**
	 * Pobiera z konfiguracji nazwy regionow oraz informacje o nich
	 * @return mixed
	 */
	public static function getRegions()
	{
		$regions = (array)Config::getItem('region');
		foreach ($regions as $region)
		{ 
			self::$region[(string)$region['name']] = (array)$region;
		}

		return self::$region;
	}

	/**
	 * Zwraca informacje o konkretnym regionie
	 * @param string $name Nazwa regionu
	 * @return mixed
	 */
	public static function getRegion($name)
	{
		$region = self::getRegions();
		if (!isset($region[$name]))
		{
			return false;
		}
		return $region[$name];
	}

	/**
	 * Prywatna metoda wyswietlajaca zawartosc regionu o danej nazwie
	 * @param string $name Nazwa regionu
	 */
	private static function displayRegion($name)
	{
		$region = Block::getRegionBlocks($name);
		if (!$region)
		{
			return false;
		}

		foreach ($region as $row)
		{
			// wywolanie triggera i przekazanie do zdarzenia nazwy regionu oraz bloku
			UserErrorException::__(Trigger::call('application.onRegionDisplay', $name, $row['block_name']));
			// wyswietlenie bloku o danej nazwie
			Block::display($row['block_name']);			
		}
	}

	/**
	 * Wyswietla zawartosc regionu o danej nazwie. 
	 * @param string $name Nazwa regionu
	 * @param int $cache Parametr okreslajacy, czy region ma byc cachowany
	 * @example display('foo', Region::ANONYMOUS_CACHE); 
	 */
	public static function display($name, $cache = null)
	{			
		$is_cache = false;
		// jezeli wartosc parametru jest pusta, odczytujemy informacje o cachowaniu
		// z konfiguracji regionu
		if ($cache === null)
		{
			$config = self::getRegion($name);
			$cache = $config['cache'];
		}

		switch ($cache)
		{
			case self::ALL_CACHE:
				$is_cache = true;
			break;

			case self::ANONYMOUS_CACHE:
				$is_cache = User::$id == User::ANONYMOUS;
			break;

			case self::NO_CACHE:
			default:
				$is_cache = false;
			break;
		}
		if ($is_cache)
		{
			if (!Core::getInstance()->cache->start($name))
			{
				self::displayRegion($name);
				Core::getInstance()->cache->end($name);
			}
		}
		else
		{
			self::displayRegion($name);
		}
	}
}
?>