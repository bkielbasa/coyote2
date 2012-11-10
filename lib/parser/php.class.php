<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Parser_Php implements Parser_Interface
{
	public function parse(&$content, Parser_Config_Interface &$config)
	{
		$content = Text::evalCode($content);
	}
}
?>