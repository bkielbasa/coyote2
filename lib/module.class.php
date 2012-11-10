<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa oblugi modulow
 */
class Module
{
	/**
	 * Tablica zawierajaca informacje o wlaczonych modulach
	 */
	private $module = array();
	/** 
	 * Konfiruacja modulow
	 */
	private $config = array();
	/**
	 * Tablica zawierajaca liste pluginow wlaczonych w obecnym module
	 */
	private $plugin = array();

	/**
	 * Pobiera informacje o wlaczonych modulach, zarowno z pliku XML jak i bazy danych
	 * @return mixed Tablica obiektow z informacjami o modulach
	 */
	public function getModules()
	{
		if (!$this->module)
		{ 
			$module = new Module_Model;

			foreach ($module->fetch() as $row)
			{  
				$this->module[$row['module_name']] = $row;
			}
			$router = &Load::loadClass('router');

			if ($router->getFolder() == 'adm')
			{
				$plugin = new Plugin_Model;
				$this->plugin = $plugin->getPlugins();
			}
			else
			{
				$this->plugin = $this->getPlugins($this->getCurrentModule());
			}

			foreach (array_keys($this->plugin) as $pluginName)
			{
				// sprawdzenie, czy modul istnieje
				if (!file_exists("plugin/$pluginName"))
				{
					Log::add("Unable to load plugin $pluginName", E_ERROR);
				}
				else
				{
					// dodanie sciezki do zmiennej include path
					Load::setIncludePath(Config::getBasePath() . 'plugin/' . $pluginName);
					
					foreach (Core::getConfigPath() as $path)
					{
						if (file_exists("plugin/$pluginName/$path"))
						{
							Config::load("plugin/$pluginName/$path");
							Trigger::load(); // ponowne zaladowanie konfiguracji triggerow
						}
					}
					Log::add("Plugin $pluginName loaded", E_DEBUG);	
				}
			}
		}

		return $this->module;
	}	

	/**
	 * Zwraca TRUE jezeli modul o danej nazwie jest obecny w systemie
	 * @param string $name Nazwa modulu
	 * @return bool
	 */
	public function isEnabled($name)
	{
		return isset($this->module[$name]);
	}

	/**
	 * Zwraca informacje na temat modulu na podstawie nazwy
	 * @param string $name Nazwa modulu (np. comment, pastebin)
	 * @return mixed Obiekt zawierajacy informacje o module lub FALSE jezeli brak danego modulu
	 */
	public function getModule($name)
	{
		return $this->isEnabled($name) ? $this->module[$name] : false;
	}

	/**
	 * Zwraca nazwe aktualnego modulu (w ktorym znajduje sie aktualnie wykonywany kontroler)
	 * Domyslnie zwraca 'main', co oznacza glowny modul (systemowy)
	 * @return string Nazwa modulu (np. comment, pastebin)
	 */
	public function getCurrentModule()
	{
		$result = 'main';
		$core = &Core::getInstance();

		if (isset($core->page) && $core->page !== false)
		{
			$result = $this->getName($core->page->getModuleId());
		}
		else
		{
			// lokalizujemy katalog, w ktorym uruchomiony jest kontroler
			$locate = Load::locate(Core::getInstance()->router->getPath());

			$path = str_replace(Core::getInstance()->router->getPath(), '', @$locate[0]);
			if (preg_match('#module/([a-zA-Z0-9_-]+)#i', $path, $m))
			{
				$result = $m[1];
			}
		}

		return $result;
	}

	/**
	 * Zwraca ID modulu na podstawie nazwy folderu (ID slowne - np. "comment", "pastebin")
	 * @param string $name 
	 * @return int
	 */
	public function getId($name)
	{
		return isset($this->module[$name]['module_id']) ? $this->module[$name]['module_id'] : null;
	}

	/**
	 * Zwraca nazwe modulu na podstawie jego ID
	 * @param int $id ID modulu
	 * @return string
	 */
	public function getName($id)
	{
		$result = $this->getById($id);
		return $result['module_name'];
	}

	/**
	 * Zwraca informacje o module na podstawie jego ID z bazy danych
	 * @param int $module_id ID modulu
	 */
	public function getById($id)
	{
		foreach ($this->module as $name => $data)
		{
			if ($data['module_id'] == $id)
			{
				return $this->module[$name];
			}
		}
	}

	private function _getModuleConfig($moduleId, $pageId = 0)
	{
		$result = array();

		$module = &Core::getInstance()->load->model('module');
		$query = $module->config->getModuleConfig($moduleId, $pageId);		

		while ($row = $query->fetchAssoc())
		{
			$result[$row['field_name']] = $row['config_value'] !== null ? $row['config_value'] : $row['field_default'];
		}
		
		return $result;
	}

	public function getModuleConfig($moduleId, $pageId = 0)
	{
		if (!isset($this->config[$moduleId][$pageId]))
		{
			$result = $this->_getModuleConfig($moduleId, $pageId);
			if (!$result && $pageId != 0)
			{
				$result = $this->getModuleConfig($moduleId, 0);
			}

			$this->config[$moduleId][$pageId] = $result;			
		}

		return $this->config[$moduleId][$pageId];
	}

	public function getConfig($moduleName, $fieldName, $pageId = 0)
	{
		$config = $this->getModuleConfig($this->getId($moduleName), $pageId);
		return isset($config[$fieldName]) ? $config[$fieldName] : null;
	}

	/**
	 * Mozliwosc odczytu konfiguracji danego modulu
	 * Np. $this->module->wiki('foo');
	 */
	public function __call($name, $key)
	{ 
		return $this->getConfig($name, $key[0], (int)@$key[1]);
	}

	/**
	 * Odczytuje pluginy wlaczone w danym module
	 * @param string $name ID modulu w formie slownej (czyli np. comment, pastebin - nazwa folderu)
	 */
	public function getPlugins($name)
	{
		$core = &Core::getInstance();
		$result = array();

		$moduleId = &$this->getId($name);
		if ($moduleId)
		{
			$result = $core->model->module->plugin->getPlugins($moduleId); 			
		}

		return $result;
	}

	/**
	 * Zwraca TRUE lub FALSE w zaleznosci, czy wtyczka jest wlaczona w aktualnie dzialajacym
	 * module
	 * @param string $name	Nazwa wtyczki - np. comment, poll
	 * @return bool
	 */
	public function isPluginEnabled($name)
	{
		return isset($this->plugin[$name]);
	}
	
	/**
	 * Zwraca ID pluginu przylaczonego, do aktualnie dzialajacego modulu
	 * @param string $name	Nazwa wtyczki - np. comment, poll
	 * @return int|null 	NULL w przypadku, gdy wtyczka o danej nazwie nie jest wlaczona w module
	 */
	public function getPluginId($name)
	{
		return isset($this->plugin[$name]) ? $this->plugin[$name]['plugin_id'] : null;
	}

	/**
	 * Zwraca wartosc typu bool oznaczajaca, czy dany plugin jest wlaczony w danym module
	 * @param string $name Nazwa modulu
	 * @param string $plugin Nazwa pluginu (slownie)
	 * @return bool
	 */
	public function getPlugin($name, $plugin)
	{
		return in_array($plugin, array_keys($this->getPlugins($name)));
	}
}
?>