<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Element_Submit extends Form_Element_Abstract implements IElement
{
	public function setUserValue($value)
	{
		return $this;
	}

	public function getXhtml()
	{ 
		return Form::submit($this->name, $this->value, $this->attributes);
	}
}
?>