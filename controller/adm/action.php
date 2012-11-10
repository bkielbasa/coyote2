<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Action_Controller extends Adm
{
	function main()
	{
		$action = &$this->load->model('action');

		if ($this->input->getMethod() == Input::POST)
		{
			$delete = array_map('intval', $this->input->post->delete);
			if ($delete)
			{
				$event_arr = array();

				$trigger = &$this->load->model('trigger');
				$query = $this->db->select('event_name, trigger_name')->from('trigger_event, trigger')->where('event_action IN(' . implode(',', $delete) . ') AND trigger_id = event_trigger')->get();

				while ($row = $query->fetchAssoc())
				{
					$event_arr[$row['trigger_name']][] = $row['event_name'];
				}
				foreach ($event_arr as $trigger_name => $arr)
				{
					UserErrorException::__(Trigger::call('application.onEventDelete', $trigger_name, $arr));
				}									
				$action->delete('action_id IN(' . implode(',', $delete) . ')');
				$this->cache->destroy();
			}
			$this->redirect('adm/Action');
		}

		echo $this->load->view('adm/action', array(
				'actions'		=> $action->getActions(),
				'action'		=> $action->fetch()->fetch()
			)
		);
	}

	public function submit($id = 0)
	{
		$id = (int)$id;
		$action = &$this->load->model('action');
		$row = array();

		if ($id)
		{
			if (!$row = $action->get($id))
			{
				throw new AcpErrorException('Brak akcji o tym ID!');
			}
			$class = $row->action_class;
		}
		else
		{
			$class = $this->input->get('class');
		}
		$filename = strtolower($class);		
		if (!Load::loadFile('lib/action/' . $filename . '.class.php'))
		{
			throw new AcpErrorException("Brak pliku $filename.class.php w katalogu /lib/action.");
		}
		$class = ucfirst($class) . '_Action'; 

		$object = new $class($row);
		$object->submit();
	}
}
?>