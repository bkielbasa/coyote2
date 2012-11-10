<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Gzipoutput 
{
	public function compress()
	{
		@ob_start("ob_gzhandler");
	}
}
?>