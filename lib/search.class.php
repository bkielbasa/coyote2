<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Search extends Context 
{
	private $instance;

	function __construct(Search_Interface $instance = null)
	{
		if ($instance !== null)
		{
			$this->instance = $instance;
		}
		else
		{
			$search = &$this->getModel('search');
			$result = $search->getEnabledSearch();
	
			/*
			 * Moze dojsc do sytuacji, w ktorej indeksowanie i wyszukiwanie jest
			 * wylaczone. 
			 */
			if ($result)
			{
				$className = 'Search_' . $result['search_class'];
				$this->instance = new $className;
			}
		}
	}
	
	public function find($queryString = '', $start = null, $limit = null)
	{
		$this->getModel('search');		
		$top10 = new Search_Top10_Model;

		if (($start == null || $start == 0) && $this->getStartPage() == 0)
		{
			$top10->update($queryString ? $queryString : $this->getQueryString());
		}
		
		if ($this->instance instanceof Search_Interface)
		{
			return $this->instance->find($queryString, $start, $limit);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Przeciazanie metod. W rzeczywistosci wywolujemy metode z wlasciwej
	 * klasy mechanizmu wyszukiwania w zaleznosci jaki mechanizm zostal wlaczony
	 * w panelu administracyjnym
	 */
	public function __call($method, $args)
	{
		if (!($this->instance instanceof Search_Interface))
		{
			return;
		}
		if (!method_exists($this->instance, $method))
		{
			throw new Exception("Metoda $method nie istnieje w klasie " . get_class($this->instance));
		}
		
		return call_user_func_array(array(&$this->instance, $method), $args);
	}
	
	/**
	 * Metoda przeprowadza proces indeksacji stron, ktore znajduja sie w kolejce
	 * do indeksacji
	 */
	public function buildIndex()
	{
		if ($this->instance instanceof Search_Interface)
		{
			$pageIds = $this->db->select('page_id')->order('timestamp DESC')->get('search_queue')->fetchCol();

			$this->addDocuments($pageIds);
			$this->db->delete('search_queue');
		}
	}
	
	/**
	 * Metoda wywolywana w skutek wystapienia danego triggera.
	 * Dodaje strone do kolejki indeksowania jezeli zostala dodana lub usuniecia
	 * z systemu. Jezeli strona zostala usunieta - system usunie ja z indeksu wyszukiwarki.
	 * W zwiazku z tym, ze metoda jest wywolywana na skutek wystapienia triggera
	 * application.onPageSubmitComplete lub application.onPageDeleteComplete,
	 * w parametrze $data dostarczona jest albo - tablica z jednym elementem (obiekt Page)
	 * - albo ID strony do usuniecia
	 * @param mixed|int
	 * @static
	 */
	public static function buildQueue($pageId)
	{
		$pageId = is_array($pageId) ? $pageId[0]->getId() : $pageId;
		
		$load = &Load::loadClass('load');
		$load->model('search');
		
		$queue = new Search_Queue_Model;
		$queue->addToQueue($pageId);				
	}
}
?>