<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Interfejs adaptera klasy sesji
 */
interface ISession
{
	public function open($path, $sessionName);
	public function close();
	public function read($sid);
	public function write($sid, $data);
	public function destroy($sid);
	public function gc($lifeTime);
}

/**
 * Obsluga sesji uzytkownika
 */
class Session
{
	/**
	 * Adapter sesji
	 */
	protected $adapter;
	/**
	 * @bool Okresla, czy sesja zostala uruchomiona
	 */
	protected $start = false;

	/** 
	 * Inicjalizacja sesji. Mozliwe jest skorzystanie z niestandardowego adaptera sesji.
	 * W takim przypadku, w konstruktorze nalezy przekazac obiekt klasy adaptera lub nazwe
	 * adaptera. Jezeli zostanie przekazana wartosc pusta system sprobuje zainicjalizowac
	 * domyslnego adaptera (jezeli taki zostal okreslony w pliku konfiguracji)
	 * @param $adapter string|object|null
	 */
	function __construct($adapter = null)
	{
		if ($adapter instanceof ISession)
		{
			$this->setAdapter($adapter);
		}
		elseif (is_string($adapter) && !empty($adapter))
		{
			if (strpos($adapter, 'Session_') === false)
			{
				$className = 'Session_' . $adapter;
			}		
			$this->setAdapter(new $className);
		}
		else
		{
			if (Config::getItem('session.adapter'))
			{
				$className = 'Session_' . (string)Config::getItem('session.adapter');
				$this->setAdapter(new $className);
			}			
		}
	}

	/** 
	 * Ustawia obiekt adaptera
	 * @param object $adapter
	 */
	public function setAdapter(ISession $adapter)
	{
		$this->adapter = new $adapter;

		session_set_save_handler
		(
			array($this->adapter, 'open'),
			array($this->adapter, 'close'),
			array($this->adapter, 'read'),
			array($this->adapter, 'write'),
			array($this->adapter, 'destroy'),
			array($this->adapter, 'gc')
		);
	}

	/**
	 * Rozpoczecie sesji
	 */
	public function start()
	{
		/**
		 * Jezeli sesja jest juz zainicjalizowana, pomijamy dalszy kod
		 */
		if ($this->start)
		{
			return false;
		}
		session_name(Config::getItem('cookie.prefix') . Config::getItem('session.name', 'Coyote'));

		session_set_cookie_params(
			Config::getItem('session.lifetime', 0), 
			Config::getItem('cookie.path'), 
			Config::getItem('cookie.host')
		);

		session_start();
		$this->start = true;
	}

	/** 
	 * Zwrocenie ID sesji
	 * @return string
	 */
	public function getId()
	{
		return session_id();
	}

	/**
	 * Ustawia ID sesji
	 * @param string ID sesji
	 */
	public function setId($sid)
	{
		return session_id($sid);
	}

	/** 
	 * Usuniecie sesji
	 */
	public function destroy()
	{
		session_unset();
		session_destroy();
	}

	/**
	 * Metoda sluzy do przypisania nowej wartosci w sesji (w tablicy $_SESSION)
	 * @param string $key Nazwa klucza
	 * @param mixed $value Wartosc
	 */
	public function set($key, $value = null)
	{
		$this->start();

		if (is_array($key))
		{
			foreach ($key as $k => $v)
			{
				$_SESSION[$k] = $v;
			}
		}
		else
		{
			$_SESSION[$key] = $value;
		}
	}

	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Odczytanie wartosci z tablicy $_SESSION
	 * @param string $key Klucz tablicy
	 * @return mixed
	 */
	public function get($key)
	{
		$this->start();
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}

	/**
	 * Metoda realizuje pobranie wartosci z tablicy $_SESSION, a nastepnie usuniecie jej
	 * @param string $key Klucz tablicy
	 * @return mixed
	 */
	public function getAndDelete($key)
	{
		$result = $this->get($key);
		$this->delete($key);

		return $result;
	}

	public function __get($key)
	{
		return $this->get($key);
	}

	public function __isset($key)
	{
		$this->start();
		return isset($_SESSION[$key]);
	}

	public function __unset($key)
	{
		return $this->delete($key);
	}

	/**
	 * Usuniecie elementu z tablicy
	 */
	public function delete($key)
	{
		$this->start();
		unset($_SESSION[$key]);
	}
}
?>