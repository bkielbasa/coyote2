<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Interfejs dla walidatorow
 */
interface IValidate
{
	public function isValid($value);
}

/**
 * Klasa walidacji danych
 */
class Validate 
{	
	/**
	 * Tablica przechowujaca obiekty walidatorow
	 */
	protected $validators = array();
	/**
	 * Komunikaty bledow
	 */
	protected $errors = array();

	/**
	 * Dodanie walidatora do kolejki
	 * @param object $validator Walidator
	 */
	public function addValidator(IValidate $validator)
	{
		$this->validators[] = $validator;
		return $this;
	}

	private function loadValidator($validator, $args = array())
	{
		$templates = array();

		// samodzielnie ladujemy klase walidatora korzystajac z include_path
		Load::loadFile('lib/validate/' . strtolower($validator) . '.class.php');
		$validator = 'Validate_' . $validator; 				

		if (class_exists($validator, false))
		{
			$class = new ReflectionClass($validator);
			if (isset($args['templates']))
			{
				$templates = $args['templates'];
				unset($args['templates']);
			}

			if ($class->hasMethod('__construct') && $args)
			{ 
				$object = $class->newInstanceArgs($args);
			}
			else
			{
				$object = $class->newInstance();
			}
			if ($templates)
			{
				foreach ($templates as $template => $message)
				{
					$object->setTemplate(constant("{$validator}::" . $template), $message);
				}
			}

			$this->addValidator($object);
		}
	}
	
	/**
	 * Umozliwia konfiguracje walidatorow
	 * Metoda umozliwia przekazanie konfiguracji bez koniecznosci samodzielnego
	 * inicjowania poszczegolnych walidatorow
	 * @param mixed $validators Tablica PHP zawierajaca parametry konfiguracji
	 * @return object Referencja do tego obiektu
	 */
	public function setValidators(array $validators)
	{
		$this->reset();
		foreach ($validators as $index => $args)
		{
			try
			{
				// klucz jest lancuchem, a wartosc - tablica argumentow przekazywana do konstruktora
				if (is_string($index))
				{
					$this->loadValidator($index, $args);					
				}
				// klucz jest liczba, a wartosc - instancja klasy walidatora
				else if (is_int($index) && ($args instanceof IValidate))
				{
					$this->addValidator($args);
				}
				// klucz jest liczba, a wartosc - lancuchem okreslajacym nazwe walidatora
				else if (is_int($index) && is_string($args))
				{
					$this->loadValidator($args);
				}
				else if (is_int($index) && is_array($args))
				{
					$validator = array_shift($args);
					$this->loadValidator($validator, $args);
				}
				else
				{
					throw new Exception();
				}
			}
			catch (Exception $e)
			{ 
				throw new Exception("Could not find $index validator");
			}
		}
		return $this;
	}

	/**
	 * Przeprowadza proces walidacji danych wykorzystujac przy tym ustawione walidatory
	 * @param string $value Walidowana wartosc
	 * @return bool TRUE w przypadku gdy proces walidacji zostal przeprowadzony prawidlowo - w przeciwnym wypadku FALSE
	 */
	public function isValid($value)
	{
		if (!$this->validators)
		{
			return true;
		}
		foreach ($this->validators as $validator)
		{
			if (!$validator->isValid($value))
			{
				$this->errors = array_merge($this->errors, $validator->getErrors());
			}
		}

		return !$this->hasErrors();
	}
	
	/**
	 * Resetuje kolejke walidatorow
	 */
	public function reset()
	{
		$this->validators = array();
		$this->errors = array();
	}

	/**
	 * Umozliwia wywolanie walidatora statycznie
	 * @param mixed $value Wartosc
	 * @param object $validator Obiekt walidatora
	 * @return bool
	 */
	public static function call($value, IValidate $validator)
	{
		return $validator->isValid($value);
	}

	/**
	 * Zwraca tablice z komunikatami bledow procesu walidacji
	 * @deprecated
	 */
	public function getMessages()
	{
		return $this->getErrors();
	}

	/**
	 * Zwraca tablice z komunikatami bledow procesu walidacji
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Zwraca TRUE jezeli wystapily bledy walidacji
	 * @return bool
	 */
	public function hasErrors()
	{
		return (bool) $this->errors;
	}
}
?>