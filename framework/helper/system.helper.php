<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Funkcja laduje helper o podanej nazwie
 * @param string $helper Nazwa helpera
 */
function load_helper($helper)
{
	Core::getInstance()->load->helper($helper);
}

/**
 * Helper zwraca znacznik <title> z tytulem strony
 * Zawartosc znacznika odczytywana jest z klasy output, z metody getTitle()
 * @return string
 */
function include_title()
{
	$core = &Core::getInstance();

	if (class_exists('output', false))
	{
		return $core->output->getTitle();
	}
}

/**
 * Funkcja zwraca znaczniki <meta>
 * @return string
 */
function include_meta()
{
	$core = &Core::getInstance();

	if (class_exists('output', false))
	{
		return ($core->output->getMeta() . $core->output->getHttpMeta());
	}
}

/**
 * Helper zwraca znaczniki z lista stylow CSS 
 * @return string
 */
function include_stylesheet()
{
	$core = &Core::getInstance();

	if (class_exists('output', false))
	{
		return $core->output->getStylesheet();
	}
}

/**
 * Helper zwraca znaczniki <script> ze scieckami do plikow JS 
 * @return string
 */
function include_javascript()
{
	$core = &Core::getInstance();

	if (class_exists('output', false))
	{
		return $core->output->getJavascript();
	}
}

/**
 * Zwraca adres bazowy, np. http://127.0.0.1
 * @retun string
 */
function include_base()
{
	if (class_exists('output', false))
	{
		return Core::getInstance()->output->getBase();
	}
}

/**
 * Zwraca wartosc domyslna
 *
 * <code>
 * echo def($_GET['name'], 'Adam');
 * </code>
 * W takim przypadku, jezeli klucz 'name' tablicy $_GET, to wartosc pusta
 * zostanie zwrocona wartosc 'Adam'. 
 * Funkcja moze posiadac wiele parametrow. Odczytuje je po kolei i sprawdza
 * wartosci. Konczy dzialanie w przypadku odnalezenia niepustej wartosci
 */
function def()
{
	$arg = func_get_args();
	foreach ($arg as $k => $v)
	{
		if (!empty($arg[$k]))
		{
			return $v;
		}		
	}
}

/**
 * Helper pozwalajacy na szybsze uzycie metody debug
 * @param mixed
 */
function debug()
{
	Core::debug(func_get_args());
}

/**
 * Helper wywoluje metode z klasy lang
 * @param string Klucz w tablicy zawierajacej lancuchy jezykowe
 * @param string Opcjonalny argument oznaczajacy katalog jezykowy (np. pl-Pl, en-US)
 * @return string
 */
function __($lang_key, $locale = null)
{
	if (isset(Core::getInstance()->lang))
	{ 
		return (Core::getInstance()->lang->_($lang_key, $locale));
	}
	else
	{
		return $lang_key;
	}
}

?>