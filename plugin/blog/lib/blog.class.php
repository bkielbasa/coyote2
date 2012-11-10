<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Blog extends Plugin
{
	public function display()
	{
		if (!(@$this->page instanceof Page))
		{
			return;
		}

		$pageId = $this->page->getId();
		$moduleName = $this->module->getName($this->page->getModuleId());

		if (!$this->module->$moduleName('enableBlog', $pageId))
		{
			return false;
		}

		$blog = &$this->getModel('blog');
		Sort::setDefaultSort('page_time', Sort::DESC);

		$start = max(0, (int) $this->get['start']);

		$result = $blog->getChildren($pageId, Sort::getSortAsSQL(), $start, 5);
		$totalItems = $blog->count($pageId);

		$pagination = new Pagination('', $totalItems, 5, $start);

		echo new View('_partialBlog', array(
				'blog'			=> $result->fetchAll(),
				'pagination'    => $pagination
			)
		);
	}
}
?>