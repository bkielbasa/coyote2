<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa odczytywania/generowania naglowkow Atom
 */
class Feed_Atom extends Feed_Abstract
{
	const NS = 'http://www.w3.org/2005/Atom';

	/**
	 * Tytul kanalu
	 */
	protected $title;
	/**
	 * Odnosnik do kanalu
	 */
	protected $link;
	/**
	 * Unikalne ID
	 */
	protected $id;
	/**
	 * Data wygenerowania naglowka
	 */
	protected $updated;
	
	/**
	 * Metoda generuje ciag UUID
	 * @param string $prefix Prefiks dla wygenerowanego ciagu
	 * @param string $hash Ciag na podstawie ktorego wygenerowany zostanie klucz
	 * @return string
	 */
	public static function getUuid($prefix = '', $hash = null)
	{
		$chars = md5($hash === null ? uniqid(mt_rand(), true) : $hash);

		$uuid  = substr($chars, 0, 8) . '-';
		$uuid .= substr($chars, 8, 4) . '-';
		$uuid .= substr($chars, 12, 4) . '-';
		$uuid .= substr($chars, 16, 4) . '-';
		$uuid .= substr($chars, 20, 12);
		return $prefix . $uuid;
	}	

	/**
	 * Parsownie pliku XML w formacie atom
	 * @param string $xml Dane w formacie XML
	 */
	public function loadXml($xml)
	{
		$doc = DOMDocument::loadXml($xml);

		$this->setTitle($doc->getElementsByTagName('title')->item(0)->nodeValue);
		$this->setUpdated($doc->getElementsByTagName('updated')->item(0)->nodeValue);
		$this->setId($doc->getElementsByTagName('id')->item(0)->nodeValue);
		$this->setLink($doc->getElementsByTagName('link')->item(0)->getAttribute('href'));		

		foreach ($doc->getElementsByTagName('entry') as $entries)
		{
			$entry = array();

			// odczyt kolejnych wpisow
			foreach ($entries->getElementsByTagName('*') as $element)
			{
				if ($element->nodeName == 'link')
				{
					$entry['link'] = $element->getAttribute('href');
				}
				else
				{
					$entry[$element->nodeName] = $element->nodeValue;
				}
			}
			
			$this->addElement(Feed_Element_Atom::importArray($entry));
		}
	}

	/**
	 * Ustawienie tytulu dla kanalu
	 * @param string $title
	 * @return 
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * Zwraca tytul kanalu atom
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Ustawienie linka do kanalu atom
	 * @param string $link
	 */
	public function setLink($link)
	{
		$this->link = $link;
		return $this;
	}

	/**
	 * Zwraca odnosnik do kanalu
	 * @return string
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * Ustawia date ostatniej modyfikacji kanalu
	 * @param int|string $updated Jezeli generujemy naglowki, nalezy przekazac wartosc timestamp
	 */
	public function setUpdated($updated)
	{
		$this->updated = $updated;
		return $this;
	}

	/**
	 * Zwraca informacje odnosnie ostatniej daty wygenerowania kanalu 
	 * @return string|int
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 * Ustawia unikalne ID dla kanalu
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * Zwraca ID dla kanalu
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}	

	/**
	 * Dodanie wpisu (elementu) do kanalu
	 * @param mixed $element Obiekt klasy Feed_Atom
	 */
	public function addElement(Feed_Atom $element)
	{
		$this->elements[] = $element;
		return $this;
	}

	/**
	 * Utworzenie nowego wpisu w kanele
	 * @param mixed $array Opcjonalna tablica zawierajaca informacje o nowym wpisie (tytul, data utworzenia itp)
	 * @return mixed
	 */
	public function &createElement(array $array = array())
	{
		$this->elements[] = new Feed_Element_Atom($array);
		return $this->elements[sizeof($this->elements) -1];
	}

	/**
	 * Wygenerowanie danych XML zawierajacych informacje o kanale wraz z wpisami
	 * @return string
	 */
	public function saveXml()
	{
		$doc = new DOMDocument($this->getVersion(), $this->getEncoding());

		$root = $doc->createElement('feed');
		$root->setAttribute('xmlns', self::NS);
		$doc->appendChild($root);

		$title = $doc->createElement('title', $this->getTitle());
		$root->appendChild($title);

		$id = $doc->createElement('id', $this->getId());
		$root->appendChild($id);

		if ($this->getLink())
		{
			$link = $doc->createElement('link');
			$link->setAttribute('rel', '_self');
			$link->setAttribute('href', $this->getLink());

			$root->appendChild($link);
		}
		
		$updateTime = array();

		foreach ($this->elements as $element)
		{
			$item = $doc->createElement('entry');
			$root->appendChild($item);

			$title = $doc->createElement('title', $element->getTitle());
			$item->appendChild($title);

			$id = $doc->createElement('id', $element->getId());
			$item->appendChild($id);

			$updateTime[] = $element->getUpdated();
			$updated = $doc->createElement('updated', date(DATE_ATOM, $element->getUpdated()));
			$item->appendChild($updated);

			if ($element->getLink())
			{
				$link = $doc->createElement('link');
				$link->setAttribute('rel', 'alternate');
				$link->setAttribute('href', $element->getLink());

				$item->appendChild($link);
			}
			
			if ($element->getAuthorName())
			{
				$author = $doc->createElement('author');
				$authorName = $doc->createElement('name', $element->getAuthorName());
				$author->appendChild($authorName);
				
				if ($element->getAuthorEmail())
				{
					$authorEmail = $doc->createElement('email', $element->getAuthorEmail());
					$author->appendChild($authorEmail);
				}
				
				$item->appendChild($author);
			}

			if ($element->getSummary())
			{
				$summary = $doc->createElement('summary', $element->getSummary());
				$item->appendChild($summary);
			}
			
			if ($element->getContent())
			{
				$content = $doc->createElement('content', $element->getContent());
				$content->setAttribute('type', $element->getContentType());				
				$item->appendChild($content);
			}				
		}

		if ($updateTime) 
		{
			/**
			 * Pobranie daty ostatniego wpisu. Na tej podstawie ustalamy date ostatniej
			 * modyfikacji kanalu (znacznik <updated>)
			 */
			if (max($updateTime) > $this->getUpdated())
			{
				$this->setUpdated(max($updateTime));
			}
		}

		if ($this->getUpdated())
		{
			$updated = $doc->createElement('updated', date(DATE_ATOM, $this->getUpdated()));
			$root->appendChild($updated);
		}
		
		$doc->formatOutput = true;
		return $doc->saveXml();
	}

	public function __toString()
	{
		Core::getInstance()->output->setContentType('application/atom+xml; charset=' . $this->getEncoding());

		return $this->saveXml();
	}
}
?>