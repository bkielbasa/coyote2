<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Element_File extends Form_Element_Abstract implements IElement
{
	protected $transfer;

	function __construct($name, $attributes = array(), $options = array())
	{
		parent::__construct($name, $attributes = array(), $options = array());
		$this->transfer = &Load::loadClass('upload');
	}

	/**
	 * Kazde wywolanie metody powoduje wywolanie metody klasy Upload
	 */
	public function __call($name, $args)
	{
		return call_user_func_array(array(&$this->transfer, $name), $args);
	}

	/**
	 * Do elementu nie mozna przypisac wartosci
	 */
	public function setValue($value)
	{
		return $this;
	}

	/**
	 * Do elementu nie mozna przypisac wartosci
	 */
	public function setUserValue($value)
	{
		return $this;
	}

	public function getXhtml()
	{ 
		return Form::file($this->name, $this->value, $this->attributes);
	}
}
?>