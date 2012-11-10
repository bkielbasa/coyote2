<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa obslugi routingu
 */
class Router
{
	/**
	 * Tablica regul routingu
	 */
	protected $routes = array();
	/**
	 * Jezeli jakas regula zostanie dopasowana do sciezki, ponizsza tablica bedzie zawierac informacje o regule
	 */
	protected $data = array();
	/**
	 * Fragment adresu URL (sciezka PATH_INFO)
	 */
	private $path;
	/**
	 * Elementy adresu URL (z pola $path)
	 */
	private $parts = array();
	/**
	 * Nazwa kontrolera
	 */
	private $controller = 'index';
	/**
	 * Nazwa akcji 
	 */
	private $action = 'main';
	/**
	 * Nazwa podkatalogu, w ktorym umieszczony jest kontroler
	 */
	private $folder;
	/**
	 * Nazwa reguly, ktora zosala dopasowana do tej strony
	 */
	public $name;
	/**
	 * Argumenty ktore zostaly przekazane w URLu
	 */
	private $args = array();
	/**
	 * Domyslny znak separatora w sciezce adresu
	 */
	protected $urlDelimiter = '/';
	/**
	 * Domyslny znak zmiennej w regulach routingu
	 */
	protected $urlVariable = ':';

	/** 
	 * Analizowanie regul routera
	 * @param array $routes Tablica z regulami routingu
	 * @param bool $sort|int Parametr okresla, czy tablica ma byc sortowana (wedlug elementu 'order' tablicy)
	 * @example $router = new Router($foo, SORT_ASC);
	 */
	function __construct($routes = array(), $sort = false)
	{
		$input = &Load::loadClass('input');
		$this->setPath($input->getPath());		

		if ($routes)
		{
			$this->setRoutes($routes);
		}
		$this->setRoutes((array) Config::getItem('route'));

		$this->setSort(Config::getItem('core.sortRoutes', $sort));
		/* proba dopasowania obecnego adresu URL z regulami routera */
		$this->match('', Config::getItem('core.setDefaultRoute', true) == 'true');
	}

	/**
	 * Ustawienie sciezki obecnego adresu (np. Foo/Bar)
	 * @param string $path
	 */
	public function setPath($path)
	{
		$this->path = trim($path, $this->urlDelimiter);

		// rozdzielenie sciezki na poszczegolne segmenty
		$this->parts = (array)explode($this->urlDelimiter, $this->path);
	}

	/**
	 * Ustawienie sortowania
	 * @param int|bool $sort FALSE w przypadku, gdy klasa ma nie sortowac regul
	 * @example $this->setSort(SORT_ASC);
	 */
	public function setSort($sort)
	{
		$this->sort = $sort;
	}

	/**
	 * Zwraca obecne ustawienie sortowania
	 * @return bool|int
	 */
	public function getSort()
	{
		return $this->sort;
	}

	/**
	 * Realizuje dodanie nowej reguly routingu
	 * @param string $name Nazwa reguly
	 * @param array $data Informacje o regule (tablica danych). Wiecej w dokumentacji
	 */
	public function addRoute($name, array $data)
	{
		if (!isset($data['name']))
		{
			$data['name'] = $name;
		}
		$this->routes[$name] = $data;
	}

	/**
	 * Ustawienie regul routingu
	 * @param array $routes
	 */
	public function setRoutes($routes)
	{
		if (empty($routes))
		{
			return false;
		}

		if (!is_array($routes[key($routes)]))
		{
			$routes = array($routes);
		}

		/**
		 * Dodatkowa petla tworzy tablice regul. Kluczem elementow
		 * jest nazwa reguly
		 */
		foreach ($routes as $index => $array)
		{
			$routes[$array['name']] = $array;
			unset($routes[$index]);
		}
		$this->routes = $routes;
	}

