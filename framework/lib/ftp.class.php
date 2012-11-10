<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa wyjatku generowanego w razie problemu z polaczeniem z serwerem
 */
class FtpCouldNotConnectException extends Exception {}
/**
 * Klasa wyjatku generowanego w razie problemu z zalogowaniem do serwera
 */
class FtpCouldNotLoginException extends Exception { }

/**
 * Klasa obslugi protokolu FTP
 */
class Ftp
{
	/**
	 * ID polaczenia
	 */
	private $connectionId;
	/**
	 * Host polaczenia z serwerem
	 */
	private $ftpHost;

	/** 
	 * Konstruktor klasy
	 * @param string $ftpHost Adres, nazwa serwera FTP
	 * @param int $ftpPort Port polaczenia (domyslnie 21)
	 */
	function __construct($ftpHost = '', $ftpPort = 21)
	{
		$this->connect($ftpHost, $ftpPort);
	}

	/** 
	 * Metoda umozliwiajca polaczenie z serwerem
	 * @param string $ftpHost Adres, nazwa serwera FTP
	 * @param int $ftpPort Port polaczenia (domyslnie 21)
	 * @return bool|mixed Zwraca strumien FTP lub FALSE w przypadku niepowodzenia
	 */
	public function connect($ftpHost, $ftpPort = 21)
	{
		if ($ftpHost)
		{
			if (!$this->connectionId = ftp_connect($ftpHost, $ftpPort))
			{
				throw new FtpCouldNotConnectException("Could not connect to the server: $ftpHost");
			}
			$this->ftpHost = $ftpHost;
		}

		return $this->connectionId;
	}

	/** 
	 * Logowanie do serwera FTP
	 * @param string $ftpLogin Nazwa uzytkownika
	 * @param string $ftpPassword Haslo uzytkownika
	 * @param bool Zwraca TRUE w przypadku prawidlowego polaczenia lub FALSE
	 */
	public function login($ftpLogin, $ftpPassword)
	{
		if (!$result = @ftp_login($this->connectionId, $ftpLogin, $ftpPassword))
		{
			throw new FtpCouldNotLoginException("Could not login to the server: $this->ftpHost");
		}

		return $result;
	}

	/**
	 * Zamyka aktywane polaczenie FTP
	 */
	public function close()
	{
		ftp_close($this->connectionId);
	}

	/**
	 * Zmienia aktywny katalog 
	 * @param string $dir Nazwa nowego katalogu
	 */
	public function chdir($dir)
	{
		return ftp_chdir($this->connectionId, $dir);
	}

	/**
	 * Zmienia aktywny katalog 
	 * @param string $dir Nazwa nowego katalogu
	 */
	public function changeDirectory($dir)
	{
		return $this->chdir($dir);
	}

	/**
	 * Zwraca nazwe aktualnego katalogu na sewerze
	 * @param string 
	 */
	public function pwd()
	{
		return ftp_pwd($this->connectionId);
	}

	/**
	 * Zwraca nazwe aktualnego katalogu na sewerze
	 * @param string 
	 */
	public function getCurrentDir()
	{
		return $this->pwd();
	}

	/**
	 * Metoda powoduje przejscie do katalogu wyzej, na serwerze FTP
	 * @return bool
	 */
	public function cdup()
	{
		return ftp_cdup($this->connectionId);
	}

	/**
	 * Zwraca liste plikow i katalogow w danym katalogu FTP
	 * @param string $dir Nazwa katalogu, ktorego zawartosc zostanie zwrocona. 
	 * Jezeli wartosc jest pusta zostanie pobrana zawartosc aktualnego katalogu
	 * @param fullList bool Jezeli TRUE zwroci wiecej informacji odnosnie plikow
	 * @return mixed
	 */
	public function rawlist($dir = '', $fullList = false)
	{
		if (!$dir)
		{
			$dir = $this->getCurrentDir();
		}

		if ($fullList)
		{
			return ftp_rawlist($this->connectionId, $dir);
		}
		else
		{
			return ftp_nlist($this->connectionId, $dir);
		}
	}

	/**
	 * Zwraca liste plikow i katalogow w danym katalogu FTP
	 * @param string $dir Nazwa katalogu, ktorego zawartosc zostanie zwrocona. 
	 * Jezeli wartosc jest pusta zostanie pobrana zawartosc aktualnego katalogu
	 * @param fullList bool Jezeli TRUE zwroci wiecej informacji odnosnie plikow
	 * @return mixed
	 */
	public function getFilesList($dir = '', $fullList = false)
	{
		return $this->rawlist($dir, $fullList);
	}

	/**
	 * Aktywuje lub deaktywuje tryb pasywny
	 * @param bool $pasv TRUE jezeli ma zostac wlaczony tryb pasywny
	 * @return bool
	 */
	public function pasv($pasv = false)
	{
		return ftp_pasv($this->connectionId, (bool) $pasv);
	}

