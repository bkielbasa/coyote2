<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Feed_Rss extends Feed_Abstract
{
	protected $rssVersion = '2.0';
	protected $title;
	protected $link;
	protected $description;

	/**
	 * Ustawienie wersji RSS (domyslnie 2.0)
	 * @param string $rssVersion Wersja RSS 
	 */
	public function setRssVersion($rssVersion)
	{
		$this->rssVersion = $rssVersion;
		return $this;
	}

	/**
	 * Zwraca wersje RSS
	 * @return string
	 */
	public function getRssVersion()
	{
		return $this->rssVersion;
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
	 * Ustawienie opisu dla kanalu
	 * @param string $description Opis dla kanalu
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * Zwraca opis kanalu
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Dodanie wpisu (elementu) do kanalu
	 * @param mixed $element Obiekt klasy Feed_Atom
	 */
	public function addElement($element)
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
		$this->elements[] = new Feed_Element_Rss($array);
		return $this->elements[sizeof($this->elements) -1];
	}

	/**
	 * Parsownie pliku XML w formacie rss 2.0
	 * @param string $xml Dane w formacie xml (rss 2.0)
	 */
	public function loadXml($xml)
	{
		$doc = DOMDocument::loadXml($xml);

		$this->setTitle($doc->getElementsByTagName('title')->item(0)->nodeValue);
		$this->setLink($doc->getElementsByTagName('link')->item(0)->nodeValue);		
		$this->setDescription($doc->getElementsByTagName('description')->item(0)->nodeValue);	

		foreach ($doc->getElementsByTagName('item') as $entries)
		{
			$entry = array();

			foreach ($entries->getElementsByTagName('*') as $element)
			{
				$entry[$element->nodeName] = $element->nodeValue;
			}
			
			$this->addElement(Feed_Element_Rss::importArray($entry));
		}
	}

	/**
	 * Wygenerowanie danych XML zawierajacych informacje o kanale wraz z wpisami
	 * @return string
	 */
	public function saveXml()
	{
		$doc = new DOMDocument($this->getVersion(), $this->getEncoding());

		$root = $doc->createElement('rss');
		$root->setAttribute('version', $this->getRssVersion());
		$doc->appendChild($root);

		$channel = $doc->createElement('channel');
		$root->appendChild($channel);

		$title = $doc->createElement('title', $this->getTitle());
		$channel->appendChild($title);

		$id = $doc->createElement('link', $this->getLink());
		$channel->appendChild($id);

		$id = $doc->createElement('description', htmlspecialchars($this->getDescription()));
		$channel->appendChild($id);

		foreach ($this->elements as $element)
		{
			$item = $doc->createElement('item');
			$channel->appendChild($item);

			$title = $doc->createElement('title', $element->getTitle());
			$item->appendChild($title);

			$pubDate = $doc->createElement('pubDate', date('Y-m-d H:i:s', $element->getPubDate()));
			$item->appendChild($pubDate);
			
			$link = $doc->createElement('link', $element->getLink());
			$item->appendChild($link);			

			if ($element->getDescription())
			{
				$summary = $doc->createElement('description', htmlspecialchars($element->getDescription()));
				$item->appendChild($summary);
			}
		}
		
		$doc->formatOutput = true;
		return $doc->saveXml();
	}

	public function __toString()
	{
		Core::getInstance()->output->setContentType('application/rss+xml; charset=' . $this->getEncoding());

		return $this->saveXml();
	}
}
?>