<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class News
{
	public static function plain($text)
	{
		return preg_replace("#{{(Image|File):(.*?)(\|(.*))*}}#i", '', $text);
	}
}
?>