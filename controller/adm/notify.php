<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Notify_Controller extends Adm
{
	function main()
	{
		$notify = &$this->getModel('notify');
		load_helper('array');
		
		if ($this->input->isPost())
		{
			$delete = $this->post->delete;
			if ($delete)
			{
				$notify->delete('notify_id IN(' . implode(',', $delete) . ')');
			}

			$this->session->message = 'Zaznaczone rekordy zostały usunięte';
		}
		
		$this->notify = $notify->fetchAll();
		return true;
	}
	
	public function submit($id = 0)
	{
		$id = (int) $id;
		$result = array();
		
		$notify = &$this->getModel('notify');
		if ($id)
		{
			if (!$result = $notify->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Powiadomienie o tym ID nie istnieje!');
			}
		}
		
		$this->modules = array(0 => '--');
		$this->modules += $this->db->select('module_id, module_text')->from('module')->fetchPairs();
		
		$this->plugins = array(0 => '--');
		$this->plugins += $this->db->select('plugin_id, plugin_text')->from('plugin')->fetchPairs();
		
		$this->triggers = array(0 => '--');
		$this->triggers += $this->db->select('trigger_name, trigger_name')->from('`trigger`')->fetchPairs();
				
		$this->email = array(0 => '--');
		$query = $this->db->select('email_id, email_name, email_description')->get('email');
		
		foreach ($query as $row)
		{
			$this->email[$row['email_id']] = sprintf('%s [%s]', $row['email_name'], $row['email_description']);
		}

		$notifyClass = $this->loadClassInfo('lib/notify/');
		foreach ($this->module->getModules() as $module)
		{
			$notifyClass = array_merge($notifyClass, $this->loadClassInfo('module/' . $module['module_name'] . '/lib/notify'));
		}

		$this->notifyClass = array_combine(array_values($notifyClass), array_values($notifyClass));
		$this->loadClassInfo('lib/notify');
		
		$this->filter = new Filter_Input;
		
		if ($this->input->isPost())
		{
			$data['validator'] = array(
				
				'name'					=> array(
			
													array('notempty')
										),
				'message'				=> array(
													array('notempty')
										)
			);
			
			$data['filter'] = array(
			
				'name'					=> array('trim', 'htmlspecialchars'),
				'trigger'				=> array('htmlspecialchars'),
				'class'                 => array('htmlspecialchars'),
				'module'				=> array('int'),
				'plugin'				=> array('int'),
				'default'				=> array('int'),
				'email'					=> array('int'),
				'message'				=> array('trim')
			);
			$this->filter->setRules($data);
			
			if ($this->filter->isValid($_POST))
			{
				load_helper('array');
				$data = array_key_pad($this->filter->getValues(), 'notify_');
				
				if (!$data['notify_plugin'])
				{
					$data['notify_plugin'] = null;
				}
				
				if (!$id)
				{
					$notify->insert($data);
				}
				else
				{
					$notify->update($data, "notify_id = $id");
				}
				
				$this->session->message = 'Konfiguracja powiadomień została zapisana';
				$this->redirect('adm/Notify');
			}
		}
		
		return View::getView('adm/notifySubmit', $result);	
	}

	private function loadClassInfo($directory)
	{
		$result = array();

		if (file_exists($directory))
		{
			foreach (scandir($directory) as $file)
			{
				if ($file != '.' && $file != '..')
				{
					if (pathinfo($file, PATHINFO_EXTENSION) == 'php')
					{
						if (($className = $this->getPhpClass($directory . '/' . $file)) !== false)
						{
							$reflection = new ReflectionClass($className);
							if (!$reflection->isAbstract() && $reflection->isSubclassOf('Notify_Abstract'))
							{
								$result[] = $reflection->getName();
							}
						}
					}
					else
					{
//						if (is_dir($file))
						{
							$result = array_merge($result, $this->loadClassInfo($directory . '/' . $file));
						}
					}
				}
			}
		}

		return $result;
	}

	private function getPhpClass($path)
	{
		$className = false;
		$tokens = token_get_all(file_get_contents($path));

		$count = count($tokens);
		for ($i = 2; $i < $count; $i++)
		{
			if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING)
			{
				$className = $tokens[$i][1];
				break;
			}
		}

		return $className;
	}
}
?>