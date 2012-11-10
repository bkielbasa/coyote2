<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa obslugi response
 */
class Output
{
	/**
	 * Tytul strony <title>
	 */
	private $pageTitle;
	/**
	 * Znaczniki <meta>
	 */
	private $meta = array();
	/**
	 * Dodatkowe znaczniki <meta>
	 */
	private $httpMeta = array();
	/**
	 * Tablica zawierajaca nazwy plikow CSS
	 */
	private $stylesheet = array();
	/** 
	 * Tablica zawierajaca nazwy plikow CSS (ktore beda zawarte w instrukcji warnkowej)
	 */
	private $stylesheetCondition = array();
	/**
	 * Tablica skryptow JavaScript, ktore maja byc dolaczane w kodzie widoku
	 */
	private $javascript = array();
	/**
	 * Tablica dodatkowych atrybutow 
	 */
	private $attribute = array();
	/**
	 * Zawiera wskazanie na bazowy URL (znacznik <base href="...)
	 */
	private $base;
	/**
	 * Kody statusow HTTP
	 */
	private $statusText = array(

			'100'	=> 'Continue',
			'101'	=> 'Switching Protocols',
			'200'	=> 'OK',
			'201'	=> 'Created',
			'202'	=> 'Accepted',
			'203'	=> 'Non-Authoritative Information',
			'204'	=> 'No Content',
			'205'	=> 'Reset Content',
			'206'	=> 'Partial	Content',
			'300'	=> 'Multiple Choices',
			'301'	=> 'Moved Permanently',
			'302'	=> 'Found',
			'303'	=> 'See	Other',
			'304'	=> 'Not	Modified',
			'305'	=> 'Use	Proxy',
			'306'	=> '(Unused)',
			'307'	=> 'Temporary Redirect',
			'400'	=> 'Bad	Request',
			'401'	=> 'Unauthorized',
			'402'	=> 'Payment	Required',
			'403'	=> 'Forbidden',
			'404'	=> 'Not	Found',
			'405'	=> 'Method Not Allowed',
			'406'	=> 'Not	Acceptable',
			'407'	=> 'Proxy Authentication Required',
			'408'	=> 'Request	Timeout',
			'409'	=> 'Conflict',
			'410'	=> 'Gone',
			'411'	=> 'Length Required',
			'412'	=> 'Precondition Failed',
			'413'	=> 'Request	Entity Too Large',
			'414'	=> 'Request-URI	Too	Long',
			'415'	=> 'Unsupported	Media Type',
			'416'	=> 'Requested Range	Not	Satisfiable',
			'417'	=> 'Expectation	Failed',
			'500'	=> 'Internal Server	Error',
			'501'	=> 'Not	Implemented',
			'502'	=> 'Bad	Gateway',
			'503'	=> 'Service	Unavailable',
			'504'	=> 'Gateway	Timeout',
			'505'	=> 'HTTP Version Not Supported'
	);

	/**
	 * Ustawia cookie
	 * @param string $name Nazwa ciastka (jezeli w konfiguracji zostal ustawiony prefiks, nazwa zostanie nim poprzedzona)
	 * @param string $value Wartosc zapisana w ciastku
	 * @param int $expired Data wygasniecia ciastka (unix timestamp)
	 * @param string $path Sciezka cookie
	 * @param string $host Opcjonalnie, host z jakim zostanie zapisane cookie
	 * @param bool $secure Opcjonalny parametr oznaczajacy, czy ciastko bedzie ustawione na https://
	 * @param bool $httponly Opcjonalny parametr httponly zabezpieczajacy przed odczytaniem cookie
	 */
	public function setCookie($name, $value, $expired, $path = '', $host = '', $secure = 0, $httponly = true)
	{
		if (empty($path))
		{
			$path = '/';			
		}
		
		/* j/w */
		if (empty($host))
		{
			$host = Config::getItem('cookie.host');
		}

		/* j/w */
		if (empty($secure))
		{
			if (!$secure = Config::getItem('cookie.secure'))
			{
				$secure = 0;
			}
		}
		$_COOKIE[Config::getItem('cookie.prefix') . $name] = $value;

		/* ustaw ciastko, zastouj prefix podany w tablicy $config dla nazwy ciastka */
		setcookie(Config::getItem('cookie.prefix') . $name, $value, $expired, $path, $host, $secure, $httponly);
	}

