<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Loader
 */
class Load
{
	/**
	 * Tablica przechowuje wszystkie obiekty klas zaladowanych w aplikacji
	 */
	static public $object = array();

	/**
	 * Metoda autoload
	 * @param string $class_name Nazwa klasy
	 */
	public static function autoload($className)
	{
		// Metoda probuje rozpoznac, czy klasa jest modelem, czy biblioteka
		// Jezeli sufix nazwy klasy to model (np. Test_Model), proba zaladowania modelu
		$suffix = strtolower(substr(strrchr($className, '_'), 1));

		if ($suffix == 'model')
		{
			self::model(substr($className, 0, strrpos($className, '_')));
		}
		else
		{
			if ($suffix && strpos($className, '_') !== false)
			{
				$prefix = substr($className, 0, strrpos($className, '_'));
				$path = str_replace('_', '/', strtolower($prefix));

				if (self::dirExists("lib/$path"))
				{
					if (!self::fileExists("$path/$suffix"))
					{
						$suffix = lcfirst(substr(strrchr($className, '_'), 1));
					}

					return self::loadClass("$path/$suffix", false);
				}
			}

			if (self::fileExists('helper/' . strtolower($className) . '.helper.php'))
			{
				self::loadHelper($className);
			}
			else
			{
				// proba zaladowania biblioteki
				self::loadClass($className, false);
			}
		}
	}

	/**
	 * Laduje plik do projektu korzystajac z konstrukcji include_once
	 * @param string $path Sciezka do pliku
	 * @param bool $glob Jezeli wartosc posiada true, system wczyta wszystkie pliki odpowiadajace masce
	 * @example Load::loadFile('lib/*.php', true); 
	 * @return bool 
	 */
	public static function loadFile($path, $glob = false)
	{
		if ($glob)
		{
			foreach (glob($path) as $filename)
			{
				include_once($filename);
			}
			return true;
		}
		return include_once($path);
	}

	/**
	 * Ustawia sciezke do zmiennej include path
	 * @param string $path Sciezka (path)
	 * @return string Dotychczasowy ciag include path
	 */
	public static function setIncludePath($path)
	{
		return set_include_path(get_include_path() . PATH_SEPARATOR . $path);	
	}

	/**
	 * Sprawdza czy istnieje plik, katalog czy inne dane, korzystajac z include path
	 * @param string $filename Nazwa lub sciezka do pliku
	 * @param string $func Funkcja PHP ktora ma zostac zastosowana (np. file_exists(), is_dir())
	 * @return bool
	 */
	public static function locate($filename, $func = 'file_exists')
	{
		$paths = explode(PATH_SEPARATOR, get_include_path());
		$result = array();

		foreach ($paths as $path)
		{
			if ($path == '.')
			{
				continue;
			}
			if ($path[strlen($path) -1] != '/')
			{
				$path .= '/';
			}
			if ($func($path . $filename))
			{
				$result[] = $path . $filename;
			}
		}
		return $result;
	}

	/**
	 * Sprawdza czy plik istnieje, korzystajac przy tym z include path
	 * @param string $filename Nazwa (sciezka) do pliku
	 * @return bool
	 */
	public static function fileExists($filename)
	{		
		return (bool)self::locate($filename);
	}

	/**
	 * Sprawdza czy katalog istnieje, korzystajac przy tym z include path
	 * @param string $filename Nazwa (sciezka) do pliku
	 * @return bool
	 */
	public static function dirExists($dirname)
	{
		return (bool)self::locate($dirname, 'is_dir');		
	}

