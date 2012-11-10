<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Element_Multicheckbox extends Form_Element_Abstract implements IElement
{
	protected $multiOptions = array();
	protected $value = array();
	protected $separator = ' <br />';

	public function setSeparator($separator)
	{
		$this->separator = $separator;
		return $this;
	}

	public function getSeparator()
	{
		return $this->separator;
	}

	public function addMultiOption($key, $value)
	{
		$this->multiOptions[$key] = $value;
		return $this;
	}

	public function addMultiOptions($multiOptions)
	{
		$this->multiOptions = array_merge($this->multiOptions, $options);
		return $this;
	}

	public function setMultiOptions($multiOptions)
	{
		$this->multiOptions = $multiOptions;
		return $this;
	}

	public function setValue($value)
	{
		if (is_string($value) && strpos($value, ',') !== false)
		{
			$value = explode(',', $value);
		}

		$this->value = (array) $value;
		return $this;
	}

	public function getXhtml()
	{ 
		if (!$this->multiOptions)
		{
			return;
		}
		$xhtml = '';

		foreach ($this->multiOptions as $key => $value)
		{
			$xhtml .= Form::checkbox($this->name . '[]', $key, (bool) in_array($key, $this->value), $this->attributes);
			$xhtml .= ' ' . $value . $this->separator;
		}
		return $xhtml;
	}
}
?>