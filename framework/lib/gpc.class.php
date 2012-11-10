<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa obslugi GET/POST/COOKIE
 * Jest to klasa bazowa dla kas Get, Post, Cookie, Server
 */
abstract class Gpc implements Countable, ArrayAccess, IteratorAggregate
{
	/**
	 * Tablice tablicowe
	 */
	protected $data = array();
	/**
	 * Instancja do klasy Filter
	 */
	protected $filter;
	/**
	 * Okresla, czy filtry sa aktywne (domyslnie TRUE - tak)
	 */
	protected $enableFilter = true;

	/**
	 * Konstruktor: pobranie danych z tablicy GPC w zaleznosci od parametru
	 * @param string $method Moze zawierac lancuch: GET, POST, COOKIE, SERVER.
	 */
	function __construct($method)
	{
		$method = '_' . strtoupper($method);
		// referencja co danych w tablicy globalnej
		$this->data = &$GLOBALS[$method];	

		// inicjalizacja klasy filtra
		$this->filter = new Filter;
	}	

	/**
	 * Zwraca instancje do klasy filtra
	 * @return mixed
	 */
	public function getFilter()
	{
		return $this->filter;
	}

	/**
	 * Dodaje kolejny filtr do kolejki 
	 * @param mixed $filter 
	 * @return object
	 */
	public function addFilter($filter)
	{
		return $this->getFilter()->addFilter($filter);
	}
	
	/**
	 * Ustawia kolejke filtrow usuwajac poprzednie filtry
	 * @param mixed $filters Filtr lub tablica filtrow
	 * @return object
	 */
	public function setFilters($filters)
	{
		if (!is_array($filters))
		{
			$filters = array($filters);
		}
		return $this->getFilter()->setFilters($filters);
	}

	/**
	 * Dezaktywacja filtrow
	 */
	public function disableFilter()
	{
		$this->enableFilter = false;
	}

	/**
	 * Aktywacja filtrow
	 */
	public function enableFilter()
	{
		$this->enableFilter = true;
	}

	/**
	 * Zwraca dane z tablicy GPC w "czystej" formie.
	 * Oznacza to, ze framework w zaden sposob nie ingeruje w zawartosc danych.
	 * UWAGA! Dane zwrocone przez te metode moga byc potencjalnie niebezpieczne
	 * @param string $name Klucz z tablicy GPC
	 * @param string $default Domyslna wartosc, w przypadku, gdy klucz w tablicy nie zostal odnaleziony
	 * @return mixed
	 */
	public function value($name, $default = '')
	{
		if (empty($this->data[$name]))
		{
			if ($default)
			{
				return $default;
			}
			else
			{
				return null;
			}
		}
		return $this->data[$name];
	}

	/**
	 * Dzieki tej metodzie, dane z tablicy zosana przefiltrowane zgodnie z ustawieniami.
	 * Mozliwosc okreslenia dodatkowych parametrow domyslnych jezeli wartosc jest pusta.
	 * @param string $name Klucz z tablicy GPC
	 * @param mixed $args Dodatkowe argument (wartosci domyslne)
	 * @return mixed
	 * @example $this->foo($nonExists, $exists); // metoda zwroci wartosc zmiennej $exists
	 */
	public function __call($name, $args)
	{
		if (empty($this->data[$name]))
		{
			foreach ($args as $arg)
			{
				if (!empty($arg))
				{
					return $arg;
				}
			}

			return null;
		}
		if (!$this->enableFilter)
		{
			return $this->value($name);
		}

		if (is_array($this->data[$name]))
		{
			foreach ($this->data[$name] as $key => $value)
			{
				$this->data[$name][$key] = $this->filter->filterData($value);
			}
		}
		else
		{
			$this->data[$name] = $this->filter->filterData($this->data[$name]);
		}

		// zwrocenie przefiltrowanych danych 
		return $this->data[$name];
	}

	/**
	 * @see __call
	 */
	public function __get($name)
	{ 
		return $this->__call($name, array());
	}

	/**
	 * Zwraca TRUE jezeli dany klucz obecny jest w tablicy GPC
	 * @example isset($this->get->id)
	 * @return bool
	 */
	public function __isset($name)
	{
		return array_key_exists($name, $this->data);
	}

	/**
	 * Implementacja z interfejsu Countable
	 * Zwraca ilosc elementow w tablicy GPC
	 * @return int
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * Implementacja ArrayAccess
	 * UWAGA! Dane zwrocone przez te metode moga byc potencjalnie niebezpieczne
	 */
	public function offsetGet($offset)
	{
		if ($this->offsetExists($offset))
		{
			return $this->data[$offset];
		}
	}

	/**
	 * Implementacja ArrayAccess
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->data);
	}

	/**
	 * Implementacja ArrayAccess
	 */
	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}

	/**
	 * Implementacja ArrayAccess
	 */
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

	public function getIterator()
	{
		return new ArrayIterator($this->data);
	}
}

?>