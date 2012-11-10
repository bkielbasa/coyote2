<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Trigger_Controller extends Adm
{
	public function main()
	{
		$trigger = &$this->load->model('trigger');

		if ($this->input->getMethod() == Input::POST)
		{
			$delete = array_map('intval', $this->post->delete);
			if ($delete)
			{
				foreach ($trigger->find($delete)->fetch() as $row)
				{
					UserErrorException::__(Trigger::call('application.onTriggerDelete', $row['trigger_name']));
				}
				$trigger->delete('trigger_type != ' . Trigger_Model::SYSTEM . ' AND trigger_id IN(' . implode(',', $delete) . ')');
				$this->cache->destroy();

				Box::information('Triggery usunięte', 'Zaznaczone triggery zostały permanentnie usunięte!', '', 'adm/information_box');
				exit;
			}
		}

		$this->load->helper('text');
		echo $this->load->view('adm/trigger', array(
					'trigger'			=> $trigger->fetch(null, 'trigger_type ASC, trigger_name DESC')->fetch()
			)
		);
	}

	public static function addEvent($id, $trigger_name, $event_arr, $event_name = '')
	{
		$xml = simplexml_load_file('config/trigger.xml');

		if ($event_name)
		{ 
			for ($i = 0; $i < sizeof($xml->trigger->{$trigger_name}); $i++)
			{ 
				if ($xml->trigger->{$trigger_name}[$i]->name == $event_name)
				{
					$row = &$xml->trigger->{$trigger_name}[$i];

					foreach ($event_arr as $key => $value)
					{
						if ($value)
						{
							$row->$key = $value;
						}
						else
						{
							unset($row->$key);
						}
					}
				}
			}
		}
		else
		{
			$node = $xml->trigger->addChild($trigger_name);

			foreach ($event_arr as $key => $value)
			{
				if ($value)
				{
					$node->addChild($key, $value);
				}
			}
		} 
		return file_put_contents('config/trigger.xml', $xml->asXml(), LOCK_EX);
	}

	public static function deleteEvent($trigger_name, $event_arr)
	{
		$xml = simplexml_load_file('config/trigger.xml');

		if (sizeof($xml->trigger->$trigger_name) == 1)
		{ 
			unset($xml->trigger->$trigger_name);
		}
		else
		{ 
			for ($i = 0; $i < sizeof($xml->trigger->$trigger_name); $i++)
			{
				if (in_array((string)$xml->trigger->{$trigger_name}[$i]->name, $event_arr))
				{
					unset($xml->trigger->{$trigger_name}[$i]);
				}
			}
		}
		file_put_contents('config/trigger.xml', $xml->asXml(), LOCK_EX);
	}

	public static function deleteTrigger($trigger_name)
	{
		$xml = simplexml_load_file('config/trigger.xml');
		unset($xml->trigger->$trigger_name);

		file_put_contents('config/trigger.xml', $xml->asXml(), LOCK_EX);
	}

	public static function renameTrigger($n_name, $o_name)
	{ 
		$xml = file_get_contents('config/trigger.xml');
		$xml = str_replace(array("<$o_name>", "</$o_name>"), array("<$n_name>", "</$n_name>"), $xml);

		file_put_contents('config/trigger.xml', $xml, LOCK_EX);
	}

	public function submit($id = 0)
	{
		$id = (int)$id;
		$trigger = &$this->load->model('trigger');
		$row = array();

		if ($id)
		{
			if (!$row = @$trigger->find($id)->fetchObject())
			{
				throw new AcpErrorException('Parametr ID jest nieprawidłowy!');
			}
		}
		$this->filter = new Filter_Input;

		if ($this->input->getMethod() == Input::POST)
		{
			$data['validator'] = array(

					'name'			=> array(
												array('string', false, 0, 100)
									),
					'header'		=> array(
												array('string', true, 1, 255)
									),
					'description'	=> array(
												array('string', true, 1, 255)
									)
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				$this->load->helper('array');
				$data = array_key_pad($this->filter->getValues(), 'trigger_');

				if (!$id)
				{
					$trigger->insert($data);
				}
				else
				{
					$trigger->update($data, "trigger_id = $id");

					if ($data['trigger_name'] != $row->trigger_name)
					{
						UserErrorException::__(Trigger::call('application.onTriggerRename', $data['trigger_name'], $row->trigger_name));
					}
				}
				UserErrorException::__(Trigger::call('application.onTriggerSubmit', $id, $data));
				$this->cache->destroy();

				$this->redirect('adm/Trigger');
			}
		}

		$view = $this->load->view('adm/triggerSubmit', (array)$row);
		if ($id)
		{
			$query = $trigger->event->fetch("event_trigger = $id");

			while ($row = $query->fetchAssoc())
			{
				$view->append('event', $row);
			}
		}

		return $view;
	}

	public function event($trigger_id, $id = 0)
	{
		$id = (int)$id;
		$trigger_id = (int)$trigger_id;

		$trigger = &$this->load->model('trigger');
		$row = array();

		if (!$trigger_arr = $trigger->find($trigger_id)->fetchObject())
		{
			throw new AcpErrorException('Trigger o tym ID nie istnieje!');
		}		

		if ($id)
		{
			if (!$row = @$trigger->event->find($id)->fetchObject())
			{
				throw new AcpErrorException('Brak zdarzenia o tym ID!');
			}
		}

		$this->filter = new Filter_Input;

		if ($this->input->getMethod() == Input::POST)
		{
			$data['validator'] = array(

					'action'			=> array(
													array('int', true)
										),
					'name'				=> array(	
													array('string', false, 1, 100)
										),
					'class'				=> array(
													array('string', true, 1, 255)
										),
					'function'			=> array(
													array('string', true, 1, 255)
										),
					'path'				=> array(
													array('string', true, 1, 255)
										),
					'params'			=> array(
													array('string', true, 1, 255)
										),
					'eval'				=> array(
													array('string', true)
										)
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				$this->load->helper('array');
				$sql_data = array_key_pad($this->filter->getValues(), 'event_');
				$event_data = array_key_pad($this->filter->getValues(), '');

				$sql_data['event_trigger'] = $trigger_id;
				$name = '';				

				if (!$id)
				{
					$trigger->event->insert($sql_data);
					$id = $this->db->nextId();					
				}
				else
				{
					$name = $trigger->event->select('event_name')->where("event_id = $id")->get()->fetchField('event_name');
					$trigger->event->update($sql_data, "event_id = $id");									
				}
				$event_data = array_map('stripslashes', $event_data);

				UserErrorException::__(Trigger::call('application.onEventSubmit', $id, $trigger_arr->trigger_name, $event_data, $name));
				$this->cache->destroy();

				$this->redirect('adm/Trigger/Submit/' . $trigger_id);
			}
		}

		$view = $this->load->view('adm/triggerEvent', (array)$row);
		$view->assign(array(
				'trigger'		=> $trigger_arr
			)
		);

		return $view;
	}

	public function events($trigger_id)
	{
		$trigger = &$this->load->model('trigger');

		$trigger_id = (int)$trigger_id;
		$delete = array_map('intval', $this->input->post->delete);

		if (!$data = $trigger->find($trigger_id)->fetchObject())
		{
			throw new AcpErrorException('Brak triggera o tym ID!');
		}

		if ($delete)
		{

			foreach ($trigger->event->find($delete)->fetch() as $row)
			{
				$event_arr[] = $row['event_name'];
			}

			if ($event_arr)
			{
				UserErrorException::__(Trigger::call('application.onEventDelete', $data->trigger_name, $event_arr));
				
				$trigger->event->delete("event_trigger = $trigger_id AND event_id IN(" . implode(',', $delete) . ')');
				$this->cache->destroy();
			}
		}

		$this->redirect('adm/Trigger/Submit/' . $trigger_id);
	}
}
?>