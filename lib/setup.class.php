<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa zawierajaca podstawowe metody konfiguracji modulu/pluginu
 */
class Setup extends Context
{
	public function begin()
	{
		$this->db->begin();
	}

	public function commit()
	{
		$this->db->commit();
	}

	public function rollback()
	{
		$this->db->rollback();
	}

	public function importSqlFile($fileName)
	{
		Sql::import($fileName);
	}

	public function deleteTable($tableName)
	{
		$this->db->query('DROP TABLE IF EXISTS `' . $tableName . '`');
	}

	public function deleteView($viewName)
	{
		$this->db->query('DROP VIEW IF EXISTS `' . $viewName . '`');
	}

	public function deleteProcedure($procedureName)
	{
		$this->db->query('DROP PROCEDURE IF EXISTS `' . $procedureName . '`');
	}

	public function deleteFunction($functionName)
	{
		$this->db->query('DROP FUNCTION IF EXISTS `' . $functionName . '`');
	}

	public function importTrigger($triggerName)
	{
		$triggerName = (string)$triggerName;

		$this->db->insert('`trigger`', array('trigger_name' => $triggerName));
		$triggerId = $this->db->nextId();

		UserErrorException::__(Trigger::call('application.onTriggerSubmit', $triggerId, array('name' => $triggerName)));
	}

	public function deleteTrigger($triggerName)
	{
		$triggerName = (string)$triggerName;

		UserErrorException::__(Trigger::call('application.onTriggerDelete', $triggerName));
		$this->db->delete('`trigger`', 'trigger_name = "' . $triggerName . '" AND trigger_type != ' . Trigger_Model::SYSTEM);
	}

	public function importEvent($triggerName, &$data)
	{
		$triggerName = (string)$triggerName;
		$this->load->helper('array');

		$triggerId = $this->db->select('trigger_id')->where('trigger_name = "' . $triggerName . '"')->get('`trigger`')->fetchField('trigger_id');
		if (!$triggerId)
		{
			throw new Exception("Trigger $triggerName nie istnieje!");
		}
		$data += array('trigger' => $triggerId);

		$this->db->insert('`trigger_event`', array_key_pad($data, 'event_'));
		$eventId = $this->db->nextId();

		UserErrorException::__(Trigger::call('application.onEventSubmit', $eventId, $triggerName, $data));
	}

	public function deleteEvent($triggerName, &$data)
	{
		$this->db->delete('trigger_event', 'event_name = "' . $data['name'] . '" AND event_class = "' . $data['class'] . '"');
		UserErrorException::__(Trigger::call('application.onEventDelete', $triggerName, array($data['name']))); 
	}

	public function importAuth($value, $text, $default)
	{
		$this->db->insert('auth_option', array(
			'option_text'			=> $value,
			'option_label'			=> $text,
			'option_default'		=> $default
			)
		);
	}

	public function deleteAuth($value)
	{
		$this->db->delete('auth_option', 'option_text = "' . $value . '"');
	}

	public function importConnector($moduleId, $name, $text, $class, $controller, $action, $folder)
	{
		$this->db->insert('connector', array(
			'connector_module'		=> $moduleId,
			'connector_name'		=> $name,
			'connector_text'		=> $text,
			'connector_class'		=> $class,
			'connector_controller'	=> $controller,
			'connector_action'		=> $action,
			'connector_folder'		=> $folder
			)
		);
	}

	public function importRoute($name, $url, $controller, $action, $default = array(), $requirements = array())
	{
		$route = &$this->load->model('route');
		$route->insert(array(
			'name'			=> $name,
			'url'			=> $url,
			'controller'	=> $controller,
			'action'		=> $action,
			'default'		=> $default,
			'requirements'	=> $requirements
			)
		);
	}

	public function deleteRoute($name)
	{
		$route = &$this->load->model('route');
		$route->delete($name);
	}

