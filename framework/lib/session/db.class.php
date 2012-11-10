<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Session_Db implements ISession
{
	protected $db;

	/** 
	 * Konstruktor klasy. W parametrze mozliwe jest przekazanie lokalzacji, gdzie beda przechowywane pliki
	 * z informacjami o sesji
	 * @param string $sessionPath
	 */
	function __construct()
	{
		$this->db = &Core::getInstance()->db;
	}

	/**
	 * Destruktor klasy
	 */
	function __destruct()
	{
		session_write_close();
	}

	/** 
	 * Metoda ustawia nowa sciezke, gdzie beda zapisywane pliki przechowujace informacje o sesji
	 * @param string $sessionPath Sciezka
	 */
	public function setSessionPath($sessionPath)
	{
		session_save_path($sessionPath);
	}

	/**
	 * Zwraca sciezke gdzie zapisywane sa informacje o sesji
	 * @return string
	 */
	public function getSessionPath()
	{
		return session_save_path();
	}

	public function open($path, $sessionName)
	{
		return true;
	}

	public function close()
	{
		return true;
	}

	/** 
	 * Odczyt informacji z pliku tekstowego
	 * @param string $sid ID sesji
	 * @return string
	 */
	public function read($sid)
	{
		return (string)@file_get_contents($this->getSessionPath() . "$sid.txt");
	}

	/**
	 * Zapisuje nowe dane sesji do pliku
	 * @param string $sid ID sesji
	 * @param string $data Dane do zapisu
	 * @return bool
	 */
	public function write($sid, $data)
	{
		return @file_put_contents($this->getSessionPath() . "$sid.txt", $data, FILE_TEXT);
	}

	/**
	 * Usuwa sesje
	 * @param string $sid ID sesji
	 * @return bool
	 */
	public function destroy($sid)
	{
		return @unlink($this->getSessionPath() . "$sid.txt");
	}

	/**
	 * Mechanizm GC
	 * @param int $lifeTime Czas "zycia" sesji
	 * @return bool
	 */
	public function gc($lifeTime)
	{
		foreach (glob($this->getSessionPath() . '*.txt') as $fileName)
		{
			if (filemtime($fileName) + $lifeTime < time())
			{
				@unlink($fileName);
			}
		}

		return true;
	}
}
?>