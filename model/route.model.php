<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Route_Model
{
	public function insert($data)
	{
		$xml = simplexml_load_file('config/route.xml');
		UserErrorException::__(Trigger::call('application.onRouteSubmit'));

		extract($data);

		$node = $xml->addChild('route');
		$node->addChild('name', $name);
		$node->addChild('url', $url);

		if (isset($host))
		{
			$node->addChild('host', $host);
		}
		if (isset($controller))
		{
			$node->addChild('controller', $controller);
		}
		if (isset($action))
		{
			$node->addChild('action', $action);
		}
		if (isset($folder))
		{
			$node->addChild('folder', $folder);
		}
		if (isset($connector))
		{
			$node->addChild('connector', $connector);
		}
		if (isset($page))
		{
			$node->addChild('page', $page);
		}
		if (!empty($suffix))
		{
			$node->addChild('suffix', $suffix);
		}
		if (isset($default))
		{
			$this->addDefault($node, $default);
		}
		if (isset($requirements))
		{
			$this->addRequirements($node, $requirements);
		}
		$node->addChild('order', sizeof($xml->route));
		
		return @file_put_contents('config/route.xml', $xml->asXml(), LOCK_EX);
	}
	
	public function isExists($route)
	{
		$xml = simplexml_load_file('config/route.xml');
		$result = false;
		
		foreach ($xml->route as $row)
		{
			if ($row->name == $route)
			{
				$result = true;
				break;
			}
		}
		
		return $result;
	}

	public function update($route, $data)
	{
		$xml = simplexml_load_file('config/route.xml');
		UserErrorException::__(Trigger::call('application.onRouteSubmit'));

		extract($data);

		foreach ($xml->route as $row)
		{
			if ($row->name == $route)
			{
				$row->name = $name;
				$row->url = $url;
				$row->controller = $controller;
				$row->action = $action;

				if ($host)
				{
					$row->host = $host;
				}
				else
				{
					unset($row->host);
				}

				if ($folder)
				{
					$row->folder = $folder;
				}
				else
				{
					unset($row->folder);
				}

				if ($connector)
				{
					$row->connector = $connector;
				}
				else
				{
					unset($row->connector);
				}
				if ($page)
				{
					$row->page = $page;
				}
				else
				{
					unset($row->page);
				}

				if ($default)
				{
					unset($row->default);
					$row = $this->addDefault($row, $default);
				}
				else
				{
					unset($row->default);
				}		
				if ($requirements)
				{
					unset($row->requirements);
					$row = $this->addRequirements($row, $requirements);
				}
				else
				{
					unset($row->requirements);
				}							
			}
		}

		return @file_put_contents('config/route.xml', $xml->asXml(), LOCK_EX);
	}

	public function delete($route)
	{
		$xml = simplexml_load_file('config/route.xml');
		$order = false;

		UserErrorException::__(Trigger::call('application.onRouteDelete'));
		
		for ($i = 0; $i < sizeof($xml->route); $i++)
		{
			if ($xml->route[$i]->name == $route)
			{
				$order = (int)$xml->route[$i]->order;
				unset($xml->route[$i]);
			}			
		}
		for ($i = 0; $i < sizeof($xml->route); $i++)
		{
			if ($xml->route[$i]->order > $order)
			{ 
				$xml->route[$i]->order = (int)$xml->route[$i]->order -1;
			}			
		}

		return @file_put_contents('config/route.xml', $xml->asXml(), LOCK_EX);
	}

	public function up($order)
	{
		$this->move($order, 'up');
	}

	public function down($order)
	{
		$this->move($order, 'down');
	}

	private function move($order, $mode)
	{
		$n_order = $mode == 'up' ? ($order - 1) : ($order + 1);
				
		$xml = simplexml_load_file('config/route.xml');
		$sizeof = sizeof($xml->route);

		for ($i = 0; $i < $sizeof; $i++)
		{
			if ($xml->route[$i]->order == $order)
			{		
				$n_index = $i;
			}
			if ($xml->route[$i]->order == $n_order)
			{
				$o_index = $i;
			}
		}
		if (isset($n_index) && isset($o_index))
		{
			$xml->route[$n_index]->order = $n_order;
			$xml->route[$o_index]->order = $order;

			file_put_contents('config/route.xml', $xml->asXml(), LOCK_EX);
		}
	}

	private function addDefault(&$xml, $default)
	{
		$node = $xml->addChild('default');
		foreach ($default as $key => $value)
		{
			$node->addChild($key, $value);
		}

		return $xml;
	}

	private function addRequirements(&$xml, $requirements)
	{
		$node = $xml->addChild('requirements');
		foreach ($requirements as $key => $value)
		{
			$node->addChild($key, $value);
		}

		return $xml;
	}
}
?>