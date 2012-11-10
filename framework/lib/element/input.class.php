<?php

class Element_Textarea extends Element_Abstract implements IElement
{
	public function getXhtml()
	{ 
		return Form::textarea($this->name, $this->value, $this->attributes);
	}
}
?>