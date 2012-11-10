<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Filter_Path implements IFilter
{
	public function filter($value)
	{
		$encoder = new Path;
		return $encoder->encode($value);
	}
}
?>