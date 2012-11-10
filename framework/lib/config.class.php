<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Wyjatek generowany w przypadku niemoznosci odnalezenia pliku konfiguracyjnego
 */
class ConfigFileNotFoundException extends Exception
{
	function __construct($message, $code = 0)
	{
		parent::__construct($message, $code);

		echo "<p><b>Config file not found</b></p>";
	}
}

/**
 * Interface dla adapterow konfiguracji
 */
interface IConfig 
{
	public function load($path);
	/**
	 * Do metody przekazywane jest rozszerzenie pliku konfiguracyjnego. Jezeli metoda zwroci true,
	 * to oznacza, iz akceptuje wybrane rozszerzenie i mozna przetwarzac dane. Jezeli nie - wyswietlany
	 * jest blad programu o braku danego kontrolera
	 */
	public function isAccept($suffix);
}

/**
 * Klasa konfiguracji projektu
 */
class Config
{
	/**
	 * Tablica zawierajaca konfiguracje projektu
	 */
	public static $config = array();
	/**
	 * Adapter dla pliku konfiguracji
	 */
	private static $adapter;
	/**
	 * Zawiera liste zaladowanych plikow konfiguracyjnych
	 * Wykorzystywane, aby uniknac podwojnego ladowania konfiguracji
	 */
	private static $include = array();
	/**
	 * Zwraca wartosc TRUE jezeli zawartosc konfiguracji jest cachowana
	 */
	private static $isCached = false;
	/**
	 * Lista cachowanych plikow wraz z data modyfikacji tych plikow
	 */
	private static $cacheFiles = array();

	/**
	 * Przypisuje instancje klasy adaptera
	 * @param object $adapter Instancja adaptera konfiguracji
	 */
	public static function setAdapter(IConfig &$adapter)
	{
		self::$adapter = &$adapter;
	}

	/**
	 * Na podstawie znaku . metoda rozdziela skladowe klucza tworzac 
	 * tablice asocjacyjne. Metoda wywolywana jest rekurencyjnie
	 * @param mixed $config Tablica konfiguracji
	 * @param string $key Klucz 
	 * @param mixed Wartosc klucza
	 * @return mixed
	 */
	private static function parseKey($config, $key, $value)
	{
		if (strpos($key, '.') !== false)
		{
			$part = explode('.', $key, 2);

			if (!isset($config[$part[0]]))
			{
				$config[$part[0]] = array();
			}
			if (isset($part[1]))
			{
				$config[$part[0]] = self::parseKey($config[$part[0]], $part[1], $value);
			}
		}
		else
		{
			$config[$key] = $value;
		}
		return $config;
	}

	/**
	 * Ustawia parametr konfiguracyjny. Jezeli dany klucz istnieje - wartosc zostanie zastapiona
	 * @param string $key Klucz 
	 * @param string $value Wartosc
	 * @example setItem('core.version', '1.0');
	 */
	public static function setItem($key, $value)
	{
		if (strpos($key, '.') !== false)
		{
			self::removeItem($key);
			self::$config = array_merge_recursive(self::$config, self::parseKey(array(), $key, $value));
		}
		else
		{
			self::$config[$key] = $value;
		}
	}

	/**
	 * Dodanie kolejnej warosci do istniejacego juz pola. Jezeli pole istnieje, zostanie zamienione
	 * na tablice, do ktorej zostanie dodany kolejny element
	 * @param string $key Nazwa pola konfiguracyjnego
	 * @param string $value Wartosc pola
	 */
	public static function addItem($key, $value)
	{
		self::$config = array_merge_recursive(self::$config, self::parseKey(array(), $key, $value));	
	}

	/**
	 * Usuwa element z tablicy konfiguracji
	 * @param string $key Klucz (np. core.template)
	 * @return bool TRUE w przypadku gdy usunieto element
	 */
	public static function removeItem($key)
	{
		if (strpos($key, '.') !== false)
		{
			$part = explode('.', $key);
			$config = &self::$config;
			$result = false;

			do
			{
				$element = array_shift($part);
				if (isset($config[$element]))
				{
					if (count($part))
					{
						$config = &$config[$element];
					}
					else
					{
						unset($config[$element]);
						$result = true;
						break;
					}
				}
				else
				{
					break;
				}
			}
			while (count($part));

			return $result;
		}
		else
		{
			unset(self::$config[$key]);
			return true;
		}
	}

	/**
	 * Metoda zwraca wartosc konfiguracji na podstawie klucza (parametr $key)
	 * @param string $key Klucz (nazwa parametru konfiguracji)
	 * @param string $default Wartosc domyslna, w przypadku gdy Klucz nie istnieje (opcjonalnie)
	 * @return string Wartosc konfiguracji
	 */
	public static function getItem($key = '', $default = null)
	{
		if (!$key)
		{
			return self::$config;
		}

		if (strpos($key, '.') !== false)
		{	
			$config = &self::$config;
			$part = explode('.', $key);

			do
			{
				$element = array_shift($part);
				if (empty($config[$element]))
				{
					return $default;
				}
				$config = &$config[$element];
			}
			while (count($part));

			return (is_array($config) ? (object)$config : $config);
		}
		else
		{
			if (empty(self::$config[$key]))
			{
				return $default;
			}

			return is_array(self::$config[$key]) ? (object)self::$config[$key] : self::$config[$key];
		}
	}

