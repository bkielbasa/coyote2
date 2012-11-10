<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Document_Controller extends Page_Controller
{
	function main()
	{
		/**
		 * Pobranie dokumentow potomnych, do ktorych linki
		 * bedzie mozna wyswietlic w szablonie
		 */
		$this->children = array();		
		if ($this->module->main('enableMenu', $this->page->getId()))
		{
			$this->children = $this->getChildren();
		}
		
		$content = parent::main();				
		
		if (is_string($content))
		{
			echo $content;
		}
		else
		{
			return $content;
		}
	}
}
?>