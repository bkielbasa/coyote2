<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class UserErrorException extends Exception
{
	function __construct($message, $code = 0, $throw = true)
	{
		parent::__construct($message, $code);

		if ($throw)
		{
			$this->message();
			exit;
		}
	}

	function message()
	{
		Core::getInstance()->load->helper('box');

		Box::information(__('Błąd'), $this->getMessage());
	}

	public static function __($trigger_arr)
	{
		if (!is_array($trigger_arr))
		{
			$trigger_arr = array($trigger_arr);
		}

		foreach ($trigger_arr as $result)
		{
			if (is_string($result))
			{
				throw new UserErrorException($result);
			}
		}
	}
}

?>