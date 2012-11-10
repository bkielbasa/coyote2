<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Poll_Controller extends Adm
{
	function main()
	{
		$poll = &$this->getModel('poll');

		if ($this->input->isPost())
		{
			$delete = $this->post->delete;
			
			if ($delete)
			{
				$poll->delete('poll_id IN(' . implode(',', $delete) . ')');
			}
			$this->redirect('adm/Poll');
		}

		$this->poll = $poll->fetchAll();
		
		return true;
	}

	public function submit($id = 0)
	{
		$id = (int) $id;
		$result = array();

		$poll = &$this->getModel('poll');
		
		if ($id)
		{
			if (!$result = $poll->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Brak ankiety o tym ID!');
			}
		}
		$this->filter = new Filter_Input;

		$this->items = array();
		if ($id)
		{
			$this->items = $poll->item->getItems($id);
		}

		if ($this->input->isPost())
		{
			$data['validator'] = array(
			
					'title'			=> array(
												array('string', false, 1, 100)
									),
					'max_item'		=> array(
												array('int', false, 1, 10)
									),
					'length'		=> array(
												array('int', false, 1)
									),
					'items'			=> array(
												array('notempty')
									)
			);
			$data['filter'] = array(

					'title'			=> array('htmlspecialchars'),
					'enable'		=> array('int'),
					'max_item'		=> array('int'),
					'items'			=> array('htmlspecialchars')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				$this->load->helper('array');
				$values = $this->filter->getValues();

				$start = mktime((int)$this->post->start_h, (int)$this->post->start_i, 0, (int)$this->post->start_m, (int)$this->post->start_d, (int)$this->post->start_y);
				
				$poll->submit($id, 
							  $values['title'],
							  $start,
							  $values['length'],
							  $values['max_item'],
							  $values['enable'],
							  explode("\n", $values['items'])
							 );

				$this->redirect('adm/Poll');
			}		
		}
		$this->start_d = $this->start_m = $this->start_y = $this->start_h = $this->start_i = 0;

		if ($id)
		{
			list($this->start_d, $this->start_m, $this->start_y, $this->start_h, $this->start_i) = explode('-', date('d-m-Y-H-i', $result['poll_start']));
		}
		else
		{
			$start = getdate();

			$this->start_y = $start['year'];
			$this->start_m = $start['mon'];
			$this->start_d = $start['mday'];
			$this->start_h = $start['hours'];
			$this->start_i = $start['minutes'];
		}
		
		$this->maxItemsList = array();
		for ($i = 1; $i <= 10; $i++)
		{
			$this->maxItemsList[$i] = $i;
		}
		return View::getView('adm/pollSubmit', (array) $result);
	}
}
?>