	public function importSnippet($name, $class, $text = '')
	{
		$snippet = &$this->load->model('snippet');
		$snippet->insert(array(
			'snippet_name'			=> $name,
			'snippet_class'			=> $class,
			'snippet_text'			=> $text,
			'snippet_user'			=> User::$id,
			'snippet_time'			=> time()
			)
		);
	}

	public function deleteSnippet($name)
	{
		$snippet = &$this->load->model('snippet');
		$snippet->delete('snippet_name = "' . $name . '"');
	}

	public function addModule($name, $text = '', $version = '')
	{
		if (!is_writeable('config/module.xml'))
		{
			throw new Exception('Plik module.xml nie posiada praw zapisu!');
		}
		if (!file_exists("module/$name"))
		{
			throw new Exception("Nie mozna odnaleźć katalogu $name");
		}
		$module = &$this->load->model('module');

		$data = array(
			'module_name'		=> $name,
			'module_text'		=> $text,
			'module_version'	=> $version,
			'module_type'		=> Module_Model::NORMAL
		);
		$module->insert($data);
		$id = $this->db->nextId();

		$xml = simplexml_load_file('config/module.xml');
		$xml->addChild('module', $name);

		file_put_contents('config/module.xml', $xml->asXml());
		UserErrorException::__(Trigger::call('application.onModuleInstall', $name));

		return $id;
	}

