<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Filtry musza implementowac ten intefejs
 */
interface IFilter 
{
	public function filter($value);
}

/**
 * Klasa obslugi filtrow
 */
class Filter
{
	/**
	 * Tablica przechowujaca instancje obiektow filtrow
	 * lub nazwy metod i funkcji, ktore beda wykorzystywane 
	 */
	private $filters;

	/**
	 * Statyczna metoda umozliwiajaca wywolanie pojedynczego filtra
	 * @param string $value Wartosc, ktora bedzie filtrowana
	 * @param string|object $filter Obiekt filtra lub nazwa metody/funkcji
	 * @param mixed $args Dodatkowe argumeny, ktore zostana przekazane do metody/funkcji
	 */
	public static function call($value, $filter, $args = array())
	{
		if (is_object($filter))
		{
			// jezeli klasa implementuje interfejs IFilter, od razu wywolujemy metode filter
			if ($filter instanceof IFilter)
			{
				return $filter->filter($value, $args);
			}
		}
		else
		{
			if (is_string($filter))
			{
				if (method_exists('Filter', $filter))
				{
					$filter = array('Filter', $filter);
				}
			}
		}
		// do argumentow nalezy dodac najwazniejsze - wartosc, ktora ma zostac filtrowana
		array_unshift($args, $value);

		return call_user_func_array($filter, $args);
	}

	/**
	 * Dodanie filtra do kolejki 
	 * @param string|object Obiekt/funkcja/metoda 
	 * @return string|int
	 */
	public function addFilter($filter)
	{
		if (is_object($filter))
		{
			if (!($filter instanceof IFilter))
			{
				throw new Exception('Filter class must implements IFilter interface');
			}
			$this->filters[] = $filter;
		}
		else if (is_array($filter))
		{
			$args = $filter;
			$filter = array_shift($args);

			if (!@Load::loadFile('lib/filter/' . strtolower($filter) . '.class.php'))
			{
				throw new Exception();
			}
			$class = 'Filter_' . $filter;

			if (class_exists($class, false))
			{
				$class = new ReflectionClass($class);

				if ($class->hasMethod('__construct') && $args)
				{
					$object = $class->newInstanceArgs($args);
				}
				else
				{
					$object = $class->newInstance();
				}
				$this->filters[] = $object;
			}
		}
		else
		{
			if (Load::fileExists('lib/filter/' . $filter . '.class.php'))
			{
				$class = new ReflectionClass('Filter_' . $filter);
				$this->filters[] = $class->newInstance();
			}
			else if (!method_exists($this, $filter))
			{
				if (!is_callable($filter))
				{
					throw new Exception('Filter is not valid callable method/function!');
				}

				$this->filters[] = $filter;
			}
			

		}
		return $this;	
	}

	/**
	 * Umozliwia ustawienie regul filtra (np. pobranie regul z pliku konfiguracji i przekazanie do metody)
	 * @param mixed $filters Reguly filtra
	 */
	public function setFilters(array $filters)
	{
		$this->reset();
		foreach ($filters as $index => $filter)
		{
			try
			{
				/**
				 * Jezeli indeksem tablicy jest lancuch znakow, 
				 * a wartoscia elementu - tablica, oznacza, to, ze prawdopodobnie
				 * user zada wywolania klasy filtra i przekazania mu okreslonych parametrow
				 */
				if (is_string($index) && is_array($filter))
				{
					array_unshift($filter, $index);
					$this->addFilter($filter);
				}
				else
				{
					$this->addFilter($filter);
				}
			}
			catch (Exception $e)
			{
				throw new Exception("Could not find $filter filter");
			}
		}
	}

	/**
	 * Czyszczenie kolejki
	 */
	public function reset()
	{
		$this->filters = array();
	}

	/**
	 * Wykonanie kolejki filtrow na wartosci przekazanej w parametrze
	 * @param string $value 
	 * @return 
	 */
	public function filterData($value)
	{
		if (!$this->filters)
		{
			return $value;
		}
		$filtered = $value;

		foreach ($this->filters as $filter)
		{
			if (is_object($filter))
			{
				$filtered = $filter->filter($filtered);
			}
			else if (method_exists($this, $filter))
			{
				$filtered = $this->$filter($filtered);
			}
			else
			{
				/** 
				 * Poprawka dla PHP 5.3. We wczesniejszych wersjach przekazanie 
				 * lancucha w parametrze funkcji call_user_func_array() nie powodowalo
				 * komunikatu ostrzezenia
				 */
				if (!is_array($filtered))
				{
					$filtered = array($filtered);
				}
				$filtered = call_user_func_array($filter, $filtered);
			}
		}
		return $filtered;
	}

	/**
	 * Filtr: usuwanie znakow nowej linii
	 * @param string $value
	 * @return string
	 */
	public function stripNewLines($value)
	{
		return str_replace(array("\r", "\n"), '', $value);
	}

	/**
	 * Filtr: rzutowanie na typ int
	 * @param $value
	 * @return int
	 */
	public function int($value)
	{
		return (int)$value;
	}

	/**
	 * Filtr: rzutowanie na typ string
	 * @param string $value
	 * @return string
	 */
	public function string($value)
	{
		return (string)$value;
	}

	/**
	 * Filtr: rzutowanie na typ float
	 * @param $value
	 * @return float
	 */
	public function float($value)
	{
		return (float)$value;
	}


}
?>