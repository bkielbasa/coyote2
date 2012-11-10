<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa obslugi wyzwalaczy (hookow)
 */
class Trigger
{
	private static $trigger = array();

	public static function add($name, $callable)
	{
		self::$trigger[$name][] = $callable;
	}
	
	/**
	 * Zaladowanie konfiguracji triggerow z pliku konfiguracyjnego
	 */
	public static function load()
	{
		self::$trigger = array_merge(self::$trigger, (array)Config::getItem('trigger'));
	}

	public static function call()
	{ 
		if (!Config::getItem('core.trigger'))
		{
			return;
		}
		$arg = func_get_args();
		$point = array_shift($arg);

		if (!self::$trigger)
		{ 
			self::load();			
		}

		if (!isset(self::$trigger[$point]))
		{ 
			return;
		}

		$data_arr = self::$trigger[$point];
		if (!is_array(@$data_arr[0]))
		{
			$data_arr = array($data_arr);
		} 
		$return = array();

		foreach ($data_arr as $data)
		{	
			$data = (array)$data; 
			if (!$data)
			{
				continue;
			}

			$data['params'] = array_merge($arg, !empty($data['params']) ? (array) $data['params'] : array());
			
			if (!empty($data['path']))
			{
				include_once($data['path']);
			}
			if (!empty($data['class']))
			{		
				if (!@$data['function'])
				{ 
					$data['function'] = $data['class'];
				}
				$return[] = call_user_func_array(array(&$data['class'], $data['function']), $data['params']);				
			}
			else
			{
				if (isset($data['function']))
				{
					$return[] = call_user_func_array($data['function'], $data['params']);
				}
			}		
			if (isset($data['eval']))
			{
				$return[] = eval($data['eval']);
			}
			// dodaj informacje do dziennika
			Log::add("Trigger $point called", E_DEBUG);

			// ponizsza instrukcje zabezpiecza przed zapetleniem programu poprzez ciagle wyzwalanie triggera
			if ($point != 'system.onTriggerCall')
			{
				Trigger::call('system.onTriggerCall', $point, $data);
			}
		}

		if ($return)
		{ 
			if (count($return) == 1)
			{
				return $return[0];
			}
			return $return;
		}
	}
}
?>