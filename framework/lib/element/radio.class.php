<?php

class Element_Radio extends Element_Abstract implements IElement
{
	protected $options = array();
	protected $separator = '<br />';

	public function setSeparator($separator)
	{
		$this->separator = $separator;
	}

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

	public function getXhtml()
	{ 
		if (!$this->options)
		{
			return;
		}
		$xhtml = '';

		foreach ($this->options as $key => $value)
		{
			$xhtml .= Form::radio($this->name, $key, ($key == $this->value), $this->attributes);
			$xhtml .= $value . $this->separator;
		}
		return $xhtml;
	}
}
?>