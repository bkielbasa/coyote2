<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Filter_Injection implements IFilter
{
	public function filter($value)
	{
		$drop_char_match = array('^', '$', ';', '#', '&', '(', ')', '`', '\'', '|', ',', '?', '%', '~', '[', ']', '{', '}', ':', '\\', '=', '\'', '!', '"', '%20', "'");

		$value = str_replace($drop_char_match, '', $value);
		$value = trim(htmlspecialchars(strip_tags($value)));

		return $value;	
	}
}

?>