	/**
	 * Ustawia sciezke do folderu frameworka
	 * @param string $root_path Sciezka do frameworka
	 */
	public static function setRootPath($root_path)
	{
		self::setDefault('core.root', $root_path);
	}
	
	/**
	 * Zwraca sciezke do frameworka
	 * @return string
	 */
	public static function getRootPath()
	{
		return self::getItem('core.root');
	}

	/**
	 * Ustawia sciezke do aplikacji 
	 * @param string $base_path Sciezka do aplikacji
	 */
	public static function setBasePath($base_path)
	{
		self::setDefault('core.base', $base_path);
	}

	/**
	 * Zwraca sciezke do aplikacji
	 * @return string
	 */
	public static function getBasePath()
	{
		return self::getItem('core.base');
	}

	/**
	 * Ustawia domyslna wartosc dla klucza, jezeli ten nie zostal uprzednio zadeklarowany
	 * @param string $key Klucz 
	 * @param string $default Domyslna wartosc
	 */
	public static function setDefault($key, $default)
	{
		if (!self::getItem($key))
		{
			self::setItem($key, $default);
		}
	}

	/**
	 * Metoda zwraca TRUE jezeli configuracja jest ladowana w cache
	 * @return bool
	 */
	public static function isCached()
	{
		return self::$isCached;
	}

	/**
	 * Inicjalizacja klasy. Poniewaz klasa zawiera elementy statyczne, nie uzywamy
	 * konstruktora. Ta metoda pelni role konstruktora. Sprawdza, czy istnieje
	 * cachowana (skompilowana) wersja konfiguracji
	 */
	public static function initialize()
	{
		register_shutdown_function(array('Config', 'shutdown'));

		// sprawdzenie, czy istnieje cache konfiguracji
		if (file_exists(self::getBasePath() . 'cache/__config.php'))
		{			
			include_once(self::getBasePath() . 'cache/__config.php');

			self::$isCached = true;
			self::$include = array_keys(self::$cacheFiles);

			foreach (self::$cacheFiles as $filename => $filemtime)
			{
				if (filemtime($filename) > $filemtime)
				{ 
					self::$config = array();
					self::$include = array();
					self::$isCached = false;

					break;
				}
			}
		}
	}

	/**
	 * Metoda ladujaca pliki konfiguracji
	 * @param string $filename Nazwa pliku konfiguracji (moze byc wartoscia pusta)
	 * @param string $namespace Opcjonalna przestrzen nazw dla grupy konfiguracji
	 */
	public static function load($filename = '', $namespace = '')
	{ 
		// jezeli dany plik zostal juz wczytany do projektu - pomijamy
		if (in_array($filename, self::$include))
		{
			return;
		} 
		self::$isCached = false;
		$pathinfo = pathinfo($filename, PATHINFO_EXTENSION);

		switch ($pathinfo)
		{
			case 'xml':
				$adapter = 'Config_XML';
			break;

			case 'php':
			case 'cfg':
				$adapter = 'Config_PHP';
			break;

			case 'ini':
				$adapter = 'Config_INI';
			break;

			default:
				$adapter = ''; 
		}
		if ($adapter)
		{
			if (!self::$adapter instanceof $adapter)
			{
				self::$adapter = new $adapter;
			}
		}
		else
		{
			if (self::$adapter == null || !self::$adapter->isAccept($pathinfo))
			{
				trigger_error('Configuration adapter is not set. Extension ' . $pathinfo . ' is not recognizable', E_USER_ERROR);
			}
		} 
		if (file_exists(self::getBasePath() . $filename))
		{
			self::$include[] = $filename;
			self::init(self::getBasePath() . $filename, $namespace);				
		}
		foreach (array_diff((array)self::getItem('include'), self::$include) as $include)
		{  
			if ($include)
			{
				self::load($include);
			}
		} 
	}

	/**
	 * Laczy pobrane tablice konfiguracji w jedna calosc
	 * @param string $filename - Sciezka do pliku konfiguracyjnego
	 * @param string $namespace Opcjonalnie, przestrzen nazw, w ktorej zostanie zapisana tablica
	 */
	private static function init($filename, $namespace = '')
	{
		if ($namespace)
		{
			$config[$namespace] = self::$adapter->load($filename);				
		}			
		else
		{
			$config = self::$adapter->load($filename);
		}
		Log::add("Loaded config: $filename", E_DEBUG);

		self::$config = array_merge_recursive(self::$config, $config);
	}	

	/**
	 * Metoda zapisuje konfiguracje do pliku PHP
	 */
	public static function compile()
	{
		$filemtime = array();
		foreach (self::$include as $filename)
		{
			// okreslenie czasy modyfikacji plikow
			$filemtime[$filename] = filemtime(self::getBasePath() . $filename);
		}

		$content = sprintf('<?php self::$cacheFiles = %s; self::$config = %s; ?>', var_export($filemtime, true), var_export(self::$config, true));
		file_put_contents(self::getBasePath() . 'cache/__config.php', $content, LOCK_EX);
	}

	/**
	 * Metoda wykonywana tuz przed zakonczeniem wykonywania aplikacji
	 */
	public static function shutdown()
	{
		if (Config::getItem('core.cacheCompile'))
		{
			if (!self::$isCached)
			{
				self::compile();
			}
		}
	}

	public static function __callStatic($name, $arguments)
	{ 
		if (isset(self::$config[$name]))
		{
			return self::$config[$name][$arguments];
		}
	}
}

?>