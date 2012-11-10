<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa zawiera metode zwracajaca poprawna forme jezykowa (deklinacja)
 * @example
 * echo Declination(10, array('sekunda', 'sekundy', 'sekund')); 
 */
class Declination
{
	public static function __($value, $declination)
	{
		if ($value == 1)
		{
			return $declination[0];
		}
		else
		{
			$unit = $value % 10;
			$decimal = round(($value % 100) / 10);
			if (($unit == 2 || $unit == 3 || $unit == 4) && ($decimal != 1))
			{
				return $declination[1];
			}
			else
			{
				return $declination[2];
			}
		}
	}
}
?>