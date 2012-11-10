<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa obslugi tablicy globalnej $_GET
 */
class Get extends Gpc
{
	function __construct()
	{
		parent::__construct('get');

		$drop_char_match = array('^', '$', ';', '#', '&', '(', ')', '`', '\'', '|', ',', '?', '%', '~', '[', ']', '{', '}', ':', '\\', '=', '\'', '!', '"', '%20', "'");

		// uruchomienie filtra usuwajacego znaki z powyzszej tablicy
		$this->filter->addFilter(new Filter_Replace($drop_char_match));

		// przypisanie pozostalych filtrow, ktore beda operowaly na wartosciach tablicy $_GET
		$this->filter->addFilter('strip_tags');
		$this->filter->addFilter('htmlspecialchars');
		$this->filter->addFilter('trim');
	}
}

/**
 * Klasa obslugi tablicy globalnej $_POST
 */
class Post extends Gpc
{
	function __construct()
	{
		parent::__construct('post');

		// przypisanie filtra XSS
		$this->filter->addFilter(new Filter_XSS);
	}
}

/**
 * Klasa obslugi tablicy globalnej $_COOKIE
 */
class Cookie extends Gpc
{
	function __construct()
	{
		parent::__construct('cookie');

		$this->filter->addFilter(new Filter_XSS);
	}

	public function value($name, $default = '')
	{
		// odczytanie prefiksu
		$name = Config::getItem('cookie.prefix') . $name;

		return parent::value($name, $default);
	}

	public function __call($name, $args)
	{
		// odczytanie prefiksu
		$name = Config::getItem('cookie.prefix') . $name;

		return parent::__call($name, $args);
	}
}

/**
 * Klasa obslugi tablicy globalnej $_SERVER
 */
class Server extends Gpc
{
	function __construct()
	{
		parent::__construct('server');
	}
}


/**
 * Klasa request.
 * Sluzy do pobierania danych zadania z walidacja
 */
class Input
{
	/**
	 * Instancja klasy Post
	 */
	public $post;
	/**
	 * Instancja klasy Get
	 */
	public $get;
	/**
	 * Instancja klasy Cookie
	 */
	public $cookie;
	/**
	 * Instancja klasy Server
	 */
	public $server;
	/**
	 * Request method
	 */
	private $method;

	/**
	 * Metody HTTP
	 */
	const POST			= 'POST';
	const GET			= 'GET';
	const PUT			= 'PUT';
	const DELETE		= 'DELETE';
	const HEAD			= 'HEAD';

	/**
	 * Inicjalizacja klas Gpc
	 */
	function __construct()
	{
		// ladujemy klase Filter recznie, nie chcemy, aby zaladowal
		// ja loader. Bedziemy potrzebowali kilka instancji tej klasy
		Load::loadFile('lib/filter.class.php');

		if (get_magic_quotes_runtime())
		{
			set_magic_quotes_runtime(0);
			Log::add('Disable magic_quotes_runtime in your php.ini file!', E_DEBUG);
		}
		if (get_magic_quotes_gpc())
		{
			Log::add('Disable magic_quotes_gpc in your php.ini file!', E_DEBUG);

			/**
			 * Funkcja wywoluje polecenie addslashes() na kazdym elemencie tablicy
			 * @param mixed $value Element tablicy
			 */
			function gpc_escape($value)
			{
				/* warunek sprawdza, czy parametr jest tablica */
				if (is_array($value))
				{
					/* jezeli tak, wywolujemy rekurencyjnie funkcja dla tego parametru */
					$value = array_map('gpc_escape', $value);
				}
				else
				{
					$value = stripslashes($value);
				}
				return $value;
			}

			$_POST   = array_map('gpc_escape', $_POST);
			$_GET    = array_map('gpc_escape', $_GET);
			$_COOKIE = array_map('gpc_escape', $_COOKIE);
		}

		$this->get		= new Get;
		$this->post		= new Post;
		$this->cookie	= new Cookie;
		$this->server	= new Server;
	}