	public function importConfig($moduleId, $xml)
	{
		$component = &$this->load->model('component');
		$query = $component->select()->get();
		$components = array();

		foreach ($query as $row)
		{
			$components[$row['component_name']] = $row['component_id'];
		}
		$field = &$this->load->model('field');

		foreach ($xml->option as $object)
		{
			$componentId = $components[(string)$object->attributes()->type];
			if (!$componentId)
			{
				continue;
			}
			$sql['name'] = $sql['value'] = array();

			$name = (string)$object->name;
			$auth = (string)$object->auth;
			$text = (string)$object->text;
			$description = (string)$object->title;
			$default = (string)$object->values->attributes()->default;

			$field->insert(array(
				'field_module'			=> $moduleId,
				'field_component'		=> $componentId,
				'field_name'			=> $name,
				'field_text'			=> $text,
				'field_description'		=> $description,
				'field_auth'			=> $auth,
				'field_default'			=> $default
				)
			);
			$fieldId = $this->db->nextId();			
			
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

		
		if (isset($xml->setup->connector))
		{
			foreach ($xml->setup->connector as $row)
			{
				$this->importConnector($moduleId, (string)$row->name, (string)$row->text, (string)$row->{'class'}, (string)$row->controller, (string)$row->action, (string)$row->folder);
			}
		}
		
		if (isset($xml->setup->notify))
		{
			foreach ($xml->setup->notify as $notify)
			{
				$emailId = null;
				
				if ($notify->email)
				{
					$emailId = $this->importEmail($notify->email->name, $notify->email->description, $notify->email->subject, $notify->email->text, $notify->email->format);
				}
				
				$default = $notify->default == 'true' ? true : false;
				$moduleId = null;
				$pluginId = null;
				
				if ($notify->module)
				{
					$moduleId = $this->db->select('module_id')->from('module')->where('module_name = ?', (string) $notify->module)->fetchField('module_id');
				}
				if ($notify->plugin)
				{
					$pluginId = (int) $this->module->getPluginId((string) $notify->plugin);
				}
				
				$this->importNotify($notify->name, $notify->message, $notify->trigger, $default, $emailId, $moduleId, $pluginId, @$notify->class);
			}
		}
	}

	public function deleteModule($name)
	{
		$module = &$this->load->model('module');
		$result = $module->getByName($name)->fetchAssoc();

		if (!$result)
		{
			throw new Exception("Moduł $name nie istnieje!");
		}
		if ($result['module_type'] == Module_Model::SYSTEM)
		{
			throw new Exception('Nie można usunąc modułu systemowego');
		}

		$module->delete('module_id = ' . $result['module_id'] . ' AND module_type != ' . Module_Model::SYSTEM);
		$xml = simplexml_load_file('config/module.xml');
		unset($xml->module);

		$query = $module->select('module_name')->where('module_name != "main"')->get();

		foreach ($query->fetch() as $row)
		{
			$xml->addChild('module', $row['module_name']);
		}

		file_put_contents('config/module.xml', $xml->asXml());
		UserErrorException::__(Trigger::call('application.onModuleUninstall', $result['module_name']));
	}

	public function addPlugin($name, $text = '', $version = '')
	{
		$this->db->insert('plugin', array('plugin_name' => $name, 'plugin_text' => $text, 'plugin_version' => $version));
		return $this->db->nextId();
	}

	public function deletePlugin($name)
	{
		$this->db->delete('plugin', 'plugin_name = "' . $name . '"');
	}

	public function importMenu($parent = null, $controller, $action, $name, $auth = 'a_')
	{
		$menu = &$this->load->model('adm/adm_menu');

		$data = array(
			'menu_controller'		=> (string)$controller,
			'menu_action'			=> (string)$action,
			'menu_auth'				=> (string)$auth,
			'menu_text'				=> (string)$name,
			'menu_parent'			=> 0
		);
		
		if ($parent)
		{
			if ($menuId = $menu->select('menu_id')->where('menu_controller = "' . $parent . '" AND menu_parent = 0')->get()->fetchField('menu_id'))
			{ 
				$data['menu_parent'] = $menuId;										
			}
		}			
		$menu->insert($data);
	}

	public function deleteMenu($controller, $action, $name)
	{
		$menu = &$this->load->model('adm/adm_menu');
		$menu->delete('menu_controller = "' . (string)$controller . '" AND menu_action = "' . (string)$action . '" AND menu_text = "' . (string)$name . '"');
	}
	
	public function importEmail($name, $description, $subject, $text, $format = 'plain')
	{		
		$this->db->insert('email', array(
			'email_name'			=> (string) $name,
			'email_description'		=> (string) $description,
			'email_subject'			=> (string) $subject,
			'email_text'			=> (string) $text,
			'email_format'			=> (int) (strval($format) == 'plain' ? 1 : 2)
			)
		);
		
		return $this->db->nextId();
	}
	public function deleteEmail($name)
	{
		$this->db->delete('email', 'email_name = "' . (string) $name . '"');
	}
	
	public function importNotify($name, $message, $trigger, $default = true, $email = null, $module = null, $plugin = null, $class = null)
	{
		$this->db->insert('notify', array(
			'notify_trigger'		=> (string) $trigger,
			'notify_class'          => (string) $class,
			'notify_module'			=> (int) $module,
			'notify_plugin'			=> intval($plugin) ? (int) $plugin : null,
			'notify_email'			=> intval($email) ? (int) $email : null,
			'notify_name'			=> (string) $name,
			'notify_message'		=> (string) $message,
			'notify_default'		=> (bool) $default
			)
		);
		
		return $this->db->nextId();
	}
	
	public function deleteNotify($name)
	{
		$this->db->delete('notify', 'notify_name = "' . $name . '"');
	}

	public function setupInstaller(&$xml)
	{
		$attr = $xml->attributes();
		if (!isset($attr['type']))
		{
			throw new Exception('Attribute "type" is empty (in XML file)');
		}

		$this->begin();	

		try
		{
			foreach ((array)$xml->setup->schema as $schema)
			{
				$this->importSqlFile((string)$attr['type'] . '/' . $xml->name . '/' . $schema);
			}

			if (isset($xml->setup->trigger))
			{
				foreach ($xml->setup->trigger as $triggerName)
				{
					$this->importTrigger((string) $triggerName);
				}
			}
			
			if (isset($xml->setup->event))
			{
				foreach ($xml->setup->event as $data)
				{
					$data = (array)$data;
					$triggerName = $data['@attributes']['trigger'];
	
					unset($data['@attributes']);
					$this->importEvent($triggerName, $data);
				}
			}
			
			foreach ($xml->menu as $row)
			{
				$parent = null;
				$row = (array)$row;
				
				if (isset($row['@attributes']['parent']))
				{
					$parent = $row['@attributes']['parent'];
					unset($row['@attributes']);
				}

				$this->importMenu($parent, $row['controller'], $row['action'], $row['name'], @$row['auth']);
			}

			if (isset($xml->setup->auth))
			{
				foreach ($xml->setup->auth as $row)
				{
					$text = (string)$row;
					$row = (array)$row;

					$value = $row['@attributes']['value'];
					$default = $row['@attributes']['default'];

					$this->importAuth($value, $text, $default);
				}
			}

			if (isset($xml->setup->route))
			{
				foreach ($xml->setup->route as $row)
				{
					$row = (array) $row;
					$this->importRoute($row['name'], $row['url'], @$row['controller'], @$row['action'], @$row['default'], @$row['requirements']);
				}
			}

			if (isset($xml->setup->snippet))
			{
				foreach ($xml->setup->snippet as $row)
				{
					$row = (array) $row;
					$this->importSnippet($row['name'], $row['class'], $row['text']);
				}
			}

			$this->commit();

			$this->cache->destroy();
			$this->cache->destroy('_acl');
		}
		catch (Exception $e)
		{
			$this->rollback();
			
			return $e->getMessage();
		}		

		return true;
	}

	public function setupUninstaller(&$xml, $deleteData = false)
	{
		$this->begin();

		try
		{
			if (isset($xml->setup->trigger))
			{
				foreach ($xml->setup->trigger as $triggerName)
				{
					$this->deleteTrigger($triggerName);
				}
			}
			
			if (isset($xml->setup->event))
			{
				foreach (@$xml->setup->event as $data)
				{
					$data = (array)$data;
					$triggerName = $data['@attributes']['trigger'];
	
					unset($data['@attributes']);
					$this->deleteEvent($triggerName, $data);
				}
			}
			
			if (isset($xml->menu))
			{
				foreach ($xml->menu as $row)
				{
					$row = (array)$row;
					$this->deleteMenu($row['controller'], $row['action'], $row['name'], @$row['auth']);
				}
			}
			
			if (isset($xml->setup->auth))
			{
				foreach ($xml->setup->auth as $row)
				{
					$row = (array)$row;
					$value = $row['@attributes']['value'];
	
					$this->deleteAuth($value);
				}
			}
			
			if (isset($xml->setup->notify))
			{
				foreach ($xml->setup->notify as $notify)
				{
					$this->deleteNotify($notify->name);
					
					if (isset($notify->email))
					{
						$this->deleteEmail($notify->email->name);
					}				
				}
			}

			if ($deleteData)
			{
				foreach (@$xml->setup->table as $table)
				{
					$this->deleteTable($table);
				}
				foreach (@$xml->setup->view as $view)
				{
					$this->deleteView($view);
				}
				foreach (@$xml->setup->procedure as $procedure)
				{
					$this->deleteProcedure($procedure);
				}
				foreach (@$xml->setup->function as $function)
				{
					$this->deleteFunction($function);
				}
			}

			if (isset($xml->setup->route))
			{
				foreach ($xml->setup->route as $row)
				{
					$row = (array)$row;
					$this->deleteRoute((string) $row['name']);
				}
			}

			if (isset($xml->setup->snippet))
			{
				foreach ($xml->setup->snippet as $row)
				{
					$row = (array)$row;
					$this->deleteSnippet((string) $row['name']);
				}
			}

			$this->commit();

			$this->cache->destroy();
			$this->cache->destroy('_acl');
		}
		catch (Exception $e)
		{
			$this->rollback();

			Log::add($e->getMessage(), E_ERROR);
			return $e->getMessage();
		}

		return true;
	}

}
?>