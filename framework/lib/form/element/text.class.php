<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Element_Text extends Form_Element_Abstract implements IElement
{
	function __construct($name, $attributes = array(), $options = array())
	{
		parent::__construct($name, $attributes, $options);

		$this->setAttribute('type', 'text');
	}

	public function getXhtml()
	{ 
		return Form::input($this->name, $this->value, $this->attributes);
	}
}
?>