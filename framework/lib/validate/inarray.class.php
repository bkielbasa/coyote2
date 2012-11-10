<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Walidator sprawdza, czy dana wartosc znajduje sie w tablicy
 * @todo Zmiana nawy tego pliku na inArray.class.php
 */
class Validate_InArray extends Validate_Abstract implements IValidate
{
	const NOT_IN_ARRAY						= 1;
	
	protected $templates = array(
			self::NOT_IN_ARRAY				=> 'Wartość "%value%" nie znajduje się w zbiorze danych'
	);
	
	function __construct(array $array = array())
	{
		if ($array)
		{
			$this->setArray($array);
		}
	}
	
	public function setArray(array $array)
	{
		$this->array = $array;
		return $this;
	}
	
	public function getArray()
	{
		return $this->array;
	}
	
	/**
	 * Walidacja
	 * @param $value
	 * @return bool
	 */
	public function isValid($value)
	{
		$this->setValue($value);
		
		if (in_array($value, $this->array))
		{
			return true;
		}
		else
		{
			$this->setError(self::NOT_IN_ARRAY);
		}
		
		return ! $this->hasErrors();		
	}
	
}
?>