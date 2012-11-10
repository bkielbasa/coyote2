<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Helper ulatwiajacy tworzenie znacznikow HTML
 */
class Html
{
	/**
	 * Generowanie atybutow dla znacznika HTML
	 * @param mixed $attributes
	 * @return string
	 */
	public static function attributes($attributes)
	{
		if (empty($attributes))
		{
			return false;
		}

		$output = '';
		foreach ($attributes as $key => $value)
		{
			$output .= ' ' . $key . '="' . $value . '"';
		}
		return $output;
	}

	/**
	 * Generowanie znacznika HTML
	 * @param string $name Nazwa znacznika HTML
	 * @param bool $open Jezeli TRUE - generuje znacznik <foo>, w przeciwnym przypadku - <foo />
	 * @param mixed $attrubutes Tablica atrybutow dla znacznika HTML
	 * @param string $content Zawartosc znacznika HTML
	 * @return string
	 */
	public static function tag($name, $open = false, $attributes = array(), $content = null)
	{
		$output = '<' . $name . self::attributes($attributes) . ($open ? ' >' : ' />');
		if ($content !== null)
		{
			$output .= $content;
			$output .= '</' . $name . '>';
		}
		return $output;
	}

	/**
	 * Generuje znacznik otwierajacy HTML
	 * @param string $name Nazwa znacznika
	 * @param mixed $attributes Tablica atrybutow dla znacznika HTML
	 * @param string $content Zawartosc znacznika HTML
	 * @return string
	 */
	public static function tagOpen($name, $attributes = array(), $content = '')
	{
		return self::tag($name, true, $attributes, $content);
	}

	/**
	 * Generuje znacznik zamykajacy - np. </foo>
	 * @param string $name Nazwa znacznika
	 * @return string
	 */
	public static function tagClose($name)
	{
		return self::tag($name, false);
	}

	/**
	 * Generuje znacznik <a> 
	 * @param string $url Atrybut href znacznika
	 * @param string $value Wartosc znacznika <a>wartosc</a>
	 * @param mixed $attributes Tablica atrybutow dla znacznika
	 * @return string
	 */
	public static function a($url, $value = '', $attributes = array())
	{
		$value = !$value ? $url : $value;
		$attributes['href'] = $url;

		return self::tag('a', true, $attributes, $value);
	}

	/**
	 * Generuje znacznik <a href="mailto:> 
	 * @param string $email Adres e-mail
	 * @param string $value Wartosc znacznika <a>wartosc</a>
	 * @param mixed $attributes Tablica atrybutow dla znacznika
	 * @return string
	 */
	public static function mailto($email, $value = '', $attributes = array())
	{
		$value = !$value ? $email : $value;
		$attributes['href'] = 'mailto:' . $email;

		return self::tag('a', true, $attributes, $value);
	}

	/**
	 * Generuje znacznik <img>
	 * @param string $src Sciezka do obrazka
	 * @param mixed $attributes Tablica atrybutow dla znacznika
	 * @return string
	 */
	public static function img($src, $attributes = array())
	{
		$attributes['src'] = $src;

		return self::tag('img', false, $attributes);
	}

	/**
	 * Generuje znacznik <img>
	 * @param string $src Sciezka do obrazka
	 * @param mixed $attributes Tablica atrybutow dla znacznika
	 * @return string
	 */
	public static function image($src, $attributes = array())
	{
		return self::img($src, $attributes);
	}

	/**
	 * Zwraca tekst w komentarzu XHTML
	 * @param string $value Lancuch znakow
	 * @return string
	 */
	public static function comment($value)
	{
		return '<!--' . $value . '-->';
	}

	/**
	 * Zwraca tekst w komentarzu warunkowym 
	 * @param string $value Lancuch znakow
	 * @param string $condition Warunek (wartosc tekstowa - np. gt IE6
	 * @return string
	 */
	public static function conditionalComment($value, $condition)
	{
		return '<!--[if ' . $condition . ']>' . $value . '<![endif]-->';
	}
}

?>