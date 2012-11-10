<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

abstract class Component_Abstract extends Context
{
	protected $items;
	protected $isDisplay;
	protected $isReadonly;

	abstract protected function displayLayout(&$fieldData);

	public function setItems($items)
	{
		$this->items = $items;
	}

	public function getItems()
	{
		return $this->items;
	}

	public function setDisplay($flag)
	{
		$this->isDisplay = (bool) $flag;
	}

	public function isDisplay()
	{
		return (bool)$this->isDisplay;
	}

	public function setReadonly($flag)
	{
		$this->isReadonly = (bool) $flag;
	}

	public function isReadonly()
	{
		return (bool)$this->isReadonly;
	}

	public function onSubmit($value)
	{
		return $value;
	}
}
?>