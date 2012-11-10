<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

interface IView
{
	public function initialize(&$context);
	public function display($display = true);
	public function loadConfig();
}

/**
 * Klasa obslugi widoku
 */
class View
{
	const NONE		=		false;
	const MAIN		=		'';
	const SUCCESS	=		'Success';
	const SUBMIT	=		'Submit';
	const ERROR		=		'Error';
	const DELETE	=		'Delete';
	const EDIT		=		'Edit';

	/**
	 * Dane przekazywane do widoku
	 */
	private $data = array();
	/**
	 * Zawartosc widoku
	 */
	public $content;
	/**
	 * Sciezka do katalogu z widokiem
	 */
	private $path;
	/**
	 * Nazwa ladowanego widoku
	 */
	private $name;
	/**
	 * Instancja klasy obslugi danego widoku
	 */
	private $instance;

	/**
	 * Statyczna metoda, ktora tworzy instancje widoku
	 * @param string $name Nazwa pliku widoku (bez rozszerzenia!)
	 * @param mixed $data Dodatkowe parametry, ktore zostana przekazane do widoku
	 * @param object $instance Domyslnie null. Instancja klasy obslugi widoku
	 */
	public static function getView($name, $data = array(), IView $instance = null)
	{
		return new View($name, $data, $instance);
	}

	/**
	 * Konstruktor: ladowanie konfiguracji widoku
	 * @param string $name Nazwa widoku (mo�e r�wnie� zawiera� nazw� podkatalogu)
	 * @param mixed $data Dane przekazane do widoku
	 * @param object $instance Domyslnie null. Instancja klasy obslugi widoku
	 * @example $view = new view('subdir/my.php', array('foo' => 'bar'));
	 */
	public function __construct($name, $data = array(), IView $instance = null)
	{
		// zaladowanie klasy obslugi konfiguracji widokow
		Load::loadFile('lib/view/config.class.php');

		$path = '';
		if (strpos($name, '/') !== false)
		{
			$path = dirname($name) . '/';
			$name = basename($name);
		}
		// ustalenie sciezki do widoku
		$this->path = Config::getItem('core.template') . '/' . $path;
		$this->name = basename($name, Config::getItem('core.templateSuffix'));

		log::add("View $name initialized", E_DEBUG);
		$this->data = array_merge($data, $this->data);

		if ($instance === null)
		{
			$instance = new View_XHTML;
		}
		$this->instance = $instance;
		$this->instance->initialize($this);

		// zaladowanie konfiguracji widokow
		$this->instance->loadConfig();
	}

	/**
	 * Przypisanie danych do widoku
	 * @param string $key Klucz (nazwa parametru przekazywanego do widoku)
	 * @param string | mixed $value Wartosc
	 */
	public function assign($key, $value = '')
	{
		if (!is_array($key))
		{
			$this->data[$key] = $value;
		}
		else
		{
			while (list($k, $v) = each($key))
			{
				$this->data[$k] = $v;
			}
		}
	}

	/**
	 * Metoda dodaje nowe wartosci do uprzednio przekazanej tablicy
	 * @param string $key Klucz tablicy asocjacyjnej
	 * @param string $value Wartosc
	 */
	public function append($key, $value)
	{
		if (isset($this->data[$key]))
		{
			if (!is_array($this->data[$key]))
			{
				$this->data[$key] = array($this->data[$key]);
			}
		}
		$this->data[$key][] = $value;
	}

	/**
	 * Zwraca dane przekazane do widoku metoda assign()
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Zwraca nazwe widoku
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Zwraca sciezke do katalogu z widokiem
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Zwraca instancje obiektu widoku
	 */
	public function getInstance()
	{
		return $this->instance;
	}

	/**
	 * Zaladowanie i (opcjonalnie) zwrocenie zawartosci widoku
	 * @param bool $display Domyslnie TRUE powoduje wyswietlenie zawartosci widoku
	 * @return Opcjonalnie (jezeli $display == false) zwrocenie zawartosci widoku
	 */
	public function display($display = true)
	{
		// wywolanie metody obslugi widoku
		return $this->instance->display($display);
	}

	/**
	 * Magic method - wyswietlenie zawartosci widoku
	 */
	public function __toString()
	{
		try
		{
			return $this->display(false);
		}
		catch (Exception $e)
		{
			trigger_error($e->getMessage(), E_USER_ERROR);
			return '';
		}
	}

	/**
	 * Magic method: przypisanie wartosci szablonu
	 */
	public function __set($key, $value)
	{
		$this->assign($key, $value);
	}

	/**
	 * Magic method: odczyt uprzednio przypisanej wartosci
	 */
	public function __get($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}
}

?>