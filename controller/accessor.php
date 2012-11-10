<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Accessor extends Controller
{
	function main()
	{
		$url = $this->page->getContent();
		if (!preg_match('#^[\w]+?://.*?#i', $url))
		{
			$url = Url::__($url);
		}

		$this->output->setStatusCode(301);
		$this->redirect($url);
	}
}
?>