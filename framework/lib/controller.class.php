<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa kontrolera. 
 * Nie jest ona inicjalizowana bezposrednio. Jedynie udostepnia metody, ktore sa dziedziczone 
 * przez klasy pochodne
 */
abstract class Controller extends Context
{
	/**
	 * Zwraca nazwe aktualnego kontrolera
	 * @return string
	 */
	public function getController()
	{
		$className = strtolower(get_class($this));
		$className = preg_replace('/_controller$/', '', $className);

		return $className;
	}

	/**
	 * Zwraca nazwe akcji 
	 * @return string
	 */
	public function getAction()
	{
		return $this->router->getAction();
	}

	/**
	 * Zwraca nazwe ewentualnego podfolderu, w ktorym znajduje sie kontroler
	 * @return string
	 */
	public function getFolder()
	{
		return $this->router->getFolder();
	}

	/**
	 * Odczytuje argument przekazany w URLu.
	 * Zakladajac, ze URL wyglada tak: /Foo/Bar/1/2, a Foo to nazwa kontrolera, a Bar - akcji,
	 * argumentami beda parametry 1 oraz 2
	 * @return string
	 */
	public function getArguments()
	{
		return $this->router->getArguments();
	}

	/**
	 * Zwraca tablice argumentow
 	 * W parametrze metody nalezy przekazac numer indeksu w tablicy.
	 * Element 1 bedzie posiadal indeks 0, 2 - 1 itd...
	 * @param int $index Nr indeksu
	 * @return mixed
	 */
	public function getArgument($index)
	{
		return $this->router->getArgument($index);
	}

	/**
	 * Zwraca nazwe aktualnego modulu (w ktorym znajduje sie aktualnie wykonywany kontroler)
	 * @return string Nazwa modulu (np. comment, pastebin)
	 */
	public function getModule()
	{
		$result = '';
		$locate = Load::locate($this->router->getPath());

		// lokalizujemy katalog, w ktorym uruchomiony jest kontroler
		$path = str_replace($this->router->getPath(), '', $locate[0]);
		if (preg_match('#module/([a-zA-Z0-9_-]+)#i', $path, $m))
		{
			$result = $m[1];
		}
		return $result;
	}
	
	/**
	 * Metoda dokonuje utworzenie kontrolera podanego w parametrach
	 * @param string $controller Nazwa kontrolera
	 * @param string $action Nazwa akcji
	 * @param string $folder Podkatalog (opcjonalnie)
	 */
	public function forward($controller, $action, $folder = '')
	{
		return Dispatcher::dispatch($controller, $action, $folder);		
	}

	/**
	 * Przekierowanie na podany URL
	 * @param string $url URL
	 */
	public function redirect($url, $code = null)
	{
		Url::redirect($url, $code);
	}

	/**
	 * Zwraca nazwe kontrolera
	 * @return string
	 */
	public function __toString()
	{
		return $this->getController();
	}
}

?>