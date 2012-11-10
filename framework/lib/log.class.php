<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Nowy typ bledu - E_DEBUG dla paska narzedziowego DEBUG, we frameworku
 */
define('E_DEBUG', 65535);

/**
 * Klasa obslugi logowania bledow i ostrzezen
 */
final class Log
{
	/**
	 * Tablica ostrzezen, wiadomosci i wskazowek
	 */
	public static $message = array();
	/**
	 * Typy bledow 
	 */
	public static $type = array(
			E_DEBUG					=> 'debug', 

			E_WARNING				=> 'warning',
			E_NOTICE				=> 'notice',
			E_USER_ERROR			=> 'user error',
			E_USER_WARNING			=> 'user warning',
			E_USER_NOTICE			=> 'user notice',
			E_STRICT				=> 'strict'
	);
	/**
	 * Typy bledow, ktore beda zapisywane w dzienniku
	 */
	private static $write = array(E_ERROR);
			

	/**
	 * Metoda dodaje nowy komunikat do kolejki
	 * @param string $message Komunikat zapisany do loga
	 * @param int $type Typ wiadomosci
	 * @static
	 */
	public static function add($message, $type = E_ERROR)
	{
		$info = array($type, $message, time());

		if (defined('DEBUG') && DEBUG)
		{ 
			array_push($info, Benchmark::estimated());
		}
		else
		{
			array_push($info, '0000');
		}
		self::$message[] = $info;
		if (in_array($type, self::$write))
		{
			self::write($info);
		}
	}

	/**
	 * Dodaje typy bledow, ktore beda zapisywane w dzienniku
	 * @example
	 * <code>
	 * Log::setLogType(E_WARNING);
	 * </code>
	 */
	public static function setLogType($type)
	{
		array_push(self::$write, $type);
	}

	/** 
	 * Metoda zapisuje komunikaty do dziennika bledow. 
	 * Metoda wywolywana jest w momencie wystapienia powaznego bledu
	 */
	private static function write(&$info)
	{
		if (!is_dir(Config::getBasePath() . 'log'))
		{
			if (!mkdir(Config::getBasePath() . 'log'))
			{
				self::notify('Could not create /log directory');
				return;
			}
		}
		@list($type, $message, $time, $estimated) = $info;

		if (!is_writeable(Config::getBasePath() . 'log'))
		{
			self::notify('Directory /log is not writeable. Error: ' . $message);
			return;
		}		
		/**
		 * @todo Zastapic to funkcja error_log()
		 */
		file_put_contents(Config::getBasePath() . 'log/error.log', sprintf("%s: %s\n", date('d-m-Y H:i:s', $time), $message), FILE_APPEND | LOCK_EX);
	}
	
	/**
	 * Powiadomienie o niemoznosci zapisania informacji do dziennika.
	 * E-mail wysylany jest do administratora systemu
	 * @param string $message Wiadomosc, ktora zostanie wyslana na e-mail
	 */
	private static function notify($message)
	{
		error_log($message);

		if (Config::getItem('site.email'))
		{
			$headers = "Subject: Coyote Framework error!\nFrom: Coyote Framework <coyote@mail>";
			error_log($message, 1, Config::getItem('site.email'), $headers);
		}
	}
}
?>