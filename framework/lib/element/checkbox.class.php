<?php

class Element_Checkbox extends Element_Abstract implements IElement
{
	protected $checkedValue = 1;
	protected $uncheckedValue = 0;
	protected $value = 1;
	protected $checked = false;
	protected $beforeText;
	protected $afterText;

	public function setChecked($checked = true)
	{
		$this->checked = $checked;
	}

	public function setValue($value)
	{
		if ($value == $this->checkedValue)
		{
			$this->checked = true;
			$this->value = $this->checkedValue;
		}
		else if ($value == $this->uncheckedValue)
		{
			$this->checked = false;
			$this->value = $this->uncheckedValue;
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