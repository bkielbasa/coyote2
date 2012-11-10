<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Plugin_Controller extends Adm
{
	function main()
	{
		$plugin = &$this->getModel('plugin');
		$this->plugins = array_diff_key($plugin->readdir(), $plugin->getPlugins());

		return true;
	}

	public function present()
	{
		$plugin = &$this->load->model('plugin'); 
		$this->plugins = $plugin->getPlugins();
		
		return true;
	}

	public function submit($id = 0)
	{
		$id = (int)$id;
		if (!$id)
		{
			throw new AcpErrorException('Wtyczka o podanym ID nie istnieje!');
		}
		$plugin = &$this->getModel('plugin');

		if (!$result = $plugin->find($id)->fetchAssoc())
		{
			throw new AcpErrorException('Wtyczka o podanym ID nie istnieje!');
		}
		$this->pluginEnable = array();

		$query = $this->db->select('module_id')->from('module_plugin')->where("plugin_id = $id")->get();
		while ($row = $query->fetchArray())
		{
			$this->pluginEnable[] = $row[0];
		}

		if ($this->input->isMethod(Input::POST))
		{
			$setup = new Setup;
			$module = &$this->getModel('module');

			$xml = simplexml_load_file('plugin/' . $result['plugin_name'] . '/' . $result['plugin_name'] . '.xml');
			$moduleIds = (array) $this->post->module;

			// do dodania w modulach
			foreach (array_diff($moduleIds, $this->pluginEnable) as $moduleId)
			{
				$setup->importConfig($moduleId, $xml);
				$module->plugin->insert(array('module_id' => $moduleId, 'plugin_id' => $id));
			}
			// do usuniecia w modulach
			foreach (array_diff($this->pluginEnable, $moduleIds) as $moduleId)
			{
				$fields = array();
				foreach ($xml->option as $object)
				{
					$fields[] = '"' . (string) $object->name . '"';
				}
				
				if ($fields)
				{
					$this->db->delete('field', 'field_module = ' . $moduleId . ' AND field_name IN(' . implode(',', $fields) . ')');
				}
				$module->plugin->delete("plugin_id = $id AND module_id = $moduleId");
			}

			Box::information('Zmiany zapisane', 'Zmiany zostały zapisane', '', 'adm/information_box');
			exit;
		}

		return true;

	}

	public function __call($plugin, $args)
	{
		Load::setIncludePath('plugin/' . $plugin);		
		if (!$action = array_shift($args))
		{
			$action = 'main';
		}
		
		return Dispatcher::dispatch($plugin, $action, 'adm', $args);
	}

	public function install($name = '')
	{
		if (!$name)
		{
			throw new AcpErrorException('Nieprawidłowe wywołanie programu!');
		}
		$plugin = &$this->getModel('plugin');

		if (count($plugin->getByName($name)))
		{
			throw new AcpErrorException('Plugin o tej nazwie jest już zainstalowany w systemie!');
		}

		if (!$result = $plugin->readData($name))
		{
			throw new AcpErrorException('Plugin o podanej nazwie nie istnieje w katalogu /plugin.');
		}

		if ($this->input->isMethod(Input::POST))
		{
			$xml = simplexml_load_file('plugin/' . $name . '/' . $name . '.xml');
			$setup = &Load::loadClass('setup');

			if (($setupData = $xml->setup->install))
			{
				Load::setIncludePath('plugin/' . $name);

				return Dispatcher::dispatch((string)$setupData->controller, 'main', (string)$setupData->folder);
			}
			else
			{
				if (($error = $setup->setupInstaller($xml)) === true)
				{
					$setup->addPlugin($name, (string)$xml->text, (string)$xml->version);					

					Box::information('Wtyczka zainstalowana', "Wtyczka <i>$name</i> została poprawnie zainstalowana. Przejdź do strony konfiguracji wtyczki, aby właczyć ją w wybranych modułach", url('adm/Plugin'), 'adm/information_box');

					exit;
				}
				else
				{
					$this->error = $error;
					return 'Error';
				}
			}
		}

		return View::getView('adm/pluginInstall', $result);
	}

	public function uninstall($name = '')
	{
		if (!$name)
		{
			throw new AcpErrorException('Nieprawidłowe wywołanie programu!');
		}

		$plugin = &$this->getModel('plugin');
		$query = $plugin->getByName($name);

		if (!count($query))
		{
			throw new AcpErrorException('Plugin o tej nazwie nie jest zainstalowany w systemie!');
		}
		$pluginId = $query->fetchField('plugin_id');

		$query = $plugin->getModules($pluginId);
		if (count($query))
		{
			throw new AcpErrorException('Wtyczka jest używana przez przynajmniej jeden moduł. Przed usunięciem wtyczki, odinstaluj ją w poszczególnych modułach');
		}

		$xml = simplexml_load_file('plugin/' . $name . '/' . $name . '.xml');

		if ($xml->setup->table)
		{
			$this->tablesExists = true;
		}

		if ($this->input->isMethod(Input::POST))
		{
			$setup = &Load::loadClass('setup');

			if (($setupData = $xml->setup->uninstall))
			{
				return Dispatcher::dispatch((string)$setupData->controller, 'main', (string)$setupData->folder);
			}
			else
			{
				if ($setup->setupUninstaller($xml,  (bool)$this->post->delete))
				{					
					$setup->deletePlugin($name);

					Box::information('Wtyczka odinstalowana', "Wtyczka <i>$name</i> została poprawnie odinstalowana", url('adm/Plugin'), 'adm/information_box');

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
}
?>