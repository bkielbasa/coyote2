<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Filtrowanie oraz walidacja danych GPC 
 */
class Filter_Input
{
	/**
	 * Reguly filtrowania w formie tablicy PHP
	 */
	protected $filterRules = array();
	/**
	 * Reguly filtrowania w formie tablicy PHP
	 */
	protected $validatorRules = array();
	/**
	 * Komunikaty bledow walidacji
	 */
	protected $errors = array();

	/**
	 * Konstruktor klasy
	 * @param mixed $rules Reguly filtrow oraz walidatorow
	 */
	function __construct($rules = null)
	{ 
		$this->setRules($rules);
	}

	/**
	 * Ustawia reguly filtrow oraz walidatorow
	 * @param mixed $rules Reguly filtrow oraz walidatorow
	 */
	public function setRules($rules)
	{
		if (!$rules)
		{
			return;
		}
		$this->filterRules = array();
		$this->validatorRules = array();

		if (isset($rules['filter']))
		{
			$this->setFilters($rules['filter']);			
		}		
			
		if (isset($rules['validator']))
		{
			$this->setValidators($rules['validator']);
		}		
	}

	/**
	 * Dodaje reguly walidatora
	 * @param string $field Nazwa pola
	 * @param mixed $validator
	 */
	public function addValidator($field, $validator)
	{
		if (!isset($this->validatorRules[$field]))
		{
			$this->validatorRules[$field] = array();
		}
		$this->validatorRules[$field][] = $validator;		
		return $this;
	}

	/**
	 * Ustawia reguly walidatora
	 * @param mixed Tablica regul walidatora
	 * @return object Instancja klasy
	 */
	public function setValidators(array $array)
	{
		$this->validatorRules = $array;
		return $this;
	}

	/**
	 * Dodaje reguly filtra
	 * @param string $field Nazwa pola
	 * @param mixed $filter
	 */
	public function addFilter($field, $filter)
	{
		if (!isset($this->filterRules[$field]))
		{
			$this->filterRules[$field]= array();
		}
		$this->filterRules[$field][] = $filter;	
		return $this;
	}

	/**
	 * Ustawia reguly filtrow
	 * @param mixed Tablica filtrow
	 * @return object Instancja klasy
	 */
	public function setFilters(array $array)
	{
		$this->filterRules = $array;
		return $this;
	}

	/**
	 * Zwraca wartosc TRUE jezeli dane pomyslnie przeszly proces walidacji
	 * @param mixed &$data Dane (moze to byc referencja do tablicy $_POST, $_GET itp)
	 * @return bool
	 */
	public function isValid(&$data)
	{
		$this->data = &$data;
		$filter = &Load::loadClass('filter');
		$validate = &Load::loadClass('validate');

		$this->errors = array();

		if ($this->filterRules)
		{
			foreach ($this->filterRules as $field => $chainFilter)
			{
				if (array_key_exists($field, $data))
				{
					$filter->setFilters($chainFilter);

					$data[$field] = $filter->filterData($data[$field]);
				}
			}
		}
		if ($this->validatorRules)
		{
			foreach ($this->validatorRules as $field => $validatorChain)
			{
				$validate->setValidators($validatorChain);
				$value = isset($data[$field]) ? $data[$field] : $field;

				if (!$validate->isValid($value))
				{
					$this->errors[$field] = $validate->getErrors();
				}
			}
		}

		return ! (bool) $this->hasErrors();
	}

	/**
	 * Zwraca wszystkie komunikaty zwiazane z procesem walidacji i filtrowania
	 * @deprecated
	 * @return mixed
	 */
	public function getMessages($field = null)
	{
		return $this->getErrors($field);		
	}

	/**
	 * Zwraca wszystkie komunikaty zwiazane z procesem walidacji i filtrowania
	 * @return mixed
	 */
	public function getErrors($field = null)
	{
		if ($field)
		{
			if (!isset($this->errors[$field]))
			{
				return array();
			}
			return $this->errors[$field];
		}
		return $this->errors;
	}

	/**
	 * Zwraca TRUE w przypadku gdy sa bledy walidacji
	 * @param string $field Opcjonalna nazwa pola zwiazanego z walidacja
	 * @return bool
	 */
	public function hasErrors($field = null)
	{
		if (null !== $field)
		{
			return isset($this->errors[$field]);
		}
		else
		{
			return (bool)$this->errors;
		}
	}

	/**
	 * Formatuje komunikaty bledow - np. w formie znacznika HTML
	 * @param string $field Nazwa pola, ktorego dotycza komunikaty
	 * @param string $tag Znacznik HTML, ktory bedzie obejmowal komunikat - np. <li>Komunikat bledu</li>
	 * @return string Kod HTML
	 * @deprecated
	 */
	public function formatMessages($field, $tag = 'li')
	{
		$xhtml = '';
		foreach ($this->getMessages($field) as $message)
		{
			$xhtml .= ' ' . Html::tag($tag, true, array(), $message);
		}
		
		return $xhtml;
	}

	/**
	 * Formatuje komunikaty bledow - np. w formie znacznika HTML
	 * @param string $field Nazwa pola, ktorego dotycza komunikaty
	 * @param string $tag Znacznik HTML, ktory bedzie obejmowal komunikat - np. <li>Komunikat bledu</li>
	 * @return string Kod HTML
	 */
	public function formatErrors($field, $tag = 'li')
	{		
		return $this->formatMessages($field, $tag);
	}

	/**
	 * Zwraca wartosci przefiltrowanych danych
	 * @return mixed
	 */
	public function getValues()
	{
		$keys = array_merge(array_keys($this->filterRules), array_keys($this->validatorRules));
		$values = array();

		foreach ($keys as $key)
		{
			if (isset($this->data[$key]))
			{
				$values[$key] = $this->data[$key];
			}
		}

		return $values;
	}
}
?>