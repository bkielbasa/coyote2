<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/** 
 * Element generujacy hash (element <input type="hidden"...
 */
class Form_Element_Hash extends Form_Element_Abstract implements IElement
{
	/**
	 * Okresla, czy domyslne dekoratory maja byc ladowane, czy tez nie
	 */
	protected $enableDefaultDecorators = false;

	/**
	 * @param string $name Nazwa pola 
	 * @param mixed $attributes Tablica atrybutow
	 * @param mixed $options Tablica dodatkowych opcji
	 */
	function __construct($name, $attributes = array(), $options = array())
	{
		parent::__construct($name, $attributes, $options);

		$this->setRequired(true);
		$this->initValidator();
	}

	protected function initValidator()
	{
		$session = &Load::loadClass('session');
		$key = $this->getName();

		if (isset($session->$key))
		{
			$hash = $session->$key;
		}
		else
		{
			$hash = null;
		}

		$this->addValidator('equal', array($hash));
	}

	public function getXhtml()
	{
		$key = $this->getName();
		
		$hash = md5(uniqid(rand(), true));
		$session = &Load::loadClass('session');
		$session->$key = $hash;

		return Form::hidden($this->getName(), $hash);
	}
}
?>