	/**
	 * Ustawia tytul dla strony
	 * @param string $pageTitle Tytul strony
	 */
	public function setTitle($pageTitle)
	{
		$this->pageTitle = $pageTitle;
	}

	/**
	 * Ustawia znacznik <meta> dla strony
	 * @param string $key Klucz dla elementu meta
	 * @param string $value Wartosc dla parametru meta
	 */
	public function setMeta($key, $value = '')
	{   
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->meta[$k] = $v;
			}
		}
		else
		{
			$this->meta[$key] = $value;
		}
	}

	/**
	 * Usuwa znacznik meta z tablicy
	 * @param string $key Klucz tablicy
	 */
	public function removeMeta($key)
	{
		unset($this->meta[$key]);
	}

	/**
	 * Ustawia znacznik <meta> dla strony
	 * @param string $key Klucz dla elementu meta
	 * @param string $value Wartosc dla parametru meta
	 */
	public function setHttpMeta($key, $value = '')
	{
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->httpMeta[$k] = $v;
			}
		}
		else
		{
			$this->httpMeta[$key] = $value;
		}
	}

	/**
	 * Usuwa znacznik http meta z tablicy
	 * @param string $key Klucz tablicy
	 */
	public function removeHttpMeta($key)
	{
		unset($this->httpMeta[$key]);
	}

	/**
	 * Dodaje sciezke do pliku CSS dla strony
	 * @param string $filename Nazwa lub dokladna sciezka do pliku CSS
	 * @param mixed $attributes Dodatkowe atrybuty dla znacznika <link...
	 */
	public function addStylesheet($filename, $attributes = array())
	{
		if (strpos($filename, ',') === false)
		{
			$this->stylesheet[$filename] = $attributes;
		}
		else
		{
			foreach (explode(',', $filename) as $stysheet)
			{
				$this->stylesheet[trim($stysheet)] = $attributes;
			}
		}
	}

	/**
	 * Ustawia sciezke do pliku CSS dla strony
	 * @param string $filename Nazwa lub dokladna sciezka do pliku CSS (moze byc tablica)
	 * @param mixed $attributes Dodatkowe atrybuty dla znacznika <link...
	 */
	public function setStylesheet($filename, $attributes = array())
	{ 
		if (is_array($filename))
		{
			foreach ($filename as $arr)
			{
				/** 
				 * Poprawka dla PHP 5.3. We wczesniejszych wersjach przekazanie 
				 * lancucha w parametrze funkcji call_user_func_array() nie powodowalo
				 * komunikatu ostrzezenia
				 */
				if (!is_array($arr))
				{
					$arr = array($arr);
				}
				call_user_func_array(array(&$this, 'addStylesheet'), $arr);
			}
		}
		else
		{
			$this->addStylesheet($filename, $attributes);
		}
	}

	/**
	 * Dodaje sciezke do pliku CSS dla strony
	 * Pliki CSS beda zawarte w instrukcji warunkowej
	 * @param string $filename Nazwa lub dokladna sciezka do pliku CSS
	 * @param mixed $attributes Dodatkowe atrybuty dla znacznika <link...
	 * @param string $condition Warunek (lancuch znakow)
	 */
	public function addStylesheetConditional($filename, $attributes = array(), $condition = '')
	{
		$this->addStylesheet($filename, $attributes);
		$this->stylesheetCondition[$filename] = $condition;
	}

	/**
	 * Ustawia sciezke do pliku CSS dla strony
	 * Pliki CSS beda zawarte w instrukcji warunkowej
	 * @param string $filename Nazwa lub dokladna sciezka do pliku CSS (moze byc tablica)
	 * @param mixed $attributes Dodatkowe atrybuty dla znacznika <link...
	 * @param string $condition Warunek (lancuch znakow)
	 */
	public function setStylesheetConditional($filename, $attributes = array(), $condition = '')
	{
		if (is_array($filename))
		{
			if (is_array($filename[0]))
			{
				foreach ($filename as $arr)
				{
					$this->setStylesheetConditional($arr);
				}
			}
			else
			{
				/** 
				 * Poprawka dla PHP 5.3. We wczesniejszych wersjach przekazanie 
				 * lancucha w parametrze funkcji call_user_func_array() nie powodowalo
				 * komunikatu ostrzezenia
				 */
				if (!is_array($filename))
				{
					$filename = array($filename);
				}
				call_user_func_array(array(&$this, 'addStylesheetConditional'), $filename);	
			}
		}
		else
		{
			$this->addStylesheetConditional($filename, $attributes, $condition);
		}
	}

	/**
	 * Usuwa okreslony styl CSS z tablicy
	 * @param string $filename Nazwa stylu (pliku)
	 */
	public function removeStylesheet($filename)
	{
		unset($this->stylesheet[$filename]);
		unset($this->stylesheetCondition[$filename]);
	}

	/**
	 * Dodaje do listy skrypt JavaScript, ktory zostanie dolaczony w widoku
	 * @param string $filename Nazwa pliku JS
	 * @param string $directory Katalog, w ktorym znajduje sie skrypty JavaScript (opcjonalnie)
	 */
	public function addJavascript($filename)
	{
		if (strpos($filename, ',') === false)
		{
			$this->javascript[$filename] = true;
		}
		else
		{
			foreach (explode(',', $filename) as $javascript)
			{
				$this->javascript[trim($javascript)] = true;
			}
		}
	}

	/**
	 * Ustawia nazwe pliku JS, ktory zostanie dolaczony w widoku
	 * @param string $filename Nazwa pliku JS lub tablica 
	 */
	public function setJavascript($filename)
	{
		if (is_array($filename))
		{
			foreach ($filename as $arr)
			{
				/** 
				 * Poprawka dla PHP 5.3. We wczesniejszych wersjach przekazanie 
				 * lancucha w parametrze funkcji call_user_func_array() nie powodowalo
				 * komunikatu ostrzezenia
				 */
				if (!is_array($arr))
				{
					$arr = array($arr);
				}
				call_user_func_array(array(&$this, 'addJavascript'), $arr);
			}
		}
		else
		{
			$this->addJavascript($filename);
		}
	}

	/**
	 * Usuwa skrypt JavaScript dodany do listy
	 * @param string $filename Nazwa pliku JavaScript
	 */
	public function removeJavascript($filename)
	{
		unset($this->javascript[$filename]);
	}

	/**
	 * Ustawia dodatkowe atrybuty dla danej strony
	 * @param string $key Klucz 
	 * @param string $value Wartosc dla danego klucza
	 */
	public function setAttribute($key, $value = '')
	{
		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$this->attribute[$k] = $v;
			}
		}
		else
		{
			$this->attribute[$key] = $value;
		}
	}

	/**
	 * Ustawia naglowek Content-Type dla strony
	 * @param string $value Wartosc Content-Type
	 */
	public function setContentType($value)
	{
		$this->setHttpHeader('Content-type', $value);
	}

	/**
	 * Wysyla naglowek HTTP. 
	 * @param mixed|string Tablica z naglowkami lub wartosc - klucz - naglowka
	 * @param string $value Wartosc naglowka HTTP
	 */
	public function setHttpHeader($header, $value = '')
	{
		if (is_array($header))
		{
			foreach ($header as $k => $v)
			{ 
				header($k . ': ' . $v);	
			}
		}
		else
		{
			header($header . ': ' . $value);	
		}
			
	}
	
	/**
	 * Ustawia status bledu dla strony, np. HTTP/1.0 Not Found
	 * @param int $code Numer bledu
	 */
	public function setStatusCode($code)
	{
		if (is_numeric($code))
		{
			$code = $code . ' ' . $this->statusText[$code];
		}
		header('HTTP/1.0 ' . $code);
	}

	/**
	 * Ustawia bazowy adres dla danej strony (znacznik <base href="...)
	 * @param string $url Adres bazowy
	 */
	public function setBase($url)
	{
		if (!preg_match('#^[\w]+?://.*?#i', $url))
		{
			$url = 'http://' . $url;
		}
		if ($url[strlen($url) -1] != '/')
		{
			$url .= '/';
		}
		$this->base = $url;
	}

	/**
	 * Zwraca tytul dla danej strony w formie znacznika XHTML
	 * @return string
	 */
	public function getTitle()
	{
		if ($this->pageTitle)
		{
			return '<title>' . $this->pageTitle . "</title>\n";
		}
	}
	
	/**
	 * Zwraca dodatkowe znaczniki meta w formie XHTML
	 * W przypadku podania parametru $key, zwracana jest wartosc danego elementu tablicy
	 * @return string 
	 */
	public function getMeta($element = null)
	{
		if ($element !== null)
		{
			return isset($this->meta[$element]) ? $this->meta[$element] : null;
		}
		else
		{
			$xhtml = '';
			if ($this->meta)
			{
				foreach ($this->meta as $k => $v)
				{
					$xhtml .= '<meta name="' . $k . '" content="' . $v . "\" /> \n";
				}
			}
			return $xhtml;
		}
	}

	/**
	 * Zwraca dodatkowe znaczniki meta w formie XHTML
	 * W przypadku podania parametru $key, zwracana jest wartosc danego elementu tablicy
	 * @return string 
	 */
	public function getHttpMeta($element = null)
	{
		if ($element !== null)
		{
			return isset($this->httpMeta[$element]) ? $this->httpMeta[$element] : null;
		}
		else
		{
			$xhtml = '';
			if ($this->httpMeta)
			{
				foreach ($this->httpMeta as $key => $v)
				{
					$xhtml .= '<meta http-equiv="' . $key . '" content="' . $v . "\" />\n";
				}
			}
			return $xhtml;
		}
	}

	/**
	 * Zwraca znaczniki dla stylow CSS
	 * @return string
	 */
	public function getStylesheet()
	{
		$xhtml = '';
		if ($this->stylesheet)
		{
			foreach ($this->stylesheet as $filename => $options)
			{
				$output = '';

				if ($filename{0} === '@')
				{
					$url = Url::__($filename);
				}
				else
				{
					$suffix = strrchr($filename, '.');
					if ($suffix === false || strlen($suffix) > 4)
					{
						$filename .= '.css';
					}

					if ('http' === substr($filename, 0, 4))
					{
						$url = $filename;
					}
					// warunek bedzie wykonany w momencie gdy nazwa pliku CSS jest regula routingu
					else
					{
						$url = Url::site() . Config::getItem('core.template') . '/' . $filename;
					}		
				}
	
				$output .= '<link rel="stylesheet" type="text/css" href="' . $url . '" ' . Html::attributes($options) . '/>';
				$output .= "\n";

				if (isset($this->stylesheetCondition[$filename]))
				{
					$output = Html::conditionalComment($output, $this->stylesheetCondition[$filename]);
				}

				$xhtml .= $output;
			}
		}
		return $xhtml;
	}

	/**
	 * Zwraca sciezki do plikow JavaScript w formie znacznika <script>
	 * @return string
	 */
	public function getJavascript()
	{
		$xhtml = '';
		if ($this->javascript)
		{
			foreach ($this->javascript as $filename => $options)
			{
				if ($filename{0} === '@')
				{
					$url = Url::__($filename);
				}
				else
				{
					$suffix = strrchr($filename, '.');
					if ($suffix === false || strlen($suffix) > 3)
					{
						$filename .= '.js';
					}

					if ('http' === substr($filename, 0, 4))
					{
						$url = $filename;
					}
					// warunek bedzie wykonany w momencie gdy nazwa pliku JS jest regula routingu
					else
					{
						$url = Url::site() . Config::getItem('core.js') . '/' . $filename;
					}		
				}
				
				$xhtml .= '<script src="' . $url . '" type="text/javascript"></script>';
				$xhtml .= "\n";
			}
		}
		return $xhtml;
	}

	/**
	 * Zwraca przekazany wczesniej atrybut
	 * @param string $key Klucz atrybutu
	 * @return mixed
	 */
	public function getAttribute($key)
	{
		if (isset($this->attribute[$key]))
		{
			return $this->attribute[$key];
		}
	}

	/**
	 * Zwraca znacznik <base href="... jezeli URL jest ustawiony
	 * @return string
	 */
	public function getBase()
	{
		if ($this->base)
		{
			return '<base href="' . $this->base . '" />';
		}
	}
}
?>