<?php

class Element_Submit extends Element_Abstract implements IElement
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