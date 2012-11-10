<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Time
{
	const YEAR		= 31556926;
	const MONTH		= 2629744;
	const WEEK		= 604800;
	const DAY		= 86400;
	const HOUR		= 3600;
	const MINUTE	= 60;

	/**
	 * Podaj wiek (np. pliku, daty umieszczenia artykulu, jako tekst
	 * Np. 1 year lub 2 month lub 54 minutes
	 * @param int $now Data obecna (timestamp)
	 * @param int $time Data umieszczenia materialu (timestamp)
	 * @return string
	 * @deprecated
	 */
	public static function getAge($now, $date)
	{
		return self::diff($now, $date);
	}

	/**
	 * Metoda zwraca roznice wartosci typu timestamp w sekundach
	 * @param int $remote
	 * @param int $local
	 * @return int
	 */
	private static function getTimeSpan($remote, $local = null)
	{
		if ($local === null)
		{
			$local = time();
		}

		return abs($local - $remote);
	}

	/**
	 * Zwraca roznice lat pomiedzy dwoma datami typu timestamp
	 * @param int $remote 
	 * @param int $local Wartosc null oznacza obecna date i czas
	 * @return int
	 */
	public static function diffYear($remote, $local = null)
	{		
		return (int) round(self::getTimeSpan($remote, $local) / self::YEAR);
	}

	/**
	 * Zwraca roznice miesiecy pomiedzy dwoma datami typu timestamp
	 * @param int $remote 
	 * @param int $local Wartosc null oznacza obecna date i czas
	 * @return int
	 */
	public static function diffMonth($remote, $local = null)
	{
		return (int) round(self::getTimeSpan($remote, $local) / self::MONTH);
	}

	/**
	 * Zwraca roznice tygodni pomiedzy dwoma datami typu timestamp
	 * @param int $remote 
	 * @param int $local Wartosc null oznacza obecna date i czas
	 * @return int
	 */
	public static function diffWeek($remote, $local = null)
	{
		return (int) round(self::getTimeSpan($remote, $local) / self::WEEK);
	}

	/**
	 * Zwraca roznice dni pomiedzy dwoma datami typu timestamp
	 * @param int $remote 
	 * @param int $local Wartosc null oznacza obecna date i czas
	 * @return int
	 */
	public static function diffDay($remote, $local = null)
	{
		return (int) round(self::getTimeSpan($remote, $local) / self::DAY);
	}

	/**
	 * Zwraca roznice godzin pomiedzy dwoma datami typu timestamp
	 * @param int $remote 
	 * @param int $local Wartosc null oznacza obecna date i czas
	 * @return int
	 */
	public static function diffHour($remote, $local = null)
	{
		return (int) round(self::getTimeSpan($remote, $local) / self::HOUR);
	}

	/**
	 * Zwraca roznice minut pomiedzy dwoma datami typu timestamp
	 * @param int $remote 
	 * @param int $local Wartosc null oznacza obecna date i czas
	 * @return int
	 */
	public static function diffMinute($remote, $local = null)
	{
		return (int) round(self::getTimeSpan($remote, $local) / self::MINUTE);
	}

	/**
	 * Zwraca roznice sekund pomiedzy dwoma datami typu timestamp
	 * @param int $remote 
	 * @param int $local Wartosc null oznacza obecna date i czas
	 * @return int
	 */
	public static function diffSecond($remote, $local = null)
	{
		return (int) self::getTimeSpan($remote, $local);
	}

	/**
	 * Zwraca roznie pomiedzy dwoma zmiennymi timestamp w postaci tekstowej (np. 1 year, 2 months etc)
	 * Np. 1 year lub 2 month lub 54 minutes
	 * @param int $remote Data obecna (timestamp)
	 * @param int $local 
	 * @return string
	 */
	public static function diff($remote, $local = null)
	{
		$timespan = self::getTimeSpan($remote, $local);

		if ($timespan >= self::YEAR)
		{
			$value = (int) round($timespan / self::YEAR);
			$string = 'year';
		}
		else if ($timespan >= self::MONTH)
		{
			$value = (int) round($timespan / self::MONTH);
			$string = 'month';
		}
		else if ($timespan >= self::WEEK)
		{
			$value = (int) round($timespan / self::WEEK);
			$string = 'week';
		}
		else if ($timespan > self::DAY)
		{
			$value = (int) round($timespan / self::DAY);
			$string = 'day';
		}
		else if ($timespan > self::HOUR)
		{
			$value = (int) round($timespan / self::HOUR);
			$string = 'hour';
		}
		else if ($timespan > 60)
		{
			$value = (int) round($timespan / 60);
			$string = 'minute';
		}
		else 
		{
			$value = $timespan;
			$string = 'second';
		}		

		if ($value > 1)
		{
			$string = $string . 's';
		}

		return $value . ' ' . __($string);
	}

	/**
	 * Zwraca roznice czasu w czytelnej postaci
	 * Np. dzisiaj, 13:23, wczoraj, 12:00, 2 dni, 14:45
	 * @param int $remote Czas edycji tekstu, pliku itp
	 * @return string
	 */
	public static function span($remote)
	{
		$time = date('H:i', $remote);
		$timestamp = self::diffDay($remote);

		if ($timestamp >= 1)
		{
			if ($timestamp == 1)
			{
				$string = __('yesterday');				
			}
			else
			{
				$string = self::diff($remote);
			}			
		}
		else
		{
			$string = 'today';
		}

		return __($string) . ', ' . $time;
	}

	/**
	 * Formatuje date w postaci TIMESTAMP do postaci czytelnej dla uzytkownika
	 * Przy okazji formatowania daty, realizuje tlumaczenie na aktualnie wybrany jezyk.
	 * Np. January => Styczeń
	 * UWAGA! Metoda korzysta z funkcji strftime(), ktora do prawidlowego wyswietlania
	 * daty, musi miec ustawiona prawidlowa lokalizacja
	 * @param int $tinestamp Data i czas w postacii timestamp
	 * @param string $format Format daty (zgodny z funkcja strftime())
	 * @return string
	 */
	public static function format($timestamp = null, $format = '%d-%m-%Y %H:%M')
	{
		if ($timestamp === null)
		{
			$timestamp = time();
		}

		return strftime($format, $timestamp);
	}

}

?>