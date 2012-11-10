<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Walidacja adresu e-mail
 */
class Validate_Email extends Validate_Abstract implements IValidate
{
	const EMAIL_INVALID = 1;

	private $optional;

	protected $templates = array(

			self::EMAIL_INVALID			=> 'Adres "%value%" nie jest prawidłowym adresem e-mail'

	);
	
	/**
	 * @param bool $optional Okresla czy element jest opcjonalny (system nie zglosi bledu jezeli lancuch jest pusty)
	 */
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
	 * Walidacja danych
	 * @value Adres URL
	 * @return bool
	 */
	public function isValid($value)
	{
		$this->setValue($value);

		if (!$value && $this->optional)
		{
			return true;
		}
		if (!preg_match('#^[a-z0-9._+-]+?@(.*?\.)*?[a-z0-9_-]+?\.[a-z]{2,4}$#i', $value))
		{
			return $this->setMessage(self::EMAIL_INVALID);
		}
		return true;
	}
}
?>