	/**
	 * Ustawia tryb pasywny
	 * @return bool
	 */
	public function setPassive()
	{
		return $this->pasv(true);
	}

	/**
	 * Ustawia tryb aktywny
	 * @return bool
	 */
	public function setActive()
	{
		return $this->pasv(false);
	}

	/**
	 * Tworzy nowy katalog na serwerze
	 * @param string $dir Nazwa katalogu
	 * @return bool
	 */
	public function mkdir($dir)
	{
		return ftp_mkdir($this->connectionId, $dir);
	}

	/**
	 * Tworzy nowy katalog na serwerze
	 * @param string $dir Nazwa katalogu
	 * @return bool
	 */
	public function createDirectory($dir)
	{
		return $this->mkdir($dir);
	}

	/**
	 * Usuwa katalog na serwerze. Katalog musi byc pusty!
	 * @param string $dir Nazwa katalogu
	 * @return bool 
	 */
	public function rmdir($dir)
	{
		return ftp_rmdir($dir);
	}

	/**
	 * Usuwa katalog na serwerze. Katalog musi byc pusty!
	 * @param string $dir Nazwa katalogu
	 * @return bool 
	 */
	public function removeDirectory($dir)
	{
		return $this->rmdir($dir);
	}

	/**
	 * Zmienia nazwe danego katalogu/pliku na serwerze FTP
	 * @param string $oldFileName nazwa starego pliku/katalogu
	 * @param string $newFileName nazwa nowego pliku/katalogu
	 * @rerun bool
	 */
	public function rename($oldFileName, $newFileName)
	{
		return ftp_rename($this->connectionId, $oldFileName, $newFileName);
	}

	/**
	 * Przesyla plik na serwer FTP
	 * @param string $fileName nazwa pliku na serwerze lokalnym
	 * @param string $remoteFileName Nazwa pliku na serwerze zdalnym (opcjonalnie)
	 * @param int $fileType Typ pliku (ASCII, binarny)
	 * @return bool
	 */
	public function put($fileName, $remoteFileName = '', $fileType = FTP_BINARY)
	{
		if (!$remoteFileName)
		{
			$remoteFileName = $fileName;
		}
		return ftp_put($this->connectionId, $remoteFileName, $fileName, $fileType);
	}

	/**
	 * Przesyla plik na serwer FTP (dzlanie asynchroniczne)
	 * @param string $fileName nazwa pliku na serwerze lokalnym
	 * @param string $remoteFileName Nazwa pliku na serwerze zdalnym (opcjonalnie)
	 * @param int $fileType Typ pliku (ASCII, binarny)
	 * @return Informacja o postepie w przesylanych danych
	 */
	public function putAsync($fileName, $remoteFileName = '', $fileType = FTP_BINARY)
	{
		if (!$remoteFileName)
		{
			$remoteFileName = $fileName;
		}
		return ftp_nb_put($this->connectionId, $remoteFileName, $fileName, $fileType);
	}

	/**
	 * Kontynuuj przesylanie danych (w przesylaniu asynchronicznym)
	 */
	public function ftpContinue()
	{
		return ftp_nb_continue($this->connectionId);
	}

	/**
	 * Pobiera plik z serwera FTP
	 * @param string $remoteFileName nazwa pliku na serwerze
	 * @param string $fileName Nazwa pliku (sciezka) ktora zostanie nadana na serwerze lokalnym
	 * @param int $fileType Typ pliku (ASCII, binarny)
	 * @return bool
	 */
	public function get($remoteFileName, $fileName = '', $fileType = FTP_BINARY)
	{
		if (!$fileName)
		{
			$fileName = basename($remoteFileName);
		}
		return ftp_get($this->connectionId, $fileName, $remoteFileName, $fileType);
	}

	/**
	 * Usuwa plik na serwerze FTP
	 * @param string $fileName nazwa pliku
	 * @return bool
	 */
	public function delete($fileName)
	{
		return ftp_delete($this->connectionId, $fileName);
	}

	/**
	 * Zmienia prawa dostepu dla danego katalogu/pliku
	 * @param string $fileName Nazwa pliku/katalogu
	 * @param int Prawo dostepu
	 * @return bool
	 */
	public function chmod($fileName, $chmod)
	{
		return ftp_chmod($this->connectionId, $chmod, $fileName);
	}

	/**
	 * Zwraca rozmiar danego pliku
	 * @param string $fileName Nazwa danego pliku
	 * @return int
	 */
	public function size($fileName)
	{
		return ftp_size($this->connectionId, $fileName);
	}

	/**
	 * Zwraca rozmiar danego pliku
	 * @param string $fileName Nazwa danego pliku
	 * @return int
	 */
	public function getFileSize($fileName)
	{
		return $this->size($fileName);
	}

	/**
	 * Zwraca ID polaczenia z FTP
	 * @return mixed
	 */
	public function getConnectionId()
	{
		return $this->connectionId;
	}

}
?>