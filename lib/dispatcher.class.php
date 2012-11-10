<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa nadpisuje domyslny dispatcher zadeklarowny we frameworku
 */
class Dispatcher
{
	public static function dispatch($controller, $action, $folder = '', array $args = array())
	{
		$router = &Load::loadClass('router');

		/*
		 * Jezeli system zostal uruchomiony z crona, nalezy sprawdzic wartosc
		 * parametru 'key', a nastepnie uruchomic dzialania crona
		 */
		if (defined('IN_CRON'))
		{
			if (@$_GET['key'] == Config::getItem('scheduler'))
			{
				Scheduler::load();

				exit;
			}
		}

		/*
		 * Jezeli system zostal chwilowo wylaczony, zapobiegamy dalszym czynnosciom.
		 * W takim przypadku nalezy wyswietlic ustalony szablon wraz z komunikatem
		 * informujacym, iz system jest chwilowo wylaczony
		 */
		if (Config::getItem('shutdown'))
		{
			if ('adm' != $router->getFolder())
			{
				echo new View('error/shutdown', array('message' => Config::getItem('shutdown')));
				exit;
			}
		}

		/**
		 * Ponizszy warunek zostanie spelniony jezeli system nie bedzie
		 * w stanie na podstawie konfiguracji zapisanej w router.xml, wskazac
		 * kontrolera, ani akcji, ktora powinna sie wykonac. W takim przypadku
		 * na podstawie sciezki - np - /Foo/Bar znajdujemy dana strone w bazie danych
		 * i ignorujemy domyslne wartosci przekazane w $controller, $action ...
		 */
		if (!$router->getName())
		{
			/*
			 * Nie uzywamy metody getPath() z klasy Input, poniewaz ta metoda
			 * filtruje dane
			 */
			$pathInfo = trim(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : getenv('PATH_INFO'), '/');
			if (empty($pathInfo))
			{
				$pathInfo = trim(isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : getenv('PATH_INFO'), '/');
			}

			$controller = $action = $folder = '';
			$input = &Load::loadClass('input');

			Page::setEnableRedirect(true); // wlacz szukanie jezeli strona zostala przeniesiona
			Page::setEnable404(true); // wlaczone szukanie strony 404 jezeli wlasciwa nie zostala odnaleziona
			Page::setOmmitDelete(true); // uznaj ze strona nie zostala odnaleziona jezeli jest odznaczona jako usuniecia
			Page::setOmmitUnpublished(true); // uznaj ze strona nie zostala odnaleziona jezeli jest odznaczona jako nieopublikowana

			if ($page = Page::load($pathInfo))
			{
				$controller = $page->getController();
				$action = $page->getAction();
				$folder = $page->getFolder();

				/*
				 * Jezeli nazwa lacznika to 'error', oznacza, ze mamy
				 * do czynienia ze strona bledu. Jest to blad 404, bo tylko
				 * taki na tym etapie jest mozliwy, wiec przypisujemy URL brakujacej strony
				 */
				if ($page->getConnectorName() == 'error')
				{
					$page->addVar('message', 'Podana strona nie istnieje, została usunięta lub nie jest opublikowana');
				}

				Core::getInstance()->page = &$page;
			}
		}
		else
		{
			$data = $router->getData();
			$pageId = isset($data['page']) ? $data['page'] : 0;

			if ($pageId)
			{
				Core::getInstance()->page = &Page::load((int) $pageId);
			}
		}

		/**
		 * Pobieramy konfiguracje modulow, tylko jezeli system jest zainstalowany
		 */
		if (Config::getItem('install') == 'true')
		{
			/**
			 * Zaladowanie informacji o modulach oraz o wtyczkach na danej stronie
			 */
			Load::loadClass('module')->getModules();
		}

		return self::forward($controller, $action, $folder, $args);
	}

	public static function forward($controller, $action, $folder, array $args = array())
	{
		// wywolanie triggerow
		Trigger::call('system.onBeforeController', array(&$controller, &$action, &$folder, &$args));
		$path = 'controller/' . ($folder ? $folder . '/' : '') . $controller . '.php';

		$input = &Load::loadClass('input');

		try
		{
			// nalezy sprawdzic, czy modul z klasa istnieje
			if (!Load::fileExists($path))
			{
				throw new FileNotFoundException($input->getPath());
			}
			Load::loadFile($path);
			$class = class_exists($controller . '_Controller', false) ? $controller . '_Controller' : $controller;

			Log::add("Controller $path loaded", E_DEBUG);

			// nalezy sprawdzic czy istnieje klasa kontrolera oraz zadana metoda
			if (!class_exists($class, false) ||
					(!in_array($action, get_class_methods($class))	&&
					 !in_array('__call', get_class_methods($class))))
			{
				throw new FileNotFoundException($input->getPath());
			}
		}
		catch (FileNotFoundException $e)
		{
			$e->message();
			exit;
		}

		// wywolanie konstrolera
		$controller = new $class;

		Trigger::call('system.onBeforeAction');

		if (method_exists($controller, '__start'))
		{
			Trigger::call('system.onBeforeStart');
			$controller->__start();
			Trigger::call('system.onAfterStart');
		}
		$result = call_user_func_array(array(&$controller, $action), $args);

		if ($result !== null && $result !== View::NONE)
		{
			if (is_object($result))
			{
				$view = &$result;
			}
			else
			{
				if (is_string($result))
				{
					$template = ($folder ? $folder . '/' : '') . (string)$controller . $result;
				}
				else if ($result === true)
				{
					$template = ($folder ? $folder . '/' : '') . (string)$controller . ($action != 'main' ? ucfirst($action) : '');
				}
				else
				{
					$template = (string)$controller . View::MAIN;
				}
				$view = new View($template);
			}
			$view->assign(get_object_vars($controller));

			echo $view->display();
		}
	}
}

?>