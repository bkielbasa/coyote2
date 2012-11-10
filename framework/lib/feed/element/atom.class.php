<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa dla elementu wpisu atom
 */
class Feed_Element_Atom
{
	const HTML				=	'html';
	const XHTML				=	'xhtml';
	const TEXT				=	'text';
	
	/**
	 * Tytul wpisu
	 */
	protected $title;
	/**
	 * Odnosnik do wpisu
	 */
	protected $link;
	/**
	 * Data modyfikacji wpisu
	 */
	protected $updated;
	/**
	 * Unikalne ID wpisu
	 */
	protected $id;
	/**
	 * Nazwa autora
	 */
	protected $authorName;
	/**
	 * E-mail autora
	 */
	protected $authorEmail;
	/**
	 * Opis wpisu
	 */
	protected $summary;
	/**
	 * Pelna zawartosc wpisu
	 */
	protected $content;
	/**
	 * Rodzaj wpisu (tekst, kod html, kod xhtml)
	 */
	protected $contentType;

	/**
	 * Statyczna metoda konwersji tablicy do klasy
	 * @param mixed $array Tablica z danymi odnosnie wpisu (tytul, link itp(
	 */
	public static function importArray(array $array)
	{
		/** 
		 * Utworzenie instancji klasy i przekazanie tablicy danych odnosnie wpisu
		 */
		$object = new Feed_Element_Atom($array);
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
		$this->title = htmlspecialchars($title);
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
	 * Ustawia date modyfikacji tresci 
	 * @param string int|string 
	 */
	public function setUpdated($updated)
	{
		$this->updated = $updated;
		return $this;
	}

	/**
	 * Zwraca date i czas modyfikacji tresci wpisu
	 * @return string|int
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 * Ustawia unikalne ID dla wpisu
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * Zwraca ID wpisu
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Ustawia autora wpisu 
	 * @param string $name Nazwa autora
	 * @param string $email E-mail (opcjonalnie)
	 */
	public function setAuthor($name, $email = '')
	{
		$this->authorName = htmlspecialchars($name);
		$this->authorEmail = htmlspecialchars($email);
		
		return $this;
	}
	
	/**
	 * Zwraca nazwe autora wpisu
	 */	
	public function getAuthorName()
	{
		return $this->authorName;
	}
	
	/**
	 * Zwraca e-mail autora wpisu
	 */	
	public function getAuthorEmail()
	{
		return $this->authorEmail;
	}

	/**
	 * Ustawia skrocony opis dla elementu
	 * @param string
	 */
	public function setSummary($summary)
	{
		$this->summary = htmlspecialchars($summary);
		return $this;
	}
	
	/**
	 * Zwraca skrocony opis wpisu
	 * @param string
	 */
	public function getSummary()
	{
		return $this->summary;
	}
	
	/**
	 * Ustawia tresc wpisu 
	 * Tresc moze byc kodem XHTML/HTML lub czystym tekstem
	 * 
	 * @param $content 
	 * @param $contentType self::HTML/self::XHTML/self::TEXT
	 */
	public function setContent($content, $contentType = self::HTML)
	{
		if ($contentType == self::HTML || $contentType == self::TEXT)
		{
			$content = htmlspecialchars($content);			
		}
		elseif ($contentType == self::XHTML)
		{
			$content = '<div xmlns="http://www.w3.org/1999/xhtml">' . $content . '</div>';
		}
		else
		{
			throw new Exception('Invalid content type');
		}
		
		$this->content = $content;
		$this->contentType = $contentType;
		
		return $this;
	}
	
	/**
	 * Zwraca zawartosc wpisu
	 */	
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	 * Zwraca typ wpisu
	 */
	public function getContentType()
	{
		return $this->contentType;
	}
}
?>