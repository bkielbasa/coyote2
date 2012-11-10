<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Route_Controller extends Adm
{
	function main()
	{ 
		if ($mode = $this->input->get('mode'))
		{
			if ($mode == 'up' || $mode == 'down')
			{
				$order = (int)$this->input->get('order');
				$router = &$this->getModel('route');

				$router->$mode($order);
				$this->redirect('adm/Route');
			}
		}

		$connector = &$this->getModel('connector');

		$this->connector = array('' => '(brak)');
		foreach ($connector->fetch() as $row)
		{
			$this->connector[$row['connector_id']] = $row['connector_text'];
		}
		
		return true;
	}

	public function submit($route = '')
	{
		$route = Filter::call($route, new Filter_Injection);
		if ($route)
		{
			$this->routes = $this->router->getRoutes();
			if (!isset($this->routes[$route]))
			{
				throw new AcpErrorException('Reguła o tej nazwie nie istnieje!');
			}
		}
		$this->filter = new Filter_Input;
		$this->folder = array('' => '');

		foreach (scandir('controller/') as $dir)
		{
			if ($dir{0} != '.')
			{
				if (is_dir('controller/' . $dir))
				{
					$this->folder[$dir] = $dir;
				}
			}
		}
		foreach (array_keys($this->module->getModules()) as $module)
		{
			foreach ((array)@scandir("module/$module/controller/") as $dir)
			{
				if ($dir{0} != '.')
				{
					if (is_dir("module/$module/controller/$dir"))
					{
						$this->folder[$dir] = $dir;
					}
				}
			}
		}

		$connector = &$this->getModel('connector');

		$this->connector = array('' => '(brak)');
		foreach ($connector->fetch() as $row)
		{
			$this->connector[$row['connector_id']] = $row['connector_text'];
		}

		if ($this->input->getMethod() == Input::POST)
		{
			$data['validator'] = array(

					'name'					=> array(
														array('string', false, 1, 100)
											),
					'url'					=> array(		
														array('string', false)
											),
					'controller'			=> array(
														array('string', true)
											),
					'action'				=> array(
														array('string', true)
											),
					'host'					=> array(
														array('string', true)
											),
					'folder'				=> array(
														array('string', true)
											)
			);
			$data['filter'] = array(

					'name'					=> array('injection' => array()),
					'url'					=> array('stripslashes'),
					'controller'			=> array('htmlspecialchars'),
					'action'				=> array('htmlspecialchars'),
					'host'					=> array('stripslashes')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				$default = $requirements = array();

				if ($this->input->post->default_c)
				{
					$default = array_combine($this->input->post->default['key'], $this->input->post->default['value']);					
				}
				if ($this->input->post->requirements_c)
				{
					$requirements = array_combine($this->post->requirements['key'], $this->input->post->requirements['value']);
				}
				unset($default[''], $requirements['']);

				$data = array(
					'name'			=> $this->post->name,
					'url'			=> $this->post->url,
					'controller'	=> $this->post->controller,
					'action'		=> $this->post->action,
					'folder'		=> $this->post->folder,
					'host'			=> $this->post->host,
					'connector'		=> $this->post->connector,
					'page'			=> $this->post->page,
					'default'		=> $default,
					'requirements'	=> $requirements
				);
				$this->load->model('route');

				if ($route)
				{					
					$this->model->route->update($route, $data);
				}
				else
				{
					$this->model->route->insert($data);
				}
				
				Box::information('Konfiguracja zapisana!', 'Konfiguracja routingu została prawidłowo zapisana do pliku XML!', url('adm/Route'), 'adm/information_box');
				exit;
			}
		}

		return View::getView('adm/routeSubmit', array(
			'name'		=> $route
			)
		);
	}

	public function delete($route)
	{
		$routes = (array)$this->router->getRoutes();
		if (!isset($routes[$route]))
		{
			throw new AcpErrorException('Reguła o tej nazwie nie istnieje!');
		}

		$this->load->model('route');
		$this->model->route->delete($route);
		
		Box::information('Konfiguracja zapisana', 'Konfiguracja routingu została prawidłowo zapisana do pliku XML!', url('adm/Route'), 'adm/information_box');
	}
}
?>