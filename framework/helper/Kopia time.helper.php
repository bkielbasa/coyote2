<?php
/**
 * @package Coyote-F
 * @version $Id: autoload.php,v 1.2 2008/04/16 14:02:21 adam Exp $
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */


class Time
{
	/**
	 * Podaj wiek (np. pliku, daty umieszczenia artykulu, jako tekst
	 * Np. 1 year, 2 month, 54 minutes
	 * @param int $now Data obecna
	 * @param int $date Data umieszczenia materialu
	 * @return string
	 */
	public static function getAge($now, $date)
	{
		$time = $now - $date;

		if ($time <= 0)
		{
			return false;
		}
		else if ($time > 31536000)
		{
			$value = round($time / 31536000);
			$age = 'year';
		}
		else if ($time > 2592000)
		{
			$value = round($time / 2592000);
			$age = 'month';
		}
		else if ($time > 604800)
		{
			$value = round($time / 604800);
			$age = 'week';
		}
		else if ($time > 86400)
		{
			$value = round($time / 86400);
			$age = 'day';
		}
		else
		{
			if ($time > 3600)
			{
				$value = round($time / 3600);
				$age = 'hour';
			}
			elseif ($time > 60)
			{
				$value = round($time / 60);
				$age = 'minute';
			}
			else 
			{
				$value = $time;
				$age = 'second';
			}
		}

		if ($value > 1)
		{
			$age = $age . 's';
		}
		return $value . ' ' . __($age);
	}

	public static function relative($timestamp)
	{
		$hour = date('H:i', $timestamp);
		$day = date('d', $timestamp);
		$diff = date('d') - $day;

		if ($diff > 0)
		{
			if ($diff == 1)
			{
				$age = 'yesterday';
			}
			elseif ($diff == 2)
			{
				$age = '2 days ago';
			}
			else
			{
				$result = date('d-m-Y H:i', $timestamp);
			}
		}
		else
		{
			$age = 'today';
		}

		if (isset($age))
		{
			$result = __($age) . ', ' . $hour;
		}

		return $result;
	}
}

?>