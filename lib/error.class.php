<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Error extends Exception
{
	function __construct($httpCode, $message = '', $throw = true)
	{
		parent::__construct($message, $httpCode);

		if ($throw)
		{
			$this->message($httpCode, $message);
			exit;
		}
	}

	function message($httpCode, $message)
	{
		$query = Page::loadError($httpCode);

		if (count($query))
		{
			$data = $query->fetchAssoc();
			$className = 'Connector_' . $data['connector_class'];
			$connector = new $className($data);

			$page = new Page($connector);
			Core::getInstance()->page = &$page;

			if (method_exists($connector, 'addVar'))
			{
				$page->addVar('message', $this->getMessage());
			}
			Dispatcher::forward($page->getController(), $page->getAction(), $page->getFolder());
		}
		else
		{
			Box::information(__('Błąd'), $this->getMessage());
		}
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
				throw new Error($result);
			}
		}
	}
}

?>