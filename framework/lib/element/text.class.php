<?php

class Element_Text extends Element_Abstract implements IElement
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