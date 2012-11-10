<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Filtr uzywany przy zapisie tagow do bazy danych
 */
class Filter_Tag implements IFilter
{
	public function filter($value)
	{
		$value = Text::toLower(strip_tags($value));

		$filter = new Filter_Replace(str_split('"<>^$;&()`\|?%~[]{}:\=!"\'/'));
		$arr = explode(' ', preg_replace('#\s+#', ' ', str_replace(',', ' ', $filter->filter($value))));

		foreach ($arr as $index => $element)
		{
			if (Text::length($element) > 25)
			{
				unset($arr[$index]);
			}
		}

		return implode(' ', $arr);
	}
}
?>