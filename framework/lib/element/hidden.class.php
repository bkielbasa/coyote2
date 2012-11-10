<?php

class Element_Hidden extends Element_Abstract implements IElement
{
	public function getXhtml()
	{ 
		return Form::hidden($this->name, $this->value, $this->attributes);
	}

	public function display()
	{
		return $this->getXhtml();
	}
}
?>