	/**
	 * Zwraca metode zadania
	 * @return int
	 */
	public function getMethod()
	{
		if (isset($_SERVER['REQUEST_METHOD']))
		{
			switch ($_SERVER['REQUEST_METHOD'])
			{
				case 'POST':
					$this->method = self::POST;
				break;

				case 'PUT':
					$this->method = self::PUT;
				break;

				case 'DELETE':
					$this->method = self::DELETE;
				break;

				case 'HEAD':
					$this->method = self::HEAD;
				break;

				case 'GET':
				default:
					$this->method = self::GET;
				break;
			}
		}
		else
		{
			$this->method = self::GET;
		}
		return $this->method;
	}

	/**
	 * Sprawdza, czy metoda zadania jest zgodna, z ta przekazana w parametrze
	 * @param string $method
	 * @example var_dump($this->isMethod('POST'));
	 * @return bool
	 */
	public function isMethod($method)
	{
		return strtoupper($method) == $this->getMethod();
	}

	/**
	 * Zwraca TRUE jezeli metoda zadania to POST
	 * @return bool
	 */
	public function isPost()
	{
		return $this->isMethod(self::POST);
	}

	/**
	 * Zwraca TRUE jezeli metoda zadania to GET
	 * @return bool
	 */
	public function isGet()
	{
		return $this->isMethod(self::GET);
	}

