<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa macierzysta dla klas Feed_Atom, Feed_Rss
 */
abstract class Feed_Abstract implements Countable, Iterator, ArrayAccess
{	
	/**
	 * Wersja pliku XML
	 */
	protected $version = '1.0';
	/** 
	 * Kodowanie pliku XML (atrybut <?xml encoding="...
	 */
	protected $encoding = 'UTF-8';
	/**
	 * Wewnetrze pole
	 */
	protected $position = 0;
	/**
	 * Elementy kanalu
	 */
	protected $elements = array();

	/**
	 * @param string $url Adres do pliku z naglowkami RSS lub ATOM
	 * @param string $xml Lancuch w formacie Atom lub RSS (XML)
	 */
	function __construct($url = null, $xml = null)
	{
		if ($url !== null)
		{
			$xml = @file_get_contents($url);

			if (!$xml)
			{
				throw new Exception("Unable to connect $url");
			}

			$this->loadXml($xml);
		}
		elseif ($xml !== null)
		{
			$this->loadXml($xml);
		}
	}

	abstract public function loadXml($xml);
	abstract public function saveXml();

	/**
	 * Ustawienie wersji dla pliku XML
	 * @param string $version
	 */
	public function setVersion($version)
	{
		$this->version = $version;
		return $this;
	}

	/**
	 * Zwraca numer wersji dla pliku XML
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Ustawienie kodowania dla pliku XML
	 * @param string $encoding 
	 */
	public function setEncoding($encoding)
	{
		$this->encoding = $encoding;
		return $this;
	}

	/**
	 * Zwraca kodowanie dla pliku XML
	 * @return string
	 */
	public function getEncoding()
	{
		return $this->encoding;
	}

	/**
	 * Implementacja interfejsu Countable
	 */
	public function count()
	{
		return sizeof($this->elements);
	}

	/**
	 * Implementacja interfejsu Iterator
	 */
	public function rewind()
	{
		$this->position = 0;
	}

	/**
	 * Implementacja interfejsu Iterator
	 */
	public function current()
	{
		return $this->elements[$this->position];
	}

	/**
	 * Implementacja interfejsu Iterator
	 */
	public function key()
	{
		return $this->position;
	}

	/**
	 * Implementacja interfejsu Iterator
	 */
	public function next()
	{
		++$this->position;
	}

	/**
	 * Implementacja interfejsu Iterator
	 */
	public function valid()
	{
		return isset($this->elements[$this->position]);
	}

	/**
	 * Implementacja interfejsu ArrayAccess
	 */
	public function offsetSet($offset, $value)
	{
		throw new Exception('Can\'t set value to feed\'s object');
	}

	/**
	 * Implementacja interfejsu ArrayAccess
	 */
	public function offsetExists($offset)
	{
		return isset($this->elements[$offset]);
	}

	/**
	 * Implementacja interfejsu ArrayAccess
	 */
	public function offsetUnset($offset)
	{
		unset($this->elements[$offset]);
	}

	/**
	 * Implementacja interfejsu ArrayAccess
	 */
	public function offsetGet($offset)
	{
		return isset($this->elements[$offset]) ? $this->elements[$offset] : null;
	}
}
?>