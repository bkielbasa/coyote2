<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Stala okreslajaca start systemu (zaladowanie biblioteki core)
 */
define('COYOTE_START', microtime());

/**
 * Podstawowa klasa frameworka
 *
 * Wszystkie pozostale klasy kontrolera lub modeli, dziedziczy po tej klasie
 * Tworzy ona instancje klasy widoku oraz loadera
 */
final class Core
{
	/**
	 * Stala okresla sciezke do frameworka
	 */
	const ROOT_PATH		=		2;
	/**
	 * Stala okresla sciezke do katalogu z aplikacja
	 */
	const BASE_PATH		=		4;
	/**
	 * Stala okresla sciezke do loadera
	 */
	const LOADER_PATH	=		8;
	/**
	 * Stala okresla sciezke do pliku konfiguracyjnego
	 */
	const CONFIG_PATH	=		16;

	/**
	 * Tablica waznych sciezek projektu
	 */
	static private $path = array(
			self::ROOT_PATH		=> '',
			self::BASE_PATH		=> '',
			self::LOADER_PATH	=> 'lib/load.class.php',
			self::CONFIG_PATH	=> array()
	);

	/**
	 * Prywatne pole przechowuje instancje klasy
	 */
	private static $instance;

	/**
	 * Ustawia sciezke do glownego katalogu z aplikacja (NIE frameworkiem)
	 * @param string $base_dir Sciezka do katalogu z aplikacja
	 * @static
	 */
	public static function setBasePath($base_dir)
	{
		if ($base_dir[strlen($base_dir) -1] != '/')
		{
			$base_dir .= '/';
		}
		self::$path[self::BASE_PATH] = $base_dir;
	}

	/**
	 * Zwraca sciezke do katalogu z aplikacja
	 * @return string
	 */
	public static function getBasePath()
	{
		return self::$path[self::BASE_PATH];
	}

	/**
	 * Metoda realizuje ustawienie sciezki do klasy loadera
	 * @param string $filename Nazwa (sciezka) klasy loadera
	 */
	public static function setLoaderPath($filename)
	{
		self::$path[self::LOADER_PATH] = $filename;
	}

	/**
	 * Zwraca sciezke do klasy loadera
	 * @return string
	 */
	public static function getLoaderPath()
	{
		return self::$path[self::LOADER_PATH];
	}

	/**
	 * Metoda umozliwia ustawienie sciezki do plikow frameworka
	 * @param string $root_dir
	 * @static
	 */
	public static function setRootPath($root_dir = '')
	{
		if (!$root_dir)
		{
			$root_dir = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/';
		}
		self::$path[self::ROOT_PATH] = $root_dir;
	}

	/**
	 * Zwraca sciezke do kataolgu z frameworkiem
	 */
	public static function getRootPath()
	{
		return self::$path[self::ROOT_PATH];
	}

	/**
	 * Ustawia sciezke do pliku konfiguracji projektu
	 * @param string $config_path
	 */
	public static function setConfigPath($config_path)
	{
		self::$path[self::CONFIG_PATH][] = $config_path;
	}

	/**
	 * Zwraca sciezke do pliku konfiguracji projektu
	 * @return array
	 */
	public static function getConfigPath()
	{
		return self::$path[self::CONFIG_PATH];
	}

	/**
	 * Metoda realizuje dodanie sciezki do include_path. Poniewaz biblioteki i helpery moga byc
	 * ladowane z roznych folderow
	 */
	public static function setIncludePath($path)
	{
		set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	}

