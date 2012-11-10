<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Filtr usuwajacy dane znaki w tekscie
 */
class Filter_Replace implements IFilter
{
	/**
	 * Tablica znakow
	 */
	private $pattern = array();

	function __construct($pattern)
	{
		if (func_num_args() > 1)
		{
			$pattern = func_get_args();
		}
		elseif (is_string($pattern))
		{
			$pattern = str_split($pattern);
		}

		$this->setPattern($pattern);
	}

	/**
	 * Ustaw tablice znakow, ktore zostana zamienione
	 */
	public function setPattern(array $pattern)
	{
		$this->pattern = $pattern;
	}

	public function filter($value)
	{
		return str_replace($this->pattern, '', $value);
	}
}
?>