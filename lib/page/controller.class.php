<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

abstract class Page_Controller extends Controller
{
	public $parents = array();
	public $children = array();
	public $categories = array();

	public function __start()
	{
		if (@$this->page instanceof Page && !$this->page->isAllowed())
		{
			throw new Error(403, 'Nie masz uprawnień do odwiedzenia tej strony');
		}
	}

	protected function main()
	{
		if ($this->page->getTemplate())
		{
			$view = new View($this->page->getTemplate());
		}
		else
		{
			$view = $this->page->getContent();
		}

		if (file_exists($this->getStylesheet()))
		{
			$this->output->addStylesheet(Url::__($this->getStylesheet()));
		}

		if ($this->page->getMetaTitle())
		{
			$this->output->setTitle(sprintf('%s :: %s', $this->page->getMetaTitle(), Config::getItem('site.title')));
		}
		else
		{
			$this->output->setTitle(sprintf('%s :: %s', ($this->page->getTitle() ? $this->page->getTitle() : $this->page->getSubject()), Config::getItem('site.title')));
		}
		if ($this->page->getMetaKeywords())
		{
			$this->output->setMeta('keywords', $this->page->getMetaKeywords());
		}
		if ($this->page->getMetaDescription())
		{
			$this->output->setMeta('description', $this->page->getMetaDescription());
		}
		else
		{
			$this->output->setMeta('description', Text::limit(Text::plain($this->page->getContent(false)), 150));
		}
		if ($this->page->getContentType())
		{
			$this->output->setContentType($this->page->getContentType());
		}

		foreach ($this->getParents() as $row)
		{
			Breadcrumb::add(url($row['location_text']), $row['page_subject']);
		}
		Breadcrumb::add(url($this->page->getLocation()), $this->page->getSubject());

		Trigger::call('application.onPageDisplay');
		return $view;
	}

	public function getChildren()
	{
		if ($this->page->getChildren())
		{
			if (!$this->children)
			{
				$page = &$this->getModel('page');
				$query = $page->getChildren($this->page->getId());

				$this->children = $query->fetchAll();
			}
		}

		return $this->children;
	}

	public function getParents()
	{
		if ($this->page->getDepth() > 0)
		{
			if (!$this->parents)
			{
				$page = &$this->getModel('page');
				$this->parents = $page->getParents($this->page->getId())->fetchAll();
			}
		}

		return $this->parents;
	}

	public function getCategories()
	{
		if (!$this->categories)
		{
			$page = &$this->getModel('page');
			$query = $page->getCategoriesByText($this->page->getTextId());

			if (!$query || !count($query))
			{
				$this->categories = true;
			}
			else
			{
				$this->categories = $query->fetchAll();
			}
		}

		return $this->categories === true ? array() : $this->categories;
	}

	protected function getStylesheet()
	{
		return 'store/css/page-' . $this->page->getId() . '.css';
	}
}
?>