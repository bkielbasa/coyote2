<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

abstract class Plugin extends Context
{
	private $itemId = 0;

	function __construct($args = array())
	{
		parent::__construct();
		
		if ($args)
		{
			foreach ($args as $arg => $value)
			{
				if (method_exists($this, 'set' . $arg))
				{
					$this->{'set' . $arg}($value);
				}
			}
		}

		return $this;
	}

	public function setItem($itemId)
	{
		$this->itemId = $itemId;
	}

	public function getItem()
	{
		return $this->itemId;
	}

	abstract public function display();

	public function __toString()
	{
		return $this->display();
	}
}
?>