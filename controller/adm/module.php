<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

set_time_limit(0);

class Module_Controller extends Adm
{
	function main()
	{ 
		$module = &$this->load->model('module'); 
		$this->modules = array_diff_key($module->readdir(), $this->module->getModules());

		return true;
	}

	public function module()
	{
		$module = &$this->load->model('module'); 
		$this->modules = $this->module->getModules();

		return true;
	}

	public function refresh($moduleId)
	{
		$moduleId = (int)$moduleId;
		$module = $this->module->getById($moduleId);
		if (!$module)
		{
			throw new AcpErrorException('Moduł o tym ID nie istnieje!');
		}

		if (!file_exists('module/' . $module['module_name'] . '/' . $module['module_name'] . '.xml'))
		{
			throw new AcpErrorException('Plik konfiguracyjny tego modułu nie istnieje!');
		}
		$xml = simplexml_load_file('module/' . $module['module_name'] . '/' . $module['module_name'] . '.xml');

		$data = array(
			'module_text'		=> (string)$xml->text,
			'module_version'	=> (string)$xml->version
		);
		$this->db->update('module', $data, "module_id = $moduleId");
		
		$field = &$this->getModel('field');
		$query = $field->select()->where("field_module = $moduleId")->get();

		foreach ($query as $row)
		{
			$fieldData[$row['field_name']] = $row;
		}

		$component = &$this->load->model('component');
		$query = $component->select()->get();
		$components = array();

		foreach ($query as $row)
		{
			$components[$row['component_name']] = $row['component_id'];
		}

		foreach ($xml->option as $object)
		{
			$componentId = $components[(string)$object->attributes()->type];
			if (!$componentId)
			{
				continue;
			}
			$name = (string)$object->name;
			$auth = (string)$object->auth;
			$text = (string)$object->text;
			$description = (string)$object->title;
			$default = (string)$object->values->attributes()->default;

			$data = array(
				'field_module'			=> $moduleId,
				'field_text'			=> $text,
				'field_description'		=> $description,
				'field_auth'			=> $auth,
				'field_default'			=> $default
			);

			if (!isset($fieldData[$name]))
			{
				$data['field_name'] = $name;
				$data['field_component'] = $componentId;
				$field->insert($data);

				$fieldId = $this->db->nextId();	
			}
			else
			{
				$fieldId = $fieldData[$name]['field_id'];
				$field->update($data, 'field_id = ' . $fieldId);
			}

			$sql['name'] = $sql['value'] = array();

			$values = $object->values;
			if (isset($values->value))
			{
				foreach ($values->value as $value)
				{
					$sql['name'][] = (string) $value->attributes()->id;
					$sql['value'][] = (string) $value[0];
				}
			}
			
			if ($sql['name'])
			{
				$field->item->setFieldItems($fieldId, $sql);
			}
		}
		
		$this->redirect('adm/Module/Submit/' . $moduleId);
	}

	public function install($name = '')
	{
		if (!Auth::get('a_module'))
		{
			throw new AcpErrorException('Brak uprawnień do instalacji modułu');
		}
		if (!$name)
		{
			throw new AcpErrorException('Nieprawidłowe wywołanie programu!');
		}

		if ($this->module->getModule($name) !== false)
		{
			throw new AcpErrorException('Moduł o tej nazwie jest już zainstalowany w systemie!');
		}

		$module = &$this->getModel('module');
		if (!$result = $module->readData($name))
		{
			throw new AcpErrorException('Moduł o podanej nazwie nie istnieje w katalogu /module.');
		}

		if ($this->input->isMethod(Input::POST))
		{
			$setup = &Load::loadClass('setup');
			$xml = simplexml_load_file('module/' . $name . '/' . $name . '.xml');

			if (($setupData = $xml->setup->install))
			{
				Load::loadModule($name);

				return Dispatcher::dispatch((string) $setupData->controller, (string) $setupData->action, (string) $setupData->folder);
			}
			else
			{
				if (($error = $setup->setupInstaller($xml)) === true)
				{
					$id = $setup->addModule($name, (string) $xml->text, (string) $xml->version);					
					$setup->importConfig($id, $xml);					

					Box::information('Moduł zainstalowany', "Moduł <i>$name</i> został poprawnie zainstalowany", url('adm/Module'), 'adm/information_box');

					exit;
				}
				else
				{
					$this->error = $error;
					return 'Error';
				}
			}
		}

		return View::getView('adm/moduleInstall', $result);
	}

	public function uninstall($name = '')
	{
		if (!Auth::get('a_module'))
		{
			throw new AcpErrorException('Brak uprawnień do usunięcia modułu');
		}
		if (!$name)
		{
			throw new AcpErrorException('Nieprawidłowe wywołanie programu!');
		}

		if ($this->module->getModule($name) == false)
		{
			throw new AcpErrorException('Moduł o tej nazwie nie jest zainstalowany w systemie!');
		}
		$xml = simplexml_load_file('module/' . $name . '/' . $name . '.xml');

		if ($xml->setup->table)
		{
			$this->tablesExists = true;
		}

		$page = &$this->getModel('page');
		if (0 < $page->select('COUNT(*) AS items')->where('page_module = ' . $this->module->getId($name))->get()->fetchField('items'))
		{
			throw new AcpErrorException('Pewne strony wciąż odnoszą się do tego modułu. Nie możesz usunąć modułu dopóki nie usuniesz stron z niego korzystających');
		}

		if ($this->input->isMethod(Input::POST))
		{
			$setup = &Load::loadClass('setup');

			if (($setupData = $xml->setup->uninstall))
			{
				return Dispatcher::dispatch((string) $setupData->controller, (string) $setupData->action, (string) $setupData->folder);
			}
			else
			{
				if (($error = $setup->setupUninstaller($xml,  (bool) $this->post->delete)) == true)
				{					
					$setup->deleteModule($name);

					Box::information('Moduł odinstalowany', "Moduł <i>$name</i> został poprawnie odinstalowany", url('adm/Module'), 'adm/information_box');

					exit;
				}
				else
				{
					return 'Error';
				}
			}
		}

		return true;
	}

	public function submit($id = 0)
	{
		if (!Auth::get('a_module'))
		{
			throw new AcpErrorException('Brak uprawnień do edycji modułu');
		}
		$id = (int)$id;
		if (!$id)
		{
			throw new AcpErrorException('Nie podano parametru ID');
		}

		$module = &$this->load->model('module');
		$result = array();

		if (!$result = $module->find($id)->fetchAssoc())
		{
			throw new AcpErrorException('Moduł o tym ID nie istnieje!');
		}

		if (file_exists('module/' . $result['module_name'] . '/' . $result['module_name'] . '.xml'))
		{
			$xml = simplexml_load_file('module/' . $result['module_name'] . '/' . $result['module_name'] . '.xml');
			$result = array_merge($result, (array)$xml);
		}		
		
		$field = &$this->getModel('field');
		$component = new Component;

		$this->form = null;
		$this->form = &$component->displayForm($id);

		$config = array();

		$config = $this->module->getModuleConfig($id);
		$this->form->setDefaults($config);
		$this->form->setEnableDefaultDecorators(false);

		if ($this->form->isValid())
		{	
			$module->config->setModuleConfig($id, null, $this->form->getValues());
			$this->message = 'Zmiany konfiguracji zostały zapisane';
		}		
		
		return View::getView('adm/moduleSubmit', $result);
	}
}
?>