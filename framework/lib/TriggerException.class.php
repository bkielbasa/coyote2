<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa obslugi wyjatkow
 */
class TriggerException extends Exception
{
	public function __construct($message, $code = 0)
	{
		parent::__construct($message, $code);

		/**
		 * Obsluga bledow przez framework. 
		 * Polega na logowaniu warningow oraz bledow, ktore moga byc wyswietlane
		 * na pasku narzedziowym DEBUG we frameworku.
		 * Jezeli chcesz wylaczyc obsluge bledow przez framework, usun te linie
		 */
		set_error_handler(array(&$this, 'errorHandler'));
		set_exception_handler(array(&$this, 'exceptionHandler'));		
	}

	public function exceptionHandler($e)
	{
		Log::add($e->getMessage());
		header('HTTP/1.1 500 Internal Server Error');

		$error = 'Błąd systemu';
		$description = 'Program wykonał nieprawidłową operację. Jeżeli uważasz to za słuszne, powiadom administratora tego systemu pod adresem ' . Config::getItem('site.email');

		if (@(include(Config::getItem('core.template') . '/error/Exception.php')) === false)
		{
			echo $e->getMessage();
		}
	}

	public function errorHandler($errno, $errstr, $errfile, $errline)
	{ 
		// logowanie bledow w dzienniku
		Log::add("$errstr [<b>$errfile <$errline></b>]", $errno);	
		if ($errno == E_USER_ERROR)
		{
			throw new Exception($errstr);
		}

		return false;
	}
}

/**
 * Wyjatek obslugi bledu 404
 */
class FileNotFoundException extends exception
{
	function __construct($message, $code = 0)
	{ 
		parent::__construct($message, $code);	
	}

	public function message($tpl = 'FileNotFoundException', $dir = 'error')
	{
		header('HTTP/1.1 404 File Not Found');

		$error = 'File not Found';
		$description = 'The requested page was not found. It may have been moved, been deleted, or archived.';
		$details = 'Requested file: ' . $this->getMessage();

		if (@(include(Config::getItem('core.template') . "/{$dir}/{$tpl}.php")) == false)
		{
			echo '<b>404</b> File not found: ' . $this->getMessage();		
		}

		exit;
	}
}
?>