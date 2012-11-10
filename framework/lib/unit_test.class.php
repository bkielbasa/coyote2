<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

 /**
  * Unit test class
  */
class Unit_test
{
	private $enabled = true;
	private $result = array();

	public function enabled()
	{
		$this->enabled = true;
	}

	public function disabled()
	{
		$this->enabled = false;
	}

	public function assertTrue($value, $name = '')
	{
		return $this->assert($value, true, $name);
	}

	public function assertFalse($value, $name = '')
	{
		return $this->assert($value, false, $name);
	}

	public function assertNull($value, $name = '')
	{
		return $this->assert($value, null, $name);
	}

	public function assertEqual($value, $result, $name = '')
	{
		return $this->assert($value, $result, $name);
	}

	public function assert($value, $result, $name = '')
	{
		if (!$this->enabled)
		{
			return;
		}
		$trace = debug_backtrace();

		$function = !isset($trace[1]['function']) ? 'Unknown' : $trace[1]['function'];
		$class = !isset($trace[1]['class']) ? 'Unknown' : $trace[1]['class'];

		if ($value != $result)
		{
			$return = 'Failed';
		}
		else
		{
			$return = 'Success';
		}
		$this->result[] = array(
			'name'			=> $name,
			'value'			=> var_export($value, true),
			'result'		=> var_export($result, true),
			'message'		=> $return,
			'return'		=> $return == 'Failed' ? false : true,
			'function'		=> $function,
			'class'			=> $class
		);
		return $return;
	}

	public function report()
	{
		foreach ($this->result as $row)
		{
			echo "
<h1>$row[name]</h1>
<p style='width: 90%; color: white; margin: 10px auto 10px auto; padding: 10px; background-color: " . ($row['return'] ? 'green' : 'red') . "'>$row[message] : result $row[value] (expected $row[result]) on class $row[class], method: $row[function]</p>
";
		}
	}
}
?>