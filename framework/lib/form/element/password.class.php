<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Element_Password extends Form_Element_Abstract implements IElement
{
	public function getXhtml()
	{ 
		return Form::password($this->name, $this->value, $this->attributes);
	}
}
?>