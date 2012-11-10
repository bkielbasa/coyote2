<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Helper sluzy do generowania breadcrumb
 */
class Breadcrumb
{
	/**
	 * Tablica elementow
	 */
	private static $elements = array();
	/**
	 * Separator elementow
	 */
	private static $separator = ' <li class="breadcrumb-separator">»</li> ';
	/**
	 * Wlaczone/wylaczone
	 */
	private static $enable = true;
	/**
	 * URL bazowego elementu (URL pierwszego elementu w breadcrumb)
	 */
	private static $baseUrl;
	/**
	 * Tytul bazowego elementu
	 */
	private static $baseElement;

	/**
	 * Ustawia separator 
	 * @param string $separator Separator
	 */
	public static function setSeparator($separator)
	{
		self::$separator = $separator;
	}

	/**
	 * Ustawia bazowy URL
	 * @param string $baseUrl Bazowy URL
	 */
	public static function setBaseUrl($baseUrl)
	{
		self::$baseUrl = $baseUrl;
	}

	/**
	 * Ustawia bazowy element
	 * @param string $baseElement Bazowy element
	 */
	public static function setBaseElement($baseElement)
	{
		self::$baseElement = $baseElement;
	}

	/**
	 * Dezaktywuje helper
	 */
	public static function disable()
	{
		self::$enable = false;
	}

	/**
	 * Metoda sluzy do dodawania kolejnego elementu do breadcrumb
	 * @param string $url URL
	 * @param string $element Tytul elementu
	 * @param mixed $attributes Dodatkowe atrybuty dla elementu <a>
	 */
	public static function add($url, $element, $attributes = array())
	{
		self::$elements[] = array($element, $url, $attributes);
	}

	/**
	 * Generowanie breadcrumb
	 * @return string Kod XHTML
	 */
	public static function display()
	{
		if (!self::$enable || !self::$elements)
		{
			return;
		}
		if (!self::$baseUrl)
		{
			self::$baseUrl = Url::base();
		}
		if (!self::$baseElement)
		{
			self::$baseElement = Config::getItem('site.title');
		}
		$breadcrumb[] = '<li>' . Html::a(self::$baseUrl, self::$baseElement) . '</li>';
		$counter = 0;
		$elementsSize = count(self::$elements);

		while (list(, $arr) = each(self::$elements))
		{
			list($element, $url, $attributes) = $arr;

			if (++$counter == $elementsSize)
			{
				$breadcrumb[] = '<li>' . Html::tag('strong', true, $attributes, Html::a($url, $element)) . '</li>';

			}
			else
			{
				$breadcrumb[] = '<li>' . Html::a($url, $element) . '</li>';
			}
		}
		reset(self::$elements);

		return '<ul>' . implode(self::$separator, $breadcrumb) . '</ul>';
	}
}
?>