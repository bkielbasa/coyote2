<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Element_Select extends Form_Element_Abstract implements IElement
{
	protected $options = array();
	protected $includeKey = true;
	protected $optionAttributes = array();

	public function addMultiOption($key, $value)
	{
		$this->options[$key] = $value;
		return $this;
	}

	public function addMultiOptions($options)
	{
		$this->options = array_merge($this->options, $options);
		return $this;
	}

	public function setMultiOptions($options)
	{
		$this->options = $options;
		return $this;
	}

	public function getMultiOptions()
	{
		return $this->options;
	}

	public function setOptionAttributes($options)
	{
		$this->optionAttributes = $options;
		return $this;
	}

	public function getXhtml()
	{ 
		if (is_array($this->value))
		{
			$this->setAttribute('multiple', 'multiple');
		} 

		return Form::select($this->name, Form::option($this->options, $this->value, $this->includeKey, $this->optionAttributes), $this->attributes);
	}
}
?>