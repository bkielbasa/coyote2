<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa na podstawie podanych parametrow wywoluje dany kontroler oraz akcje
 */
class Dispatcher 
{
	public static function dispatch($controller, $action, $folder = '', array $args = array())
	{  
		// wywolanie hookow
		Trigger::call('system.onBeforeController', array(&$controller, &$action, &$folder, &$args));
		$path = 'controller/' . ($folder ? $folder . '/' : '') . $controller . '.php';

		try
		{
			// nalezy sprawdzic, czy modul z klasa istnieje
			if (!Load::fileExists($path))
			{ 
				throw new FileNotFoundException($path);	
			} 	
			Load::loadFile($path); 
			$class = class_exists($controller . '_Controller', false) ? $controller . '_Controller' : $controller;

			Log::add("Controller $path loaded", E_DEBUG);

			// nalezy sprawdzic czy istnieje klasa kontrolera oraz zadana metoda
			if (!class_exists($class, false) ||
					(!in_array($action, get_class_methods($class))	&& 
					 !in_array('__call', get_class_methods($class))))
			{
				throw new FileNotFoundException('Action: ' . $action);	
			} 	
		}
		catch (FileNotFoundException $e)
		{ 
			$e->message();
			exit;
		} 
		// wywolanie konstrolera
		$controller = new $class; 		

		Trigger::call('system.onBeforeAction');

		if (method_exists($controller, '__start'))
		{
			Trigger::call('system.onBeforeStart');

			$controller->__start();

			Trigger::call('system.onAfterStart');
		}
		$result = call_user_func_array(array(&$controller, $action), $args);

		if ($result !== null && $result !== View::NONE)
		{
			if (is_object($result))
			{
				$view = &$result;				
			}
			else
			{
				if (is_string($result))
				{
					$template = ($folder ? $folder . '/' : '') . (string)$controller . $result;
				}
				else if ($result === true)
				{
					$template = ($folder ? $folder . '/' : '') . (string)$controller . ($action != 'main' ? ucfirst($action) : '');
				}
				else
				{
					$template = (string)$controller . View::MAIN;
				}
				$view = new View($template);				
			}
			$view->assign(get_object_vars($controller));

			echo $view;
		}
	}
}

?>