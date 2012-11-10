<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Element_Radio extends Form_Element_Abstract implements IElement
{
	protected $options = array();
	/**
	 * Separator dla poszczegolnych elementow typu radio
	 */
	protected $separator = '<br />';

	public function setSeparator($separator)
	{
		$this->separator = $separator;
		return $this;
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