<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa oblugi cache. 
 * Obsluguje jedynie cache oparty o system plikow
 */
class Cache_File implements Cache_Interface
{
	/**
	 * Tablica zmiennych umieszczonych w cache 
	 */
	protected $data = array();
	/**
	 * prefix dla nazw plikow PHP
	 */
	protected $prefix = '';
	/**
	 * Sciezka do cache 
	 */
	protected $dir = 'cache/';

	/**
	 * Konstruktor laduje ewentualny plik z cache
	 */
	function __construct($dir = null)
	{
		if ($dir)
		{
			$this->setDir($dir);
		}
		else
		{
			$this->setDir(Config::getBasePath() . 'cache/');
		}
	}

	/**
	 * Ustawia sciezke, gdzie zapisywany bedzie plik PHP z cache
	 * @param string $dir Sciezka do katalogu
	 */
	public function setDir($dir)
	{
		$this->dir = $dir;
	}
	
	/**
	 * Zwraca sciezke (katalog) gdzie powinien byc skladowany cache
	 * @return string
	 */
	public function getDir()
	{
		return $this->dir;
	}
	
	/**
	 * Ustawia prefix dla cache (wowczas kilka projektow moze dzielic ten sam katalog cache)
	 * @param string $prefix
	 */	
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}
	
	/**
	 * Zwraca prefix
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * Wywolanie tej metody skutkuje rozpoczeciem procesu cachowania
	 * @param $id	Identyfikator zasobu
	 */
	public function start($id)
	{
		$md5 = md5($id);
		
		if (isset($this->data[$md5]))
		{
			echo $this->data[$md5];
			return true;
		}

		ob_start();
		return false;
	}

	/**
	 * Wywolanie metody konczy proces cachowania i powoduje zapis rezulatu do pliku
	 * @param string $id Unikalne ID zasobu
	 * @param int $lifetime Czas (w sekundach) po ktorym zasob zostanie przedawniony
	 */
	public function end($id, $lifetime = 0)
	{
		$content = ob_get_contents();
		ob_flush();

		$this->save($id, $content, $lifetime);
	}

	/**
	 * Pobranie danego zasobu z cache
	 * @param $id	Nazwa (identyfikator)
	 */
	public function load($id)
	{
		$md5 = md5($id);
		
		if (!isset($this->data[$md5]))
		{
			@include_once($this->getDir() . $this->getFileName($id));
		}
		
		return empty($this->data[$md5]) ? null : $this->data[$md5];	
	}

	/**
	 * Pobranie wlasciwosci z tablicy
	 * @param string $id Nazwa klucza
	 * @return string
	 * @deprecated
	 */
	public function get($id)
	{
		return $this->load($id);
	}

	/**
	 * Umieszczenie zmiennej w cache
	 * @param string $id Nazwa zmiennej umieszczonej w cache
	 * @param mixed $value Dane do zapisu
	 * @param int $ttl Czas po ktorym dane zapisane w pliku zostana przedawnione
	 * @deprecated
	 */
	public function put($id, $value, $ttl = 0)
	{
		return $this->save($id, $value, $ttl);	
	}

	/**
	 * Usuniecie zmiennej znajdujacej sie w cache
	 * @param string $var Nazwa klucza
	 * @deprecated
	 */
	public function destroy($var = '')
	{
		return $this->remove($var);		
	}

	/**
	 * Usuniecie zmiennej znajdujacej sie w cache
	 * @param string $id Nazwa klucza
	 * @param int $mode Tryb usuwania. 
	 * 
	 * @example
	 * <code>
	 * $this->remove('foo*', Cache::PATTERN); // usunie klucze foobar, foofoo itp
	 * $this->remove('foo', Cache::ID); // usunie tylko klucz id
	 * $this->remove('', Cache::ID); // usunie caly cache
	 * </code>
	 * @return bool
	 */
	public function remove($id = '', $mode = Cache::ID)
	{
		$result = true;
		
		if ($mode == Cache::ID)
		{
			if ($id)
			{
				$result = @unlink($this->getDir() . $this->getFileName($id));
			}
			else
			{
				$glob = $this->getDir() . ($this->getPrefix() ? ($this->getPrefix() . '_') : '') . '*.php';
				foreach (glob($glob) as $fileName)
				{
					@unlink($fileName);
				}				
			}
		}
		elseif ($mode == Cache::PATTERN)
		{
			$regexp = str_replace('*', '.*', $id);
			$glob = $this->getDir() . ($this->getPrefix() ? ($this->getPrefix() . '_') : '') . '*.php';
			
			foreach (glob($glob) as $fileName)
			{
				$content = file_get_contents($fileName);			
				if (preg_match('#\/\* ' . $regexp . ' \*\/#', $content))
				{
					@unlink($fileName); 
				}
			}
		}
		
		return $result;		
	}

	/**
	 * Na podstawie parametru ID, zwraca nazwe pliku PHP, gdzie skladowany jest cache
	 * @param string $id
	 */
	private function getFileName($id)
	{
		$fileName = '';
		if ($this->getPrefix())
		{
			$fileName .= $this->getPrefix() . '_';
		}
		
		$fileName .= md5($id) . '.php';
		return $fileName;
	}

	/**
	 * Zapisanie danych do cache
	 * @param string $id	Identyfikator zasobu
	 * @param mixed $data	Dane do skladowania
	 * @param int $lifetime	Czas waznosci cache (w sekundach)
	 */
	public function save($id, $data, $lifetime = 0)
	{
		if ($fp = @fopen($this->getDir() . $this->getFileName($id), 'wb'))
		{
			$md5 = md5($id);
			
			@flock($fp, LOCK_EX);
			fwrite($fp, "<?php /* $id */ " . ($lifetime ? "\n\nif (time() > " . (time() + $lifetime) . ') { return; }' : '') . "\n\n\$this->data['$md5'] = " . var_export($data, true) . ";\n?>");
			@flock($fp, LOCK_UN);
			fclose($fp);
			
			$this->data[$md5] = $data;
		}
	}

	/**
	 * Metoda sprawdza, czy zmienna o podanej nazwie znajduje sie w cache
	 * @param string $id Nazwa identyfikatora
	 */
	public function exists($id)
	{
		$md5 = md5($id);
		$result = false;
		
		if (file_exists($this->getDir() . $this->getFileName($id)))
		{
			if (!isset($this->data[$md5]))
			{
				@include_once($this->getDir() . $this->getFileName($id));
			}
			
			$result = empty($this->data[$md5]) ? false : true;	

			if (!$result)
			{
				@unlink($this->getDir() . $this->getFileName($id));
			}			
		}
		
		return $result;		
	}
}

?>