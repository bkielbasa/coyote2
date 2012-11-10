<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa Pagination (stronnicowanie)
 */
class Pagination
{
	/**
	 * Bazowy adres URL dla linkow
	 */
	protected $baseUrl;
	/**
	 * Liczba pozycji (nie stron!). Np. 200 tematow na forum
	 */
	protected $totalItems;
	/**
	 * Liczba pozycji wyswietlanych na stronie (Np. 10 tematow na forum)
	 */
	protected $itemsPerPage;
	/**
	 * Aktualna strona na ktore przechywa uzytkownik
	 */
	protected $currentPage;
	/**
	 * Aktualna pozycja (nie strona)
	 */
	protected $currentItem;
	/**
	 * Wartosc QUERY_STRING, ktora ma byc dolaczna do URL'a
	 */
	protected $queryString;
	/**
	 * Jezeli ponizsza opcja jest wlaczona do linku bedzie dolaczony aktualny QUERY STRING
	 */
	protected $enableDefaultQueryString = true;

	function __construct($baseUrl = '', $totalItems = 0, $itemsPerPage = 10, $currentItem = 0)
	{
		$this->setBaseUrl($baseUrl);
		$this->setTotalItems($totalItems);
		$this->setItemsPerPage($itemsPerPage);
		$this->setCurrentItem($currentItem);
	}

	/**
	 * @param bool
	 */
	public function setEnableDefaultQueryString($flag)
	{
		$this->enableDefaultQueryString = (bool) $flag;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isDefaultQueryStringEnabled()
	{
		return $this->enableDefaultQueryString;
	}

	/**
	 * Ustawia bazowy adres dla linkow
	 * @param string $baseUrl
	 * @return object 
	 */
	public function setBaseUrl($baseUrl)
	{
		if (function_exists('url'))
		{
			$baseUrl = url($baseUrl);
		}
		$this->baseUrl = $baseUrl;
		return $this;
	}

	/**
	 * Zwraca bazowy adres dla URL
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->baseUrl;
	}

	/**
	 * Ustawia wartosc QUERY_STRING dla URL'a
	 * @param string $queryString
	 * @return object
	 */
	public function setQueryString($queryString)
	{
		$this->queryString = $queryString;
		return $this;
	}

	/**
	 * Metoda przypisuje do pola queryString aktualna wartosc $_SERVER[QUERY_STRING]
	 */
	public function loadDefaultQueryString()
	{
		if (!$this->isDefaultQueryStringEnabled())
		{
			return;
		}

		if (empty($this->queryString))
		{
			parse_str($_SERVER['QUERY_STRING'], $this->queryString);
			unset($this->queryString['start']);

			$this->queryString = http_build_query($this->queryString);

			if ($this->queryString)
			{
				$this->queryString = '?' . $this->queryString;	
			}
		}

		return $this;
	}

	/**
	 * Zwraca wartosc QUERY_STRING ustawiona w klasie
	 * @return string
	 */
	public function getQueryString()
	{
		return $this->queryString;
	}

	/**
	 * Ustawia ilosc rekordow na stronie (np. 200 tematow na forum)
	 * @param int $totalItems
	 * @return object
	 */
	public function setTotalItems($totalItems)
	{
		$this->totalItems = $totalItems;
		return $this;
	}

	/**
	 * Ustawia ilosc rekordow na jednej stronie (np. 10 tematow)
	 * @param int $itemsPerPage
	 * @return object
	 */
	public function setItemsPerPage($itemsPerPage)
	{
		if (!$itemsPerPage)
		{
			$itemsPerPage = 10;
		}
		$this->itemsPerPage = $itemsPerPage;
		return $this;
	}

	/**
	 * Zwraca ilosc rekorow (np. 200 tematow na forum
	 */
	public function getItemsPerPage()
	{
		return $this->itemsPerPage;
	}

	/**
	 * Zwraca ilosc stron (Ilosc rekordow / Ilosc rekordow na jedna strone)
	 * @return int
	 */
	public function getTotalPages()
	{
		return ceil($this->totalItems / $this->itemsPerPage);
	}

	/**
	 * Zwraca ogolna ilosc pozycji
	 * @return int
	 */
	public function getTotalItems()
	{
		return $this->totalItems;
	}

	/**
	 * Ustawia aktualna strone (np. 2 strona tematow na forum)
	 * @return object
	 */
	public function setCurrentPage($currentPage)
	{
		$this->currentPage = $currentPage;
		return $this;
	}

	/**
	 * Zwraca numer aktualnej strony (np. 2 strona tematow na forum)
	 * @return int
	 */
	public function getCurrentPage()
	{
		if (!$this->currentPage)
		{
			$this->currentPage = 1;
		}
		return $this->currentPage;
	}

	/**
	 * Ustawia poczatkowa pozycje (np. odczytaj tematy na forum, poczawszy od setnego)
	 * @param int 
	 * @return object
	 */
	public function setCurrentItem($currentItem)
	{
		$this->currentItem = $currentItem;
		$this->currentPage = floor($currentItem / $this->itemsPerPage) + 1;
		return $this;
	}

	/**
	 * Zwraca aktualna pozycje
	 * @return int
	 */
	public function getCurrentItem()
	{
		return $this->currentItem;
	}

	/**
	 * Wyswietlanie linkow w widoku
	 * @param string $template Nazwa szablonu
	 * @return string
	 */
	public function display($template = 'default')
	{
		$this->loadDefaultQueryString();

		$baseUrl = $this->getBaseUrl() . $this->getQueryString();
		$amp =  strpos($baseUrl, '?') !== false ? '&amp;' : '?';

		$baseUrl .= $amp;

		$view = new View('pagination/' . $template);
		$view->assign(array(
				'totalPages'			=> $this->getTotalPages(),
				'url'					=> $baseUrl,
				'currentPage'			=> $this->getCurrentPage(),
				'itemsPerPage'			=> $this->getItemsPerPage(),

				'previousPage'			=> $this->getCurrentPage() > 1 ? $this->getCurrentPage() - 1 : false,
				'nextPage'				=> $this->getCurrentPage() < $this->getTotalPages() ? $this->getCurrentPage() + 1 : false,
				'firstPage'				=> $this->getCurrentPage() == 1 ? false : 1,
				'lastPage'				=> $this->getCurrentPage() >= $this->getTotalPages() ? false : $this->getTotalPages()
			)
		);

		return $view->display(false);
	}

	public function __toString()
	{
		return $this->display();
	}
}

?>