<?php

class Element_Password extends Element_Abstract implements IElement
{
	public function getXhtml()
	{ 
		return Form::password($this->name, $this->value, $this->attributes);
	}
}
?>