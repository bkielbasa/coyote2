<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Snippet powoduje wyswietlenie "spisu tresci". 
 * Wyswietla liste dokumentow znajdujacych sie w danej kategorii. 
 * Jezeli snippet zostanie uzyty w dokumencie /Foo, snippet spowoduje wyswietlenie
 * listy dokumentow nalezacych do strony /Foo, czyli np. /Foo/Bar, /Foo/Test itd.
 * Lista w formacie <dl><dd><dt>
 */
class Snippet_Content extends Snippet
{
	/**
	 * Maksymalne "zaglebienie" dokumentow w stosunku do biezacego dokumentu, ktore
	 * zostana wyswietlone 
	 */
	public $depth = 32524;

	/**
	 * Metoda wyswietlajaca snippet.
	 */
	public function display()
	{
		$parentId = $this->page->getId() ? $this->page->getId() : null;
		$page = &$this->load->model('page');

		$baseDepth = $page->page->getDepth();
		$depth = $baseDepth;

		if ($parentId !== null)
		{
			$depth++;
		}
		$count[$depth] = 0;

		$result = $page->getBranchList($parentId);

		$xhtml = '<dl>';
		foreach ($result as $index => $row)
		{
			if ($row['page_delete'] || !$row['page_publish'])
			{
				continue;
			}
			if ($row['page_depth'] - $baseDepth > $this->depth)
			{
				continue;
			}

			if ($row['page_depth'] > $depth)
			{
				$xhtml .= "<dd>\n<dl>\n";
				$count[$row['page_depth']] = 0;
			}
			elseif ($row['page_depth'] < $depth)
			{
				while ($row['page_depth'] < $depth)
				{
					unset($count[$depth]);
					$xhtml .= "</dl>\n</dd>";
					--$depth;					
				}
			}

			++$count[$row['page_depth']];
			$xhtml .= '<dt><a href="' . url($row['location_text']) . '">' . implode('.', $count) . '. ' . $row['page_subject'] . '</a></dt>';

			$depth = $row['page_depth'];
		}
		$xhtml .= '</dl>';
	
		echo $xhtml;
	}
}
?>