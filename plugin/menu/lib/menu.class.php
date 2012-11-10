<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Menu extends Plugin
{
	public function display()
	{
		if (!$this->getItem())
		{
			return false;
		}

		$menu = &$this->getModel('menu');

		if (!$result = $menu->find($this->getItem())->fetchAssoc())
		{
			return false;
		}
		if ($result['menu_auth'])
		{
			if (!Auth::get($result['menu_auth']))
			{
				return false;
			}
		}
		$menuTag = &$result['menu_tag'];
		$xhtml = '';

		if ($menuTag)
		{
			$xhtml = Html::tag($menuTag, true, unserialize($result['menu_attributes']));
		}
		$parts = array();
		$location = null;

		if (@($this->page) && $this->page instanceof Page)
		{
			$location = $this->page->getLocation();
			$parts = explode('/', $location);
		}

		$items = array();

		$query = $menu->item->getItems($this->getItem());
		$items = $query->fetchAll();

		if ($location !== null)
		{
			/*
			 * Odwracamy elementy w menu, tak, aby petla analizowala najpierw
			 * elementy, ktore sa najbardziej zaglebione w wielopoziomowym menu
			 */
			$items = array_reverse($items, true);

			/*
			 * Petla ma za zadanie analizowac kazdy element adresu i porownywac
			 * z dana pozycja celem wykrycia, czy dana pozycja powinna byc aktywna.
			 *
			 * Np. jezeli aktualna strona, ktora przeglada user, to Foo/Bar/A, to
			 * aktywna pozycja w menu powinno byc zarowno Foo, jak i Bar oraz A (jezeli
			 * oczywiscie istnieje taka pozycja w menu)
			 */
			foreach ($parts as $index => $part)
			{
				$path = implode('/', array_slice($parts, 0, $index + 1));
				$parent = null;

				foreach ($items as $key => $item)
				{
					if ($parent !== null && $parent == $item['item_id'])
					{
						if ($item['item_parent'])
						{
							$parent = $item['item_parent'];
						}
						else
						{
							$parent = null;
						}
						$items[$key]['focus'] = true;
					}
					elseif (strcasecmp($item['item_path'], $path) == 0)
					{
						if ($item['item_parent'])
						{
							// jezeli uznajemy dana pozycje za aktywna,
							// nalezy rowniez "aktywowac" pozycje nadrzedne
							$parent = $item['item_parent'];
						}
						$items[$key]['focus'] = true;
					}
				}
			}

			$items = array_reverse($items, true);
		}

		foreach ($items as $index => $item)
		{
			if ($item['item_auth'])
			{
				if (!Auth::get($item['item_auth']))
				{
					continue;
				}
			}
			$depth = $item['item_depth'];
			$nextDepth = isset($items[$index + 1]) ? $items[$index + 1]['item_depth'] : 0;

			$item['item_name'] = htmlspecialchars_decode($item['item_name']);
			if (strpos($item['item_name'], '<?php') !== false)
			{
				$item['item_name'] = Text::evalCode($item['item_name']);
			}
			if (strpos($item['item_path'], '<?php') !== false)
			{
				$item['item_path'] = Text::evalCode($item['item_path']);
			}
			$attributes = unserialize($item['item_attributes']);

			if ($item['item_description'])
			{
				$attributes['title'] = $item['item_description'];
			}

			if ($item['item_tag'])
			{
				if (isset($item['focus']) && $item['item_focus'])
				{
					if (!isset($attributes['class']))
					{
						$attributes['class'] = $item['item_focus'];
					}
					else
					{
						$attributes['class'] .= ' ' . $item['item_focus'];
					}
				}

				$itemTag = &$item['item_tag'];
				$anchor = '<a';

				if (isset($attributes['title']))
				{
					$anchor .= ' title="' . $attributes['title'] . '"';
					unset($attributes['title']);
				}

				if (isset($attributes['accesskey']))
				{
					$anchor .= ' accesskey="' . $attributes['accesskey'] . '"';
					unset($attributes['accesskey']);
				}

				if ($item['item_path'])
				{
					$anchor .= ' href="' . url($item['item_path']) . '"';
				}
				$anchor .= '>' . $item['item_name'];
				$anchor .= '</a>';

				$attributes = Html::attributes($attributes);
				$xhtml .= "<$itemTag {$attributes}>\n\t{$anchor}\n\t";

				if ($nextDepth > $depth)
				{
					$xhtml .= "\t<$menuTag>";
				}
				else
				{
					$xhtml .= "</$itemTag>";
				}

				if ($nextDepth < $depth)
				{
					while ($nextDepth < $depth)
					{
						$xhtml .= "\n</$menuTag></$itemTag>";
						--$depth;
					}
				}
			}
			else
			{
				$xhtml .= Html::a(url($item['item_path']), $item['item_name'], $attributes);
			}

			if ($result['menu_separator'])
			{
				if (isset($items[$index + 1]))
				{
					$xhtml .= $result['menu_separator'];
				}
			}
		}

		if ($menuTag)
		{
			$xhtml .= "</$menuTag>";
		}

		return $xhtml;
	}
}
?>