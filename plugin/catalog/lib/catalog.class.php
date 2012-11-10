<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Catalog extends Plugin
{
	public function display()
	{
		if (!(@$this->page instanceof Page))
		{
			return;
		}
		
		$pageId = $this->page->getId();
		$moduleName = $this->module->getName($this->page->getModuleId());

		if (!$this->module->$moduleName('enableCatalog', $pageId))
		{
			return false;
		}
		
		$catalog = &$this->getModel('catalog');
		Sort::setDefaultSort('page_edit_time', Sort::DESC);
		
		$start = max(0, (int) $this->get['start']);
		
		$result = $catalog->getChildren($pageId, Sort::getSortAsSQL(), $start, 10);
		$totalItems = $catalog->getFoundRows();
		
		$currentPage = floor((int) $this->get['start'] / 10) + 1;
		$totalPages = ceil($totalItems / 10);
		
		$baseUrl = $this->page->getLocation();
		$amp =  strpos($baseUrl, '?') !== false ? '&amp;' : '?';

		$baseUrl .= $amp;
		
		echo new View('_partialCatalog', array(
			'totalPages'		=> $totalPages,
			'currentPage'		=> $currentPage,
			'url'				=> $baseUrl,
		
			'previousPage'		=> $currentPage > 1 ? $currentPage - 1 : false,
			'nextPage'			=> $currentPage < $totalPages ? $currentPage + 1 : false,
			'firstPage'			=> $currentPage == 1 ? false : 1,
			'lastPage'			=> $currentPage >= $totalPages ? false : $totalPages,
		
			'children'			=> $result->fetchAll(),
			'category'			=> $catalog->getCategories($pageId)->fetchAll()
			)
		);
	}
}
?>