	/**
	 * Metoda statyczna realizuje ladowanie bibliotek frameworka
	 * @param string $class_name Nazwa biblioteki (klasy)
	 * @param bool $init Okresla, czy powinna byc tworzona instancja klasy (TRUE) czy tez nie (FALSE)
	 * @param string $params Parametry przekazywane do konstruktora klasy
	 * @return mixed Zwracana jest referencja do instancji klasy
	 */
	public static function &loadClass($class_name, $init = true, $params = '')
	{
		if (!isset(self::$object[$class_name]))
		{		
			$file_name = $class_name;

			// ladujemy klase, tylko raz
			if (!@self::loadFile("lib/{$file_name}.class.php"))
			{
				$file_name = strtolower($class_name);

				if (!self::loadFile("lib/{$file_name}.class.php"))
				{
					trigger_error("Class $class_name does not exists in path(s): " . get_include_path(), E_USER_ERROR);
				}
			}
			// dodanie odpowiedniej wartosci w tablicy zagwarantuje, ze ten kod nie zostanie
			// wykonany ponownie jezeli uzytkownik zazada zaladowanie kolejny raz tej samej klasy
			self::$object[$class_name] = true;

			if ($class_name == 'benchmark')
			{
				Benchmark::start('__SYSTEM__');
			}
			Log::add("Class $class_name loaded", E_DEBUG);
		}
		if ($init)
		{
			if (self::$object[$class_name] === true)
			{
				$object = str_replace('/', '_', $class_name);
				self::$object[$class_name] = new $object($params);	

				if (method_exists(self::$object[$class_name], 'initialize'))
				{
					$reflect = new ReflectionMethod($object, 'initialize');
					if ($parameters = $reflect->getParameters())
					{
						if ($parameters[0]->getClass())
						{
							if ($parameters[0]->getClass()->name == 'IContext')
							{
								self::$object[$class_name]->initialize(Context::getInstance());
							}
						}
					}
				}
			}
		}
		
		return self::$object[$class_name];
	}

	/**
	 * Metoda statyczna realizuje dolaczenie modulu do projektu
	 * @param string $module Nazwa modulu
	 * @param string $cfg_file Nazwa pliku konfiguracyjnego modulu
	 */
	public static function loadModule($module)
	{ 
		// sprawdzenie, czy modul istnieje
		if (!file_exists("module/$module"))
		{
			trigger_error("Unable to load module $module", E_USER_ERROR);
		}
		// dodanie sciezki do zmiennej include path
		self::setIncludePath(Config::getBasePath() . "module/$module");
		
		foreach (Core::getConfigPath() as $path)
		{
			if (file_exists("module/$module/$path"))
			{
				Config::load("module/$module/$path");
			}
		}
		Log::add("Module $module loaded", E_DEBUG);
	}
	
	/**
	 * Ladowanie helpera
	 * @param string $helper Nazwa helpera
	 * @example Load::loadHelper('example');
	 */
	public static function loadHelper($helper)
	{		
		$path = '';
		$helper = strtolower($helper);

		// nalezy sprawdzic, czy plik helpera nie znajduje sie w podkatalogu
		if (strpos($helper, '/') !== false)
		{
			list($path, $helper) = explode('/', $helper);
		}

		// ladujemy plik, tylko raz
		if (Load::loadFile("helper/{$path}{$helper}.helper.php") == true)
		{
			Log::add("Helper $helper loaded", E_DEBUG);

			return true;
		}		
		trigger_error("Helper $helper does not exists in path(s): " . get_include_path(), E_USER_ERROR);
	}

	/**
	 * Construct (auto load)
	 * @param mixed Tablica zawierajaca zasoby do automatycznego zaladowania
	 * @example array('library' => array('input', 'output'))
	 */
	public function __construct($data = array())
	{  
		if ($db = Config::getItem('databases'))
		{
			if (isset($db->autoload))
			{
				$port = false;

				extract($db->{$db->autoload});
				$this->database($adapter, $host, $user, $password, $dbname, $port, @$charset);
			}
		}

		if ($autoload = array_merge_recursive((array) $data, (array) Config::getItem('autoload')))
		{  
			foreach (array('module', 'library', 'model', 'helper', 'lang') as $type)
			{  
				if (isset($autoload[$type]))
				{  
					// nie ladujemy tych samych zasobow podwojnie
					$autoload[$type] = array_unique((array)$autoload[$type]);

					foreach ($autoload[$type] as $value)
					{   
						// sprawdzenie, czy na koncu znajduje sie znak /
						// jezeli tak, ladujemy do projektu caly folder
						if (strpos($value, '/') === false)
						{
							$this->$type($value);
						}
						else
						{
							$this->directory($value);
						}
					}
				}
			}			
		}		
	}

