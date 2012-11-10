<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Walidator numeru NIP
 */
class Validate_Nip extends Validate_Abstract implements IValidate
{
	const INVALID		= 1;

	protected $optional;

	protected $templates = array(
			self::INVALID				=> 'Numer NIP jest nieprawidłowy'
	);

	function __construct($optional = false)
	{ 
		$this->setOptional($optional);
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

		if (empty($value))
		{
			if ($this->optional)
			{
				return true;
			}
			else
			{
				return $this->setMessage(self::INVALID);
			}
		}

		$arr = array(6, 5, 7, 2, 3, 4, 5, 6, 7);
		$value = preg_replace('/[\s-]/', '', $value);

		$length = strlen($value);

        $sum = 0;
        if ($length == 10 && is_numeric($value)) 
		{	 
			for ($i = 0; $i <= 8; $i++)
			{
				$sum += $value[$i] * $arr[$i];
            }
            
			if ((($sum % 11) % 10) != $value[9])
			{
				$this->setMessage(self::INVALID);
			}
        }
		else
		{
			$this->setMessage(self::INVALID);
		}
		
		return ! $this->isMessages();
	}
}
?>
