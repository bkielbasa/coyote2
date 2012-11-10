<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

define('SYSTEM_UNIQ', uniqid());

/**
 * Prosta klasa pomiaru czasu wykonywania
 */
final class Benchmark
{
	private static $start = array(SYSTEM_UNIQ => COYOTE_START);
	private static $stop = array();

	/**
	 * Metoda dopisuje do tablicy czas rozpoczecia pomiaru 
	 * @param string $name Nazwa "testu"
	 */
	public static function start($name)
	{
		self::$start[$name] = microtime();
	}

	/**
	 * Metoda dopisuje do tablicy czas zakonczenia pomiaru 
	 * @param string $name Nazwa "testu"
	 */
	public static function stop($name)
	{
		self::$stop[$name] = microtime();
	}

	/**
	 * Bazujac na wartosci rozpoczecia i zakonczenia testu, zwraca formatowana liczbe 
	 * @param string $start Wartosc poczatkowa
	 * @param string $stop Wartosc koncowa
	 * @param int $decimal Ilosc miejsc po przecinku
	 * @return string
	 */
	private static function format($start, $stop, $decimal)
	{
		list($m, $s) = explode(' ', $start);
		list($m2, $s2) = explode(' ', $stop);

		return number_format(($m2 + $s2) - ($m + $s), $decimal);
	}

	/**
	 * Zwraca sformatowany lancuch okreslajacy czas dzialania systemu (np. 0.365 sec)
	 * @param int $decimal Ilosc miejsc po przecinku
	 * @return string
	 */
	public static function estimated($decimal = 4)
	{
		return self::format(COYOTE_START, microtime(), $decimal);
	}

	/**
	 * Metoda zwraca wartosc pomiaru
	 * @example
	 * <code>
	 * Benchmark::start('foo');
	 * // kod ...
	 * Benchmark::stop('foo');
	 * echo 'Czas wykonywania kodu: ' . Benchmark::elapsed('foo', 6); // np. 0.005655
	 * </code>
	 * @param string $name Nazwa testu 
	 * @param int $decimal Ilosc miejsc po przecinku
	 */
	public static function elapsed($name = '', $decimal = 4)
	{ 
		if (!$name)
		{
			$name = SYSTEM_UNIQ;
			/* stopujemy jezeli nie zostalo zatrzymane */
			self::stop($name);
		}
		return self::format(self::$start[$name], self::$stop[$name], $decimal);
	}

	public static function results()
	{
		return self::$stop;
	}
}


?>