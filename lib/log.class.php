<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Nowy typ bledu - E_DEBUG dla paska narzedziowego DEBUG, we frameworku
 */
define('E_DEBUG', 65535);

define('E_UCP_LOGIN', 65534);
define('E_UCP_LOGIN_FAILED', 65533);
define('E_ACP_LOGIN', 65532);
define('E_ACP_LOGIN_FAILED', 65531);
define('E_REGISTER', 65530);
define('E_CONFIRM', 65529);
define('E_USER_UPDATE', 65520);
define('E_BAN_SUBMIT', 65519);

define('E_PAGE_SUBMIT', 65528);
define('E_PAGE_DELETE', 65527);
define('E_PAGE_MOVE', 65526);
define('E_PAGE_COPY', 65525);
define('E_PAGE_RESTORE', 65524);
define('E_PAGE_PURGE', 65523);

define('E_REPORT_CLOSE', 65522);

define('E_PM_SUBMIT', 65521);
define('E_CRON', 65517);


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
			E_ERROR					=> 'error',

			E_WARNING				=> 'warning',
			E_NOTICE				=> 'notice',
			E_USER_ERROR			=> 'user error',
			E_USER_WARNING			=> 'user warning',
			E_USER_NOTICE			=> 'user notice',
			E_STRICT				=> 'strict',
			E_CRON					=> 'cron'
	);
	/**
	 * Typy bledow, ktore beda zapisywane w dzienniku
	 */
	private static $write = array(
			E_ERROR,
			E_UCP_LOGIN,
			E_UCP_LOGIN_FAILED,
			E_ACP_LOGIN,
			E_ACP_LOGIN_FAILED,
			E_REGISTER,
			E_CONFIRM,
			E_PAGE_SUBMIT,
			E_PAGE_DELETE,
			E_PAGE_MOVE,
			E_PAGE_COPY,
			E_PAGE_RESTORE,
			E_PAGE_PURGE,
			E_REPORT_CLOSE,
			E_PM_SUBMIT,
			E_USER_UPDATE,
			E_BAN_SUBMIT,
			E_CRON
	);


	/**
	 * Metoda dodaje nowy komunikat do kolejki
	 * @param string $message Komunikat zapisany do loga
	 * @param int $type Typ wiadomosci
	 * @static
	 */
	public static function add($message, $type = E_ERROR, $pageId = null)
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

		if (is_string($type) || in_array($type, self::$write))
		{
			self::write($message, $type, $pageId);
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

	private static function writeToFile($message)
	{
		if (!is_dir(Config::getBasePath() . 'log'))
		{
			if (!mkdir(Config::getBasePath() . 'log'))
			{
				self::notify('Could not create /log directory');
				return;
			}
		}

		if (!is_writeable(Config::getBasePath() . 'log'))
		{
			self::notify('Directory /log is not writeable. Error: ' . $message);
			return;
		}
		/**
		 * @todo Zastapic to funkcja error_log()
		 */
		file_put_contents(Config::getBasePath() . 'log/error.log', sprintf("%s: %s\n", date('d-m-Y H:i:s', time()), $message), FILE_APPEND | LOCK_EX);
	}

	/**
	 * Metoda zapisuje komunikaty do dziennika bledow.
	 * Metoda wywolywana jest w momencie wystapienia powaznego bledu
	 */
	private static function write($message, $type, $pageId)
	{
		$message = (string)$message;

		try
		{
			$core = &Core::getInstance();

			if (isset($core->db))
			{
				$input = &Load::loadClass('input');

				$core->db->insert('log', array(
					'log_user'			=> User::$id,
					'log_page'			=> $pageId,
					'log_time'			=> time(),
					'log_type'			=> $type,
					'log_message'		=> htmlspecialchars($message),
					'log_ip'			=> $input->getIp()
					)
				);
			}
			else
			{
				self::writeToFile($message);
			}
		}
		catch (Exception $e)
		{
			self::writeToFile($e->getMessage());
			self::writeToFile($message);
		}
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