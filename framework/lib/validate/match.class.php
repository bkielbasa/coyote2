<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Walidator wyrazen regularnych
 */
class Validate_Match extends Validate_Abstract implements IValidate
{
	const NOT_MATCH = 1;

	protected $templates = array(

			self::NOT_MATCH			=> '"%value%" nie odpowiada wzorcowi "%pattern%"'

	);

	protected $vars = array(

			'pattern'				=> 'pattern'

	);

	protected $pattern;

	/**
	 * @param string $pattern Wzorzec regexp
	 */
	function __construct($pattern = '')
	{
		$this->setPattern($pattern);
	}

	/**
	 * Ustawia wrzorzec regexp
	 * @param string $pattern Wzorzec regexp
	 */
	public function setPattern($pattern)
	{
		$this->pattern = $pattern;
	}

	/**
	 * Walidacja danych
	 * @value 
	 * @return bool
	 */
	public function isValid($value)
	{
		$this->setValue($value);
		if (!@preg_match($this->pattern, $value))
		{
			return $this->setMessage(self::NOT_MATCH);
		}
		return true;
	}
}
?>