	/**
	 * Zwraca tablice routingu
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Zwraca tablica z informacjami o danej regule
	 * @return array
	 */
	public function getRoute($name)
	{
		if (isset($this->routes[$name]))
		{
			return $this->routes[$name];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Dopasowuje sciezke adresu URL do danej reguly routingu
	 * @param string $path Sciezka adresu URL (parametr opcjonalny. Sciezka mogla zostac przekazana w konstruktorze)
	 * @param bool $setDefault Jezeli regula nie zostanie odnaleziona w tablicy regul, zostana przypisany domyslny kontroler oraz akcja
	 * @return bool TRUE jezeli kontroler oraz akcja zostaly przypisane
	 */
	public function match($path = '', $setDefault = false)
	{
		if ($path)
		{
			$this->setPath($path);
		}
		$matched = false;	

		if ($this->getSort() !== false)
		{
			$sort = array();

			foreach ($this->routes as $key => $array)
			{
				$sort[$key] = (int)@$array['order'];
			}
			array_multisort($sort, constant($this->getSort()), $this->routes); 
			unset($sort);
		}

		/**
		 * Petla analizujaca kazda regule w tablicy routingu
		 */
		foreach ($this->routes as $data)
		{
			$url = trim($data['url'], $this->urlDelimiter);
			/**
			 * Jezeli w regule routingu przekazano informacje o rozszerzeniu, usuwamy
			 * je, poniewaz nie jest potrzebne w procesie sprawdzania tej reguly
			 */
			if (isset($data['suffix']))
			{
				$url = preg_replace('#\.' . $data['suffix'] . '$#i', '', $url);
			}

			$variables = array();
			$parts = array();
			$static = 0;

			foreach (explode($this->urlDelimiter, $url) as $pos => $urlPart)
			{
				if (substr($urlPart, 0, 1) == $this->urlVariable)
				{
					$varName = substr($urlPart, 1);
					$variables[$urlPart] = '';

					if (isset($data['default'][$varName]))
					{
						$variables[$urlPart] = $data['default'][$varName];
					}
				}
				elseif ($urlPart != '*')
				{
					++$static;
				}

				$parts[$pos] = $urlPart;
			}
			$partCount = sizeof($parts); 
			$matchCount = 0;
			$staticCount = 0;

			if (isset($data['suffix']))
			{
				$isSuffix = &$this->parts[sizeof($this->parts) -1];
				if (strpos($isSuffix, '.' . $data['suffix']) !== false)
				{
					$isSuffix = preg_replace('#\.' . $data['suffix'] . '$#i', '', $isSuffix);
				}
			}

			/**
			 * Petla analizuje kazda czesc sciezki (NIE sciezki z routingu)
			 */
			foreach ($this->parts as $pos => $part)
			{
				if (!array_key_exists($pos, $parts))
				{
					continue 2;
				}

				if ($parts[$pos] == '*')
				{
					$this->args = array_slice($this->parts, $pos);
					break;
				}

				if (strcasecmp($part, $parts[$pos]) !== 0)
				{
					$varName = $parts[$pos];

					if (!isset($variables[$varName]))
					{
						continue 2;
					}
					else
					{
						$name = substr($varName, 1);

						if (isset($data['requirements'][$name]))
						{
							if (!preg_match('#^' . $data['requirements'][$name] . '$#i', $part))
							{
								continue 2;
							}
						}
						$variables[$varName] = $part;
					}
				}
				else
				{
					++$staticCount;
				}

				++$matchCount;
			}

			if (!empty($data['host']))
			{
				$input = &Load::loadClass('input');

				if ($data['host']{0} == '/')
				{ 
					if (!preg_match($data['host'], $input->getHost()))
					{ 
						continue;
					}
				}
				else
				{
					if ($data['host'] != $input->getHost())
					{
						continue;
					}
				}
			}
			
			if ($matchCount <= $partCount && $static == $staticCount)
			{
				$this->name = $data['name'];
				Log::add("Route {$this->name} matched", E_DEBUG);

				$defaultVars = array('controller', 'action', 'folder');

				foreach ($defaultVars as $var)
				{
					if (!empty($data[$var]))
					{
						$this->$var = $data[$var];
					}
					elseif (!empty($variables[$this->urlVariable . $var]))
					{
						$this->$var = $variables[$this->urlVariable . $var];						
					}
				}

				foreach ($variables as $varName => $value)
				{
					$varName = substr($varName, 1);

					if (empty($this->$varName))
					{
						$this->$varName = $value;
					}
				}

				$matched = true;
				$this->data = $data;
				break;
			}			
		}

		if (!$matched && $setDefault)
		{
			$this->setDefault();
			$matched = true;
		}
		return $matched;
	}

	/**
	 * Metoda sprawdza domyslne reguly routingu jezeli nie zadeklarowano zadnych w konfiguracji
	 */
	private function setDefault()
	{
		// path[0] to pierwszy element adresu
		// mozemy przypuszczac, ze jest to nazwa kontrolera lub podkatalogu
		if ($this->parts && $this->parts[0] != '')
		{ 
			$this->controller = $this->parts[0];

			// jezeli plik o takiej nazwie zostaje odnaleziony - uznajemy, ze jest to kontroler
			if (!Load::fileExists("controller/{$this->parts[0]}.php"))
			{
				// nalezy sprawdzic, czy kontroler nie jest umieszczony w podkatalogu
				if (Load::dirExists("controller/{$this->parts[0]}"))
				{   
					if (Load::fileExists("controller/{$this->parts[0]}/{$this->parts[1]}.php"))
					{
						$this->controller = $this->parts[1];
						$this->folder = $this->parts[0];
					}
				}
			}

			if ($this->controller)
			{
				// proba odczytania nazwy akcji
				$key = $this->folder ? 2 : 1;
				if (sizeof($this->parts) > $key)
				{
					$this->action = $this->parts[$key];
					$this->args = array_slice($this->parts, ++$key);
				}
			}
		}
		
		if (!$this->controller)
		{
			// domyslny kontroler pobierany jest z konfiguracji...
			$this->controller = Config::getItem('core.defaultController', 'index');
		}
		if (!$this->action)
		{
			$this->action = 'main';
		}		
	}

	/**
	 * Metoda zwraca URL na podstawie przekazanych parametrow oraz nazwy reguly routingu
	 *
	 * Przyklad:
	 * <code>
	 * echo $router->url('Foo', array('controller' => 'Bar', 'action' => 'Main'));
	 * </code>
	 * @param string $route Nazwa reguly
	 * @param mixed $params Tablica parametrow,
	 * @param mixed $matched Referencja uzywana w helperze url.helper.php. Zawiera tablice dopasowanych elementow
	 * @return string URL
	 */
	public function url($route, $params = array(), &$matched = '')
	{
		if (!sizeof($this->routes))
		{
			return false;
		} 
		if (!isset($this->routes[$route]))
		{
			return false;
		}
		if (empty($this->routes[$route]['url']))
		{
			return false;
		}
		extract($this->routes[$route]);
		$elements = array();

		foreach (explode($this->urlDelimiter, trim($url, $this->urlDelimiter)) as $part)
		{
			if (substr($part, 0, 1) == $this->urlVariable)
			{
				$varName = substr($part, 1);

				if (isset($params[$varName]))
				{
					$elements[] = $params[$varName];
					$matched[$varName] = true;
				}
			}
			else
			{
				if (isset($params[$part]))
				{
					$elements[] = $params[$part];
				}
				else
				{
					if ($part != '*')
					{
						$elements[] = $part;
					}
				}
			}
		}
		$url = trim(implode($this->urlDelimiter, $elements), $this->urlDelimiter);

		if (!empty($host))
		{
			if (substr($host, 0, 1) == '/')
			{
				$url = 'http://' . $subdomain . $url;
			}
			else
			{
				$url = trim('http://' . $host . '/' . $url, '/');	
			}
		}	

		return $url;
	}

	/**
	 * Odczytuje argument przekazany w URLu.
	 * Zakladajac, ze URL wyglada tak: /Foo/Bar/1/2, a Foo to nazwa kontrolera, a Bar - akcji,
	 * argumentami beda parametry 1 oraz 2
	 * W parametrze metody nalezy przekazac numer indeksu w tablicy $args.
	 * Element 1 bedzie posiadal indeks 0, 2 - 1 itd...
	 * @param int $index Nr indeksu
	 * @return string
	 */
	public function getArgument($index)
	{
		if (!isset($this->args[$index]))
		{
			return '';
		}
		return $this->args[$index];
	}

	/**
	 * Zwraca tablice argumentow
	 * @return mixed
	 */
	public function getArguments()
	{
		return $this->args;
	}

	/**
	 * Zwraca wartosc parametru URL na podstawie indeksu. 
	 * Jezeli indeks nie zostanie podany, metoda zwroci tablice parametrow.
	 * Indeksy numerujemy od 1, nie od zera. Tzn. Pierwszym elementem URL bedzie element
	 * o indeksie 1.
	 * @param int $index Numer Indeksu
	 * @return mixed
	 */
	public function getParam($index = 0)
	{
		if (!$index)
		{
			return $this->param;
		}

		--$index;
		if (!isset($this->param[$index]))
		{
			return '';
		}
		return $this->param[$index];
	}

	/**
	 * Zwraca liczbe elementow w adresie URL
	 * @return int
	 */
	public function getParamsNumber()
	{
		return count($this->param());
	}

	/** 
	 * Reczne ustawienie nazwy kontrolera
	 * @param string $controller Nazwa kontrolera (np. Foo, Bar)
	 */
	public function setController($controller)
	{
		$this->controller = $controller;
	}

	/**
	 * Metoda zwraca nazwe aktualnego kontrolera
	 * @return string
	 */
	public function getController()
	{
		return strtolower($this->controller);
	}

	/**
	 * Reczne ustawienie aktualnej akcji
	 * @param string $action Nazwa akcji (metody)
	 */
	public function setAction($action)
	{
		$this->action = $action;
	}
	
	/**
	 * Metoda zwraca nazwe aktualnej akcji
	 * @return string
	 */
	public function getAction()
	{
		return strtolower($this->action);
	}

	/**
	 * Reczne ustawienie podkatalogu z kontrolerem
	 * @param string $folder
	 */
	public function setFolder($folder)
	{
		$this->folder = $folder;
	}

	/**
	 * Metoda zwraca ewentualna nazwe modulu (podkatalog!)
	 * @return string
	 */
	public function getFolder()
	{
		return strtolower($this->folder);
	}

	/**
	 * Zwraca nazwa reguly, ktora zostala dopasowana do tej strony
	 */
	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Zwraca tablice z informacjami o dopasowanej regule
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Metoda zwraca pelna sciezke do pliku klasy kontrolera (np. controller/foo.php)
	 * @return string
	 */
	public function getPath()
	{
		return strtolower('controller/' . ($this->folder ? ($this->folder . '/') : '') . $this->controller . '.php');
	}
}


?>