<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Element_Button extends Form_Element_Abstract implements IElement
{
	public function getXhtml()
	{ 
		$this->setAttribute('name', $this->name);
		return Html::tag('button', true, $this->attributes, $this->value);
	}
}
?>