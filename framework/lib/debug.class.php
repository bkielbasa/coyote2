<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa prezentacji danych srodowiskowych
 */
class Debug
{
	/**
	 * Zmien ta stale na TRUE jezeli chcesz, aby wyswietlane byly informacje o klasach, metodach it[
	 * (mechanizm reflection)
	 */
	const REFLECTION = false;

	/**
	 * Metoda zwraca tablice zadeklarowanych stalych
	 * @return mixed
	 */
	private static function getDefined()
	{
		$constant = array();
		foreach (array_reverse(get_defined_constants()) as $type => $value)
		{ 	
			$constant[$type] = $value;
		}
		return $constant;
	}

	private static function reflection()
	{
		$class = array();
		if (!self::REFLECTION)
		{
			return $class;
		}

		foreach (array_reverse(get_declared_classes()) as $class_name)
		{
			$reflection = new ReflectionClass($class_name);

			$methods = $properties = array();
			foreach ($reflection->getMethods() as $method)
			{
				$methods[] = array(
					'name'			=> $method->getName(),
					'startLine'		=> $method->getStartLine(),
					'endLine'		=> $method->getEndLine(),
					'comment'		=> htmlspecialchars($method->getDocComment()),
					'element'		=> implode(' ',  Reflection::getModifierNames($method->getModifiers())) //self::element($method)
				);
			}
			foreach ($reflection->getProperties() as $property)
			{
				$properties[] = array(
					'name'			=> $property->getName(),
					'comment'		=> htmlspecialchars($property->getDocComment()),
					'element'		=> implode(' ',  Reflection::getModifierNames($property->getModifiers()))
				);
			}					
			$class[] = array(
				'name'				=> $class_name,
				'methods'			=> $methods,
				'properties'		=> $properties,
				'comment'			=> htmlspecialchars($reflection->getDocComment())
			);
		}
		return $class;
	}

	public static function explain()
	{  
		if (!defined('DEBUG') || !DEBUG)
		{
			return;
		} 
		/* usuwamy informacje o polaczeniach z baza danych */
		Config::removeItem('databases');
		$estimated = Benchmark::estimated();

		$core = &Core::getInstance();
		$view = $core->load->view('debug/debug');
		$i = 0;

		foreach (Log::$message as $arr)
		{
			list($type, $message, $time, $estimated) = $arr;
			{				
				++$i;
				$messages[] = array(
					'id'				=> '#' . $i,
					'type'				=> isset(Log::$type[$type]) ? Log::$type[$type] : '',
					'time'				=> $estimated,
					'message'			=> $message
				);
			}
		}
		$totalSqlTime = 0;

		if (class_exists('Db_Profiler', false))
		{ 
			$profiler = Db_Profiler::getInstance();

			if ($profiler->getTotalNumQueries())
			{ 
				$i = 0;
				foreach ($profiler->get() as $query)
				{	
					$view->append('sql', array(
						'id'		=> ++$i,
						'time'		=> $query->getElapsedTime(),
						'message'	=> $query->getQuery()
						)
					);

					$totalSqlTime += $query->getElapsedTime();
				}
			}
		}

		$view->assign(array(
			'memory_usage'		=>	sprintf('%.2f', memory_get_usage() / 1024 / 1024),
			'memory_usage_real'	=>  sprintf('%.2f', memory_get_usage(true) / 1024 / 1024),
			'memory_peak'		=>	sprintf('%.2f', memory_get_peak_usage(true) / 1024 / 1024),

			'estimated'			=> $estimated,

			'message'			=> $messages,
			'constant'			=> self::getDefined(),
			'config'			=> Config::getItem(),
			'reflection'		=> self::reflection(),

			'totalSqlTime'		=> $totalSqlTime
			)
		);

		echo $view;
	}

	
}
?>