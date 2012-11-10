<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Walidator lancuchow danych
 */
class Validate_String extends Validate_Abstract implements IValidate
{
	const STRING_EMPTY		= 1;
	const NOT_STRING		= 2;
	const TOO_SHORT			= 3;
	const TOO_LONG			= 4;

	protected $optional;

	protected $min;
	protected $max;

	protected $templates = array(
			self::STRING_EMPTY			=> 'Podana wartość jest pusta',
			self::NOT_STRING			=> '"%value%" nie jest prawidłowym tekstem',
			self::TOO_SHORT				=> 'Wartość "%value%" nie może być krótsza niż %min% znaków',
			self::TOO_LONG				=> 'Wartość "%value%" nie może przekraczać %max% znaków'
	);

	protected $vars = array(
			'min'						=> 'min',
			'max'						=> 'max'
	);

	function __construct($optional = false, $min = 0, $max = null)
	{ 
		$this->setOptional($optional);
		$this->setMin($min);
		$this->setMax($max);
	}

	/**
	 * Ustawia minimalna dlugosc lancucha
	 * @param int $min
	 */
	public function setMin($min)
	{
		$this->min = $min;
	}

	/**
	 * Ustawia maksymalna mozliwa dlugosc lancucha
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
	 * Walidacja
	 * @param $value
	 * @return bool
	 */
	public function isValid($value)
	{
		$this->setValue($value);

		if (!is_string($value))
		{
			$this->setMessage(self::NOT_STRING);
		}
		$length = Text::length($value);

		if (!$length)
		{
			if ($this->optional)
			{
				return true;
			}
			else
			{
				return $this->setMessage(self::STRING_EMPTY);
			}
		}

		if ($length < $this->min)
		{
			$this->setMessage(self::TOO_SHORT);
		}
		else if ($this->max != null && $length > $this->max)
		{
			$this->setMessage(self::TOO_LONG);
		}
		
		return ! $this->isMessages();
	}
}
?>