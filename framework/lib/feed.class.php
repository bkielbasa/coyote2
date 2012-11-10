<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa umozliwiajaca pobranie i parsowanie naglowkow RSS 2.0 oraz ATOM
 */
class Feed
{
	/**
	 * Pobranie i parsowanie naglowkow na podstawie adresu URL
	 * @param string $url Adres pod ktorym znajduja sie naglowki w formacie XML
	 * @return mixed
	 */
	public static function import($url)
	{
		if (!$xml = @file_get_contents($url))
		{
			throw new Exception("Unable to connect url: $url");
		}

		return self::importString($xml);
	}

	/**
	 * Parsowanie naglowkow na podstawie danych typu string
	 * @param string $xml Naglowki w formacie XML
	 * @return mixed
	 */
	public static function importString($xml)
	{
		$doc = new DOMDocument;
		$doc->loadXml($xml);

		if ($doc->getElementsByTagName('feed')->item(0))
		{
			return new Feed_Atom(null, $xml);
		}
		elseif ($doc->getElementsByTagName('channel')->item(0))
		{
			return new Feed_Rss(null, $xml);
		}
		else
		{
			throw new Exception('Invalid feed format');
		}
	}

	/**
	 * Parsowanie naglowkow, pobieranie ich z pliku tekstowego
	 * @param string $fileName Nazwa (sciezka) do pliku
	 * @return mixed
	 */
	public static function importFile($fileName)
	{
		$xml = @file_get_contents($fileName);
		if (!$xml)
		{
			throw new Exception("File not found: $fileName");
		}

		return self::importString($xml);
	}
}
?>