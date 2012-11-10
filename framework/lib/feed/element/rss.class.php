<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Feed_Element_Rss
{
	/**
	 * Tytul wpisu
	 */
	protected $title;
	/**
	 * Odnosnik do wpisu
	 */
	protected $link;
	/**
	 * Data publikacji materialu
	 */
	protected $pubDate;
	/**
	 * Opis 
	 */
	protected $description;

	/**
	 * Statyczna metoda konwersji tablicy do klasy
	 * @param mixed $array Tablica z danymi odnosnie wpisu (tytul, link itp(
	 */
	public static function importArray(array $array)
	{
		$object = new Feed_Element_Rss($array);
		return $object;
	}

	/**
	 * Utworzenie instancji klasy
	 * W parametrze mozliwe jest przekazanie tablicy zawierajacej informacje o wpisie
	 */
	function __construct(array $array = array())
	{
		if ($array)
		{
			foreach ($array as $key => $value)
			{
				if (method_exists($this, 'set' . $key))
				{
					$this->{'set' . $key}($value);
				}
			}
		}
	}

	/**
	 * Ustawienie tytulu dla wpisu
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * Zwraca tytul wpisu
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Ustawienie odnosnika do oryginalnej tresci wpisu
	 * @param string $link 
	 */
	public function setLink($link)
	{
		$this->link = $link;
		return $this;
	}

	/**
	 * Zwraca odnosnik do oryginalnego wpisu
	 * @param string
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * Ustawia date publikacji tresci 
	 * @param string int|string 
	 */
	public function setPubDate($pubDate)
	{
		$this->pubDate = $pubDate;
		return $this;
	}

	/**
	 * Zwraca date i czas publikacji tresci wpisu
	 * @return string|int
	 */
	public function getPubDate()
	{
		return $this->pubDate;
	}

	/**
	 * Ustawia skrocony opis dla elementu
	 * @param string
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * Zwraca skrocony opis wpisu
	 * @param string
	 */
	public function getDescription()
	{
		return $this->description;
	}
}
?>