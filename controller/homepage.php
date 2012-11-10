<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Homepage_Controller extends Page_Controller
{
	function main()
	{
		$content = parent::main();

		Breadcrumb::disable();
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