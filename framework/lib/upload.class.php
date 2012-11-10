<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class DirNotFoundException extends Exception {}
class DirNotWriteable extends Exception {} 
class FileSendingFailedException extends Exception {}
class FileExistsException extends Exception {}

/**
 * Odbieranie plikow wysylanych przez uzytkownika
 */
class Upload
{
	/**
	 * Sciezka do katalogu, w ktorym zostnie umieszczony plik
	 */
	protected $path;
	/**
	 * Wartosc bool okresla czy w przypadku gdy pod podana sciezka znajduje sie plik,
	 * system powinien dokonac jego zamiany
	 */
	protected $overwrite = false;
	/**
	 * Tablica z informacjami o pliku (z tablicy _FILES)
	 */
	protected $data;
	/**
	 * Rozszerzenie odebranego pliku
	 */
	protected $suffix;

	function __construct()
	{
		if (ini_get('file_uploads') == false) 
		{
            throw new Exception('File uploads are not allowed in your php config!');
        }
	}

	/**
	 * Ustawia wartosc overwrite. Jezeli TRUE - w przypadku gdy istnieje juz plik o tej nazwie - zostanie nadpisany
	 * @param bool
	 */
	public function setOverwrite($overwrite)
	{
		$this->overwrite = $overwrite;
	}

	/**
	 * Ustawia sciezke do katalogu, w ktorym zostanie umieszczony plik
	 * @param string $path
	 */
	public function setDestination($path)
	{
		if (!is_dir($path))
		{
			throw new DirNotFoundException("Directory $path does not exists!");
		}
		if (!is_writeable($path))
		{
			throw new DirNotWriteable("Directory $path is not writable");
		}
		if (!$path[strlen($path) -1] != '/')
		{
			$path .= '/';
		}
		$this->path = $path;		
	}

	/**
	 * Pobieranie pliku od uzytkownika
	 * @param string $field Nazwa pola <input type="file" ...
	 * @return bool
	 */
	public function recive($field = null)
	{
		// jezeli nie okreslono pola - system probuje odczytac klucz z tablicy
		if ($field == null)
		{
			$field = @key($_FILES);
		}
		if (!isset($_FILES[$field]))
		{
			return false;
		}
		if (empty($_FILES[$field]['name']))
		{
			return false;
		}
		
		if (!$this->path)
		{
			throw new DirNotFoundException("Destination directory is not set");
		}
		if (is_uploaded_file($_FILES[$field]['tmp_name']))
		{
			if (file_exists($this->path . $_FILES[$field]['name']) && !$this->overwrite)
			{
				throw new FileExistsException('File ' . $_FILES[$field]['name'] . " already exists in {$this->path}");
			}
			$this->suffix = strtolower(end(explode('.', $_FILES[$field]['name'])));

			if (!move_uploaded_file($_FILES[$field]['tmp_name'], $this->path . $_FILES[$field]['name']))
			{
				throw new FileSendingFailedException("Could not copy file to directory: {$this->path}");
			}		
		}		
		$this->data = $_FILES[$field];

		return true;
	}

	/**
	 * Zwraca nazwe wyslanego pliku
	 * @return string
	 */
	public function getFileName()
	{
		return $this->data['name'];
	}

	/**
	 * Zwraca informacje o pliku
	 * @return mixed
	 */
	public function getFileInfo()
	{
		return $this->data;
	}

	/**
	 * Zwraca katalog (sciezke) w ktorej zostal umieszczony plik
	 * @return string
	 */
	public function getDestination()
	{
		return $this->path;
	}

	/**
	 * Zwraca pelna sciezke do pliku (sciezka do katalogu + nazwa pliku
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->getDestination() . $this->getFileName();
	}

	/**
	 * Zwraca rozmiar wyslanego pliku
	 * @return int
	 */
	public function getFileSize()
	{
		return $this->data['size'] / 1024;
	}

	/**
	 * Zwraca typ MIME wyslanego pliku
	 * @return string
	 */
	public function getFileMime()
	{
		return $this->data['type'];
	}

	/**
	 * Zwraca nazwe rozszerzenia wyslanego pliku
	 * @return string
	 */
	public function getExtension()
	{
		return $this->suffix;
	}

	/**
	 * @see getExtension()
	 */
	public function getSuffix()
	{
		return $this->suffix;
	}
}
?>