	/**
	 * Metoda laduje wszystkie biblioteki i helpery z podanej lokalizacji
	 * @param string $dir_name Sciezka (nazwa) katalogu
	 */
	private function directory($dir_name)
	{
		if (!$dir = opendir($dir_name))
		{
			trigger_error("Directory $dir_name does not exists", E_USER_ERROR);
		}

		while ($entry = readdir($dir))
		{
			if ($entry != '.' && $entry != '..')
			{
				if (preg_match('#([a-zA-Z0-9_-]*)\.(class|helper|model)\.php#', $entry, $m))
				{ 
					if ($m[2] == 'class')
					{
						$this->library($m[1]);
					}
					else if ($m2[2] == 'model')
					{
						$this->model($m[1]);
					}
					else
					{
						$this->helper($m[2]);
					}
				}
			}
			
		}
		closedir($dir); 
	}

	/**
	 * Ladowanie biblioteki frameworka
	 * @param string $lib_name Nazwa biblioteki
	 * @example $this->library('example');
	 */
	public function &library($lib_name, $params = '')
	{
		Core::getInstance()->$lib_name = &Load::loadClass($lib_name, true, $params);	

		// zwrocenie przez referencje instancji do klasy
		return Core::getInstance()->$lib_name;
	}

	/**
	 * Ladowanie pliku i18n
	 */
	public function lang($lang_file, $locale = null)
	{ 
		Core::getInstance()->lang->load($lang_file, $locale);
	}

	/**
	 * Ladowanie helpera
	 * @param string $helper_name Nazwa helpera
	 * @example $this->helper('example');
	 */
	public function helper($helper_name)
	{		
		return Load::loadHelper($helper_name);
	}

	/**
	 * Utworzenie instancji klasy db oraz inicjacja polaczenia
	 */
	public function database($adapter, $host, $user, $password, $dbname, $port = false, $charset = null)
	{
		// ladowanie biblioteki db
		$this->library('db', $adapter);

		$core = &Core::getInstance();
		// laczenie z baza danych na podstawie ustawien z pliku konfiguracyjnego
		$core->db->connect($host, $user, $password, $dbname, $port);
		Log::add("Database connected", E_DEBUG);

		if ($charset)
		{
			$core->db->setCharset($charset);
		}
	}

	/**
	 * Ladowanie klasy z modelem
	 * @param string $model_name Nazwa modelu
	 */
	public function &model($model_name)
	{
		// zamiana na male litery - ze wzgledu na wrazliwosc w systemach nix
		$model_name = strtolower($model_name);

		$path = '';
		// nalezy sprawdzic, czy plik modelu nie znajduje sie w podkatalogu
		if (strpos($model_name, '/') !== false)
		{
			$arr = explode('/', $model_name);
			$model_name = end($arr);
			array_pop($arr);

			$path = implode('/', $arr) . '/';
		}

		if (($initialize = Load::loadFile("model/{$path}$model_name.model.php")) === false)
		{
			trigger_error("Unable to load model $model_name", E_USER_ERROR);
		}
		if ($initialize !== true)
		{
			$class_name = $model_name . '_model';
			Core::getInstance()->model->$model_name = new $class_name;

			Log::add("Model $model_name loaded", E_DEBUG);
		}
		return Core::getInstance()->model->$model_name;
	}

	/**
	 * Metoda realizuje zaladowanie modulu do projektu. 
	 * Jej dzialanie jest identyczne jak dzialanie statycznej metody laodModule
	 * @see loadModule
	 */	 
	public function module($module)
	{
		self::loadModule($module);
	}

	/**
	 * Zaladowanie widoku
	 * @param string $view_name Nazwa widoku
	 * @param mixed $data Tablica z wartosciami, ktore przekazane zostana do widoku
	 * @return mixed Instancja klasy view
	 */
	public function view($view_name, $data = array(), $instance = null)
	{
		return new view($view_name, $data, $instance);
	}
}

?>