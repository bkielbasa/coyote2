<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Filtr usuwajacy "biale" znaki na koncu i poczatku lancucha
 */
class Filter_StringTrim implements IFilter
{
	/**
	 * Tablica znakow
	 */
	private $charList = array();

	function __construct($charList = null)
	{ 
		if ($charList !== null)
		{
			if (!is_array($charList))
			{
				$charList = func_get_args();
			}
			$this->setCharList($charList);
		}
	}

	/**
	 * Ustaw tablice znakow, ktore zostana zamienione
	 */
	public function setCharList(array $charList)
	{
		$this->charList = $charList;
		return $this;
	}

	public function filter($value)
	{
		//$value = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $value);

		if ($this->charList)
		{
			return trim($value, implode('', $this->charList));
		}
		else
		{
			return trim($value);
		}
	}
}
?>