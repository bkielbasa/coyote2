<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa bazowa dla walidatorow
 */
abstract class Validate_Abstract
{
	/**
	 * Walidowana wartosc
	 */
	protected $value;
	/**
	 * Komunikaty bledow walidatora
	 */
	protected $errors = array();

	/**
	 * Umozliwia ustalenie opcji dla walidatora
	 * Opcje musza byc przekazane w tablicy asocjacyjnej, a klucze tablicy musza odpowiadac
	 * nazwie metody w walidatorze (bez przedrostka set)
	 * @param mixed $options
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $function => $args)
		{
			$function = 'set' . $function;

			if (method_exists($this, $function))
			{
				$this->$function($args);
			}
		}
		return $this;
	}

	/**
	 * Ustawia wartosc dla pola $value
	 * @param string $value 
	 */
	protected function setValue($value)
	{
		/* jezeli przekazana zostala wartosc, czysimy tablice komunikatow ewentualnych bledow */
		$this->errors = array();
		$this->value = $value;
	}

	/** 
	 * Ustawia wartosc komunikatu w przypadku bledu walidatora
	 * (wartosc nie przeszla poprawnie procesu walidacji)
	 * @param int $message Stala z klasy potomnej
	 * @deprecated
	 * @return false
	 */
	protected function setMessage($message)
	{		
		return $this->setError($message);
	}

	/** 
	 * Ustawia wartosc komunikatu w przypadku bledu walidatora
	 * (wartosc nie przeszla poprawnie procesu walidacji)
	 * @param int $message Stala z klasy potomnej
	 * @return false
	 */
	protected function setError($error)
	{
		// wartosc %value% jest zastepowana przez aktualna wartosc
		$error = str_replace('%value%', $this->value, $this->templates[$error]);
		
		if (isset($this->vars))
		{ 
			foreach ($this->vars as $value => $var)
			{
				$error = str_replace('%' . $value . '%', $this->$var, $error);
			}
		}
		$this->errors[] = $error;
		
		return false;
	}
	
	/**
	 * Zwraca tablice komunikatu dla danego walidatora
	 * @deprecated
	 * @return mixed
	 */
	public function getMessages()
	{
		return $this->getErrors();
	}

	/**
	 * Zwraca tablice komunikatu dla danego walidatora
	 * @return mixed
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Zwraca TRUE jezeli dany walidator zwrocil jakies komunikaty
	 * @deprecated
	 * @return bool
	 */
	protected function isMessages()
	{
		return (bool) $this->errors;
	}

	/**
	 * Zwraca TRUE jezeli dany walidator zwrocil jakies komunikaty
	 * @return bool
	 */
	protected function hasErrors()
	{
		return (bool) $this->errors;
	}

	/**
	 * Ustawia tresc komunikatu w przypadku, gdy wartosc nie przeszla walidacji poprawnie
	 * @param int $template Stala okreslajaca typ komunikatu (z klasy potomnej)
	 * @param string $message Komunikat w przypadku wystapienia danego bledu
	 */
	public function setTemplate($template, $message)
	{
		$this->templates[$template] = $message;
	}

	public function __toString()
	{
		return get_class($this);
	}
}

?>