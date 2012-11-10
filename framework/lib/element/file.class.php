<?php

class Element_File extends Element_Abstract implements IElement
{
	protected $transfer;

	function __construct($name, $attributes = array())
	{
		parent::__construct($name, $attributes);

		$this->transfer = &Load::loadClass('upload');
	}

	public function __call($name, $args)
	{
		return call_user_func_array(array(&$this->transfer, $name), $args);
	}

	public function setValue($value)
	{
		return $this;
	}

	public function getXhtml()
	{ 
		return Form::file($this->name, $this->value, $this->attributes);
	}
}
?>