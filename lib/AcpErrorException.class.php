<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class AcpErrorException extends Exception
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
		core::getInstance()->load->helper('box');

		Box::information(__('Error'), $this->getMessage(), '', 'adm/information_box');
	}
}

?>