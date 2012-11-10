<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa sluzaca do operowania na plikach konfiguracyjnych widoku
 */
class View_Config
{
	/**
	 * Tablica danych n/t konfiguracji widokow
	 */
	private static $data = array();


	/**
	 * Metoda przeszukuje wszystkie mozliwe lokalizacje, w ktorych
	 * mozna znalezc plik konfiguracji widoku
	 * @static
	 * @param string $path Sciezka do katalogu z widokiem
	 * @return mixed $data Dane n/t konfiguracji widokow
	 */
	private static function load($path)
	{
		$basename = basename($path);
		$path_arr[] = $path;
		
		$basePath = str_replace('\\', '/', Config::getBasePath());
		foreach (explode(PATH_SEPARATOR, str_replace('\\', '/', str_replace(Config::getRootPath(), '', get_include_path()))) as $includePath)
		{
			if ($includePath = trim(str_replace($basePath, '', $includePath), './'))
			{
				$path_arr[] = $includePath . '/' . $path;
			}
		}

		foreach ($path_arr as $path)
		{
			foreach ((array)Config::getItem('core.templateConfig') as $file)
			{
				if (file_exists($path . $file))
				{
					Config::load($path . $file, $basename);
				}
			}		
		}
		return Config::getItem($basename);
	}

	/**
	 * Publiczna metoda obslugujaca klase - zwraca finalna zawartosc tablicy konfiguracji
	 * @param string $path Sciezka do katalogu z kontrolerem
	 * @return mixed
	 */
	public static function &get($path)
	{
		if (!isset(self::$data[$path]))
		{
			self::$data[$path] = self::load($path);
		} 
		return self::$data[$path];
	}
}

?>