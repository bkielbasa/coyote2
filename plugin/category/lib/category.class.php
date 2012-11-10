<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Category extends Plugin
{
	public function display()
	{
		if (!(@$this->page instanceof Page))
		{
			return;
		}

		$pageId = $this->page->getId();
		$moduleName = $this->module->getName($this->page->getModuleId());

		if (!$this->module->$moduleName('enableCategory', $pageId))
		{
			return false;
		}

		$page = &$this->getModel('page');
		$query = $page->getCategoriesByText($this->page->getTextId());

		if ($query !== false && count($query))
		{
			$category = array();

			foreach ($query as $row)
			{
				$path = explode('/', $row['location_text']);
				$row['page_subject'] = Text::humanize($row['page_subject']);

				if (sizeof($path) < 2)
				{
					$category[] = Html::a(url($row['location_text']), $row['page_subject'], array('title' => 'Kategoria macierzysta: ' . $row['page_subject']));
				}
				else
				{
					$value = Html::a(url($path[0]), Text::humanize($path[0]), array('title' => 'Przejdź do kategorii głównej: ' . Text::humanize($path[0])));
					$value .= ' <span><em>»</em></span> ';
					$value .= Html::a(url($row['location_text']), '<strong>' . $row['page_subject'] . '</strong>', array('title' => 'Kategoria macierzysta: ' . $row['page_subject']));

					$category[] = $value;
				}
			}

			echo '<div id="page-category"><strong>Kategoria:</strong> ' . implode(', ', $category) . '</div>';
		}
	}
}
?>