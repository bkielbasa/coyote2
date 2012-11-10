<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Walidator sprawdza, czy dane pole nie jest puste
 */
class Validate_NotEmpty extends Validate_Abstract implements IValidate
{
	const IS_EMPTY		=		1;

	protected $templates = array(

			self::IS_EMPTY				=> 'Pole nie może być puste'
	);

	/**
	 * Walidacja danych
	 * @value 
	 * @return bool
	 */
	public function isValid($value)
	{
		$this->setValue($value);
		
		if (is_string($value) && ('' === trim($value)))
		{
			$this->setMessage(self::IS_EMPTY);
		}
		elseif (is_string($value) && empty($value))
		{
			$this->setMessage(self::IS_EMPTY);
		}
		elseif (is_int($value) && (0 === $value))
		{
			$this->setMessage(self::IS_EMPTY);
		}
		elseif (is_float($value) && (0.0 === $value))
		{
			$this->setMessage(self::IS_EMPTY);
		}
		elseif (!is_string($value) && empty($value))
		{
			$this->setMessage(self::IS_EMPTY);
		}

		return ! $this->isMessages();
	}
}
?>