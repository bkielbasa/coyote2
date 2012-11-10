<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Walidator wartosci typu float
 */
class Validate_Float extends Validate_Abstract implements IValidate
{
	const NOT_FLOAT = 1;
	const LESS_THAN = 2;
	const GREATER_THAN = 3;

	protected $templates = array(

			self::NOT_FLOAT			=> '"%value%" nie jest prawidłową liczbą',
			self::LESS_THAN			=> 'Liczba "%value%" jest mniejsza %min%',
			self::GREATER_THAN		=> 'Liczba "%value%" jest większa od %max%'

	);

	protected $vars = array(

			'min'					=> 'min',
			'max'					=> 'max'

	);
	protected $optional;
	protected $min;
	protected $max;

	function __construct($optional = false, $min = 0, $max = null)
	{
		$this->setOptional($optional);
		$this->setMin($min);
		$this->setMax($max);
	}

	/**
	 * Ustawia minimalna wartosc liczby int
	 * @param int $min
	 */
	public function setMin($min)
	{
		$this->min = $min;
	}

	/**
	 * Ustawia maksymalna mozliwa wartosc liczby int
	 * @param int $max
	 */
	public function setMax($max)
	{
		$this->max = $max;
	}

	/**
	 * Okresla, czy element jest opcjonalny
	 * @param bool $optional Jezeli TRUE, system nie zglosi bledu w przypadku gdy lancuch jest pusty
	 */
	public function setOptional($optional)
	{
		$this->optional = $optional;
	}

	/**
	 * Walidacja danych
	 * @value 
	 * @return bool
	 */
	public function isValid($value)
	{
		$value = (string) $value;
		$this->setValue($value);

		if ($value == null && $this->optional)
		{
			return true;
		}

		$locale = localeconv();

        $valueFiltered = str_replace($locale['thousands_sep'], '', $value);
        $valueFiltered = str_replace($locale['decimal_point'], '.', $valueFiltered);

		if ((string) ((float) $valueFiltered) != $valueFiltered)
		{
			$this->setMessage(self::NOT_FLOAT);
		}
		if ($valueFiltered < $this->min)
		{
			$this->setMessage(self::LESS_THAN);
		}
		else if ($this->max != null && $valueFiltered > $this->max)
		{
			$this->setMessage(self::GREATER_THAN);
		}

		return ! $this->isMessages();
	}
}
?>