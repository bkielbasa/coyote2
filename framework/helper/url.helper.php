<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Wyswietlanie i formowanie linkow
 */
class Url
{
	/**
	 * Metoda zwraca bazowy URL strony projektu
	 * @example http://server.com/coyote/index.php
	 */
	public static function base()
	{
		return Core::getInstance()->input->getBaseUrl(); 
	}

	/**
	 * Metoda zwraca bazowy URL strony projektu
	 * Roznica w stosunku do base() jest taka, ze pomija ewentualna nazwe 
	 * skryptu (index.php)
	 * @see base()
	 * @example
	 * <code>
	 * echo site(); // http://localhost/coyote/
	 * echo base(); // http://localhost/coyote/index.php
	 * </code>
	 */
	public static function site()
	{
		$url = Core::getInstance()->input->getBaseUrl();

		if (Config::getItem('core.frontController'))
		{
			$url = str_replace(Config::getItem('core.frontController'), '', trim($url, '/'));
		}
		return $url;
	}

	/**
	 * Zwraca aktualny URL 
	 */
	public static function current()
	{
		return Core::getInstance()->input->getCurrentUrl();
	}

	/**
	 * Realizuje skracanie URL'a jezeli jego dlugosc przekracza 70 znakow
	 * @param string $url URL
	 * @return string
	 */
	function limit($url)
	{
		$url_len = strlen($url);

		if ($url_len > 70)
		{
			/* pobierz jedynie pierwsze i ostatnie 30 znakow */
			$url = substr($url, 0, 30) . '[...]' . substr($url, $url_len - 30, 30);
		}

		return $url;
	}

	/**
	 * Przekierowanie na podany URL
	 * @param string $url URL
	 * @param int $code Kod naglowka HTTP (opcjonalnie)
	 */
	public function redirect($url, $code = null)
	{
		if (!$url)
		{
			$url = Url::base();
		}
		if (function_exists('url'))
		{
			$url = url($url);
		}
		$url = trim(str_replace(array("\r", "\n"), '', str_replace('&amp;', '&', $url)));
		if ($code != null)
		{
			Core::getInstance()->output->setStatusCode($code);
		}

		/* sprawdzenie oprogramowania serwera */
		if (@preg_match('#Microsoft|WebSTAR|Xitami#', getenv('SERVER_SOFTWARE')))
		{
			header('Refresh: 0; URL=' . $url);
			/* wyslanie odpowiedniego kodu HTML */
			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2"><meta http-equiv="refresh" content="0; url=' . $url . '"><title>Redirect</title></head><body><div align="center"><a href="' . $url . '">Redirecting...</a></div></body></html>';
			exit;
		}
		/* przekierowanie przy pomocy funkcji header() jezeli powyzszy warunek nie zostanie spelniony */
		header('Location: ' . $url);
		exit;
	}

	/**
	 * Funkcja zwraca absolutny lub relatywny URL 
	 * @param string $url URL (moze byc nazwa reguly routingu jezeli zaczyna sie od znaku @)
	 * @param bool $absolute Wartosc TRUE oznacza, iz zwrocony URL bedzie absolutny
	 * @return string URL
	 */
	public static function __($url, $absolute = true)
	{ 
		if (empty($url))
		{
			return;
		}

		if ($url{0} == '@')
		{  
			$router = &Load::loadClass('router');
			preg_match('/@([a-zA-Z0-9_-]*)(.*)?/', $url, $m);

			$param = $arr = array();

			if ($m[2] && @$m[2]{0} == '?')
			{
				parse_str(substr($m[2], 1), $param); 
			}
			$url = $router->url($m[1], $param, $arr);		

			if ($m[2])
			{
				if ($param)
				{
					$http_query = array_diff_key($param, $arr);

					if ($http_query)
					{ 
						$url .= $m[2] = '?' . http_build_query($http_query);
					}
				}	
				else
				{
					$url .= $m[2];
				}
			} 
		}
		if ($absolute)
		{
			if (!preg_match('#^[\w]+?://.*?#i', $url))
			{
				$url = Url::base() . $url;
			}
		}
		return $url;
	}

}


/**
 * Funkcja zwraca absolutny lub relatywny URL 
 * @param string $url URL (moze byc nazwa reguly routingu jezeli zaczyna sie od znaku @)
 * @param bool $absolute Wartosc TRUE oznacza, iz zwrocony URL bedzie absolutny
 * @return string URL
 */
function url($url, $absolute = true)
{ 	
	return Url::__($url, $absolute);
}


?>