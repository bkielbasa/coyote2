<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Element_Checkbox extends Form_Element_Abstract implements IElement
{
	protected $checkedValue = 1;
	protected $uncheckedValue = 0;
	protected $value = 1;
	protected $checked = false;
	protected $beforeText;
	protected $afterText;

	public function setChecked($flag = true)
	{
		$this->checked = (bool) $flag;
		
		if ($this->checked)
		{
			$this->setValue($this->getCheckedValue());
		}
		else
		{
			$this->setValue($this->getUncheckedValue());
		}
		
		return $this;
	}
	
	public function isChecked()
	{
		return $this->checked;
	}
	
	public function setCheckedValue($value)
	{
		$this->checkedValue = (string) $value;
		return $this;
	}
	
	public function getCheckedValue()
	{
		return $this->checkedValue;
	}
	
	public function setUncheckedValue($value)
	{
		$this->uncheckedValue = (string) $value;
		return $this;
	}
	
	public function getUncheckedValue()
	{
		return $this->uncheckedValue;
	}	

	public function setValue($value)
	{
		if ($value == $this->checkedValue)
		{
			$this->checked = true;
			parent::setValue($value);
		}
		else if ($value == $this->uncheckedValue)
		{
			$this->checked = false;
			parent::setValue($this->uncheckedValue);
		}
		return $this;
	}

	public function addAfterText($afterText)
	{
		$this->afterText = $afterText;
		return $this;
	}

	public function addBeforeText($beforeText)
	{
		$this->beforeText = $beforeText;
		return $this;
	}

	public function getXhtml()
	{ 
		$xhtml = $this->beforeText;

		$xhtml .= Form::checkbox($this->name, $this->checkedValue, $this->checked, $this->attributes);
		$xhtml .= $this->afterText;

		return $xhtml;
	}
}
?>