	/**
	 * Zwraca TRUE jezeli dane zadanie jest zadaniem "ajaxowym"
	 * @return bool
	 */
	public function isAjax()
	{
		return strtolower($this->server('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest';
	}

	/**
	 * Pobranie wartosci z tablicy _GET.
	 * Metoda korzysta przy tym z klasy Get
	 * @param string $key Klucz tablicy asocjacyjnej
	 * @param string $default Wartosc domyslna jezeli brak podanego klucza
	 * @return mixed
	 */
	public function get($key, $default = '')
	{
		return $this->get->$key($default);
	}

	/**
	 * Pobranie wartosci z tablicy _POST
	 * Metoda korzysta przy tym z klasy Post
	 * @param string $key Klucz tablicy asocjacyjnej
	 * @param string $default Wartosc domyslna jezeli nie ma klucza
	 * @return mixed
	 */
	public function post($key, $default = '')
	{
		return $this->post->$key($default);
	}

	/**
	 * Pobranie wartosci z tablicy _COOKIE
	 * Metoda korzysta przy tym z klasy Cookie
	 * @param string $key Klucz tablicy asocjacyjnej
	 * @param string $default Wartosc domyslna jezeli nie ma klucza
	 * @return mixed
	 */
	public function cookie($key, $default = '')
	{
		return $this->cookie->$key($default);
	}

	/**
	 * Pobranie wartosci z tablicy _SERVER
	 * Metoda korzysta przy tym z klasy Server
	 * @param string $key Klucz tablicy asocjacyjnej
	 * @param string $default Wartosc domyslna jezeli nie ma klucza
	 * @return mixed
	 */
	public function server($key, $default = '')
	{
		return $this->server->$key($default);
	}

	/**
	 * Zwraca user agent
	 */
	public function getUserAgent()
	{
		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : getenv('HTTP_USER_AGENT');
		return $this->validateValue($user_agent);
	}

	/**
	 * Pobranie adresu IP usera
	 */
	public function getIp()
	{
		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : getenv('REMOTE_ADDR');
		return $ip;
	}

	/**
	 * Zwraca nazwe aktualnie przegladanej strony
	 */
	public function getPage()
	{
		$script_name = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
		$args = (!empty($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : getenv('QUERY_STRING');

		if (!$script_name)
		{
			$script_name = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
		}
		$page = str_replace(array('\\', '//'), '/', $script_name);
		$page.= $args ? ('?' . $args) : '';

		return ($page);
	}

	/**
	 * Zwraca nazwe aktualnego hosta
	 */
	public function getHost()
	{
		return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
	}

	/**
	 * Zwraca wartosc true jezeli polaczenie jest bezpieczne (protokol https://)
	 * @return bool
	 */
	public function isSecure()
	{
		return ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') && empty($_SERVER['HTTP_X_USING_SSL'])) ? false : true;
	}

	/**
	 * Pobranie nazwy strony oraz sciezki aktualnie przegladanej strony
	 */
	public function getScriptPath()
	{
		static $script_path;

		if ($script_path)
		{
			return $script_path;
		}

		$script_name = preg_replace('#\?.*$#', '', isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI'));
		$script_name = htmlspecialchars($script_name);

		$page_dirs = (explode('/', trim(str_replace('\\', '/', $script_name), '/')));
		$root_dirs = (explode('/', trim(str_replace('\\', '/', Config::getBasePath()), '/')));

		$script_path = array_intersect($root_dirs, $page_dirs);

		if (Config::getItem('core.frontController'))
		{
			$script_path[] = Config::getItem('core.frontController');
		}
		$script_path = '/' . implode('/', $script_path);

		if ($script_path[strlen($script_path) -1] != '/')
		{
			$script_path .= '/';
		}
		return $script_path;
	}

	/**
	 * Metoda zwraca bazowy URL (host, przegladana strona
	 */
	public function getBaseUrl()
	{
		$host = Config::getItem('site.host', $this->getHost());
		return ($this->isSecure() ? 'https://' : 'http://') . $host . $this->getScriptPath();
	}

	/**
	 * Zwraca aktualny URL, na ktorym znajduje sie user
	 * @return string
	 */
	public function getCurrentUrl()
	{
		return ($this->isSecure() ? 'https://' : 'http://') . $this->getHost() . $this->server('REQUEST_URI');
	}

	/**
	 * Pobiera informacje o path info
	 */
	public function getPath()
	{
		$path_info = trim(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : getenv('PATH_INFO'), '/');
		if (empty($path_info))
		{
			$path_info = trim(isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : getenv('PATH_INFO'), '/');
		}
		$path_info = $this->validateValue($path_info);

		return $path_info;
	}

	/**
	 * Zwraca adres witryny z ktorej przyszlo zadanie
	 */
	public function getReferer()
	{
		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : getenv('HTTP_REFERER');
	}

	/**
	 * Okresla czy user jest botem.
	 * @return bool
	 */
	public function isRobot()
	{
		if (Config::getItem('robots'))
		{
			foreach ((array) Config::getItem('robots') as $k => $v)
			{
				if (strpos(strtolower($this->getUserAgent()), strtolower($k)) !== false)
				{
					return $v;
				}
			}
		}
		return false;
	}

	/**
	 * Zwraca jezyk akceptowany przez usera
	 * @return string
	 */
	public function getLanguages()
	{
		$accept_lang = preg_replace('/(;q=.+)/i', '', trim($_SERVER['HTTP_ACCEPT_LANGUAGE']));
		$accept_lang = explode(',', $accept_lang);

		return $accept_lang;
	}

	/**
	 * Prosta walidacja klucza w tablicy asocjacyjnej.
	 */
	private function validateKey(&$key)
	{
		if (!preg_match("/^[a-z0-9:_\/-]+$/i", $key))
		{
			trigger_error('Invalid key characters', E_USER_ERROR);
		}
	}

	/**
	 * Usuniecie kodu HTML oraz niepotrzebnych znakow z lancucha
	 * @param string $value
	 * @return string
	 */
	private function validateValue(&$value)
	{
		$drop_char_match = array('^', '$', ';', '#', '&', '(', ')', '`', '\'', '|', '?', '%', '~', '[', ']', '{', '}', ':', '\\', '=', '\'', '!', '"', '%20', "'");

		$value = str_replace($drop_char_match, '', $value);
		$value = trim(htmlspecialchars(strip_tags($value)));

		return $value;
	}


}
?>