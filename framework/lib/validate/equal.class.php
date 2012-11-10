<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Walidator porownujacy dwie wartosci
 */
class Validate_Equal extends Validate_Abstract implements IValidate
{
	const NOT_EQUAL		=		1;

	protected $templates = array(

			self::NOT_EQUAL		=> 'Wartość "%value%" musi być taka sama jak "%token%"'
	);

	protected $vars = array(

			'token'				=> 'token'
	);
	protected $token;

	function __construct($token = '')
	{
		$this->setToken($token);
	}

	public function setToken($token)
	{
		$this->token = $token;
	}

	public function getToken()
	{
		return $this->token;
	}

	public function isValid($value)
	{
		$this->setValue($value);

		if ((string)$value !== (string)$this->token)
		{
			$this->setMessage(self::NOT_EQUAL);
		}

		return ! $this->isMessages();
	}
}
?>