<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

abstract class Snippet extends Context
{
	function __construct(array $config = array())
	{
		if ($config)
		{
			$this->setConfig($config);
		}				
	}

	public function setConfig(array $config = array())
	{
		foreach ($config as $name => $value)
		{
			$this->$name = $value;
		}
	}

	public function display(IView $instance = null)
	{
	}

	public function __toString()
	{
		return (string)$this->display();
	}
}
?>