	/**
	 * Glowna metoda frameworka. Realizuje inicjalizacje podstawowych klas i sciezek do projektu
	 * @param string $root_dir Opcjonalnie, sciezke do katalogu z frameworkiem
	 */
	public static function bootstrap($root_dir = '')
	{
		if ($root_dir)
		{
			self::setRootPath($root_dir);
		}
		if (!self::$path[self::ROOT_PATH])
		{
			self::setRootPath();
		}
		self::setIncludePath(self::$path[self::ROOT_PATH]);

		if (!self::$path[self::BASE_PATH])
		{
			self::setBasePath(self::getRootPath());
		}
		else
		{
			self::setIncludePath(self::$path[self::BASE_PATH]);
		}

		if (@(include_once(self::$path[self::LOADER_PATH])) === false)
		{
			trigger_error('Loader does not exists: ' . self::$path[self::LOADER_PATH], E_USER_ERROR);
		}
		if (!spl_autoload_register(array('Load', 'autoload')))
		{
			trigger_error('Method autoLoad() does not exists', E_USER_ERROR);
		}
		Log::add('System initialized', E_DEBUG);

		// ladowanie klasy Benchmark tylko jezeli nie zostala jeszcze zaladowana w klasie Log
		if (!class_exists('Benchmark', false))
		{
			Load::loadClass('benchmark', false);
		}
		Config::initialize();
		Config::setRootPath(self::getRootPath());
		Config::setBasePath(self::getBasePath());

		foreach (self::getConfigPath() as $path)
		{
			// zaladowanie pliku konfiguracyjnego
			Config::load($path);
		}
		// ustawienie katalogu z plikami szablonow (jezeli nie zostal okreslony w pliku konfiguracji)
		Config::setDefault('core.template', 'template');
		// ustawienie katalogu z plikami JavaScript (jezeli nie zostal okreslony w pliku konfiguracji)
		Config::setDefault('core.js', 'template/js');
		// ustawienie domyslnego pliku konfiguracji szablonow
		Config::setDefault('core.templateConfig', 'config.xml');
		// ustawienie domyslnego rozszerzenia plikow szablonow
		Config::setDefault('core.templateSuffix', '.php');
		// ustawienie domyslnego katalogu z plikami javascript
		Config::setDefault('core.javascript', 'template/js');

		Trigger::call('system.onReady');
		Load::loadClass('TriggerException');

		// jezeli w konfiguracji znajduja sie informacje o wlaczanych modulach, nalezy
		// zaladowac je do projektu
		if ($modules = (array) Config::getItem('module'))
		{
			foreach ($modules as $module)
			{
				Load::loadModule($module);
			}
		}

		// inicjalizacja klasy
		return Core::getInstance();
	}

	/**
	 * Metoda realizuje wywolanie odpowiedniego kontrolera na podstawie sciezki
	 */
	public function dispatch()
	{
		// wywolanie ew. hookow
		Trigger::call('system.onBeforeSystem');

		// inicjalizacja klasy loadera oraz zaladowanie podstawowych klas
		$this->load = new Load(array('library' => array('context', 'input', 'output')));
		// parser zadania (analizuje parametry i na podstawie uruchamia kontroler
		$router = &Load::loadClass('router');

		// wywolanie odpowiedniego kontrolera
		Dispatcher::dispatch($router->getController(), $router->getAction(), $router->getFolder(), $router->getArguments());

		// obsluga koncowych hookow
		Trigger::call('system.onShutdown');
	}

	/**
	 * Obsluga metody __clone()
	 */
	public function __clone()
	{
		trigger_error('Cloning Core object is not allowed', E_USER_ERROR);
	}

	/**
	 * Prezentuje w czytelnej formie zawartosc danych przekazanych do metody
	 */
	public static function debug()
	{
		if (func_num_args() === 0)
		{
			return;
		}
		$output = array();

		foreach (func_get_args() as $var)
		{
			$output[] = '<pre>' . htmlspecialchars(print_r($var, true)) . '</pre>';
		}
		echo implode("\n", $output);
	}

	/**
	 * Metoda zwraca wersje frameworka
	 * @return string
	 */
	public static function version()
	{
		return @file_get_contents(Config::getRootPath() . 'VERSION');
	}

	/**
	 * Metoda zwraca wersje frameworka
	 * @return string
	 */
	public static function getVersion()
	{
		return self::version();
	}

	/**
	 * Metoda zwraca instancje klasy
	 * @return mixed
	 */
	public static function &getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new Core;
		}
		return self::$instance;
	}
}

?>