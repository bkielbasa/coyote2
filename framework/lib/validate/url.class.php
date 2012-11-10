<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Walidator adresow URL
 */
class Validate_Url extends Validate_Abstract implements IValidate
{
	const NOT_URL = 1;

	protected $templates = array(

			self::NOT_URL				=> '"%value%" nie jest prawidłowym adresem URL'

	);

	protected $optional;

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
		if (!$value && $this->optional)
		{
			return true;
		}
		$this->setValue($value);

		/* sprawdzenie poprawnosci adresu	url	*/
		if (!preg_match('#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#', $value))
		{
			return $this->setMessage(self::NOT_URL);			
		}
		return true;
	}
}
?>