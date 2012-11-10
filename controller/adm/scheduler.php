<?php

class Scheduler_Controller extends Adm
{
	function main()
	{
		$scheduler = &$this->getModel('scheduler');

		if ($this->input->isPost())
		{
			$delete = $this->post->delete;
			if ($delete)
			{
				$scheduler->delete('scheduler_id IN(' . implode(',', $delete) . ')');
				$this->redirect('adm/Scheduler');
			}
		}

		$this->scheduler = $scheduler->fetchAll();
		return true;
	}

	public function submit($id = 0)
	{
		$id = (int) $id;
		$scheduler = &$this->getModel('scheduler');

		$result = array();

		if ($id)
		{
			if (!$result = $scheduler->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Wpis o tym ID nie istnieje!');
			}
		}

		$this->moduleList = array();
		foreach ($this->module->getModules() as $row)
		{
			$this->moduleList[$row['module_id']] = $row['module_text'];
		}

		$this->filter = new Filter_Input;

		if ($this->input->isPost())
		{
			$data['validator'] = array(

				'name'				=> array(
												array('notempty')
									),
				'class'				=> array(
												array('notempty')
									),
				'method'			=> array(
												array('notempty'),
									)
			);
			$data['filter'] = array(

				'name'				=> array('trim', 'htmlspecialchars'),
				'description'		=> array('htmlspecialchars'),
				'method'			=> array('trim', 'htmlspecialchars'),
				'class'				=> array('trim', 'htmlspecialchars'),
				'frequency'			=> array('int'),
				'module'			=> array('int'),
				'hour'				=> array('int'),
				'minute'			=> array('int')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				load_helper('array');

				$data = array_key_pad($this->filter->getValues(), 'scheduler_');
				$data['scheduler_enable'] = (bool) isset($this->post->enable);

				if ($this->post->mode == 'frequency')
				{
					$data['scheduler_time'] = null;
				}
				else
				{
					$data['scheduler_frequency'] = null;
					$data['scheduler_time'] = $this->post->hour . ':' . $this->post->minute;
				}
				unset($data['scheduler_hour'], $data['scheduler_minute']);

				if (!$id)
				{
					$scheduler->insert($data);
				}
				else
				{
					$scheduler->update($data, "scheduler_id = $id");
				}

				$this->redirect('adm/Scheduler');
			}
		}

		$this->hour = $this->minute = 0;
		if ($id)
		{
			if ($result['scheduler_time'])
			{
				list($this->hour, $this->minute) = explode(':', $result['scheduler_time'], 2);
			}
		}

		return View::getView('adm/schedulerSubmit', $result);
	}
}
?>