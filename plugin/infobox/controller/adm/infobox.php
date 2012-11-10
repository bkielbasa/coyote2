<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Infobox_Controller extends Adm
{
	public function main()
	{
		$infobox = &$this->getModel('infobox');

		if ($this->input->isPost())
		{
			$delete = array_map('intval', $this->post['delete']);

			if ($delete)
			{
				$infobox->delete('infobox_id IN(' . implode(',', $delete) . ')');
				$this->redirect('adm/Infobox');
			}
		}

		$totalItems = $infobox->count();
		$this->pagination = new Pagination('', $totalItems, 10, (int) $this->get['start']);
		$this->infobox = $infobox->fetch(null, 'infobox_id DESC', (int) $this->get['start'], $totalItems);

		return true;
	}

	public function submit($id = 0)
	{
		$id = (int) $id;
		$infobox = &$this->getModel('infobox');

		$result = array();

		if ($id)
		{
			if (!$result = $infobox->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Rekord o podanym ID nie istnieje!');
			}

			$result['infobox_lifetime'] = $result['infobox_lifetime'] / Time::DAY;
		}

		$this->priority = array();
		for ($i = 1; $i <= 9; $i++)
		{
			$this->priority[$i] = $i;
		}

		$this->filter = new Filter_Input;

		if ($this->input->isPost())
		{
			$data = array(

				'validator'         => array(


							'title'             => array(


												array('notempty')

							),

							'content'           => array(


												array('notempty')
							)

				),
				'filter'            => array(

							'title'             => array('trim', 'htmlspecialchars'),
							'enable'            => array('int'),
							'lifetime'          => array('int'),
							'priority'          => array('int')
				)
			);


			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				load_helper('array');
				$data = $this->filter->getValues();

				if (!isset($data['enable']))
				{
					$data['enable'] = false;
				}

				$data['lifetime'] = $data['lifetime'] * Time::DAY;
				$data = array_key_pad($data, 'infobox_');

				if (!$id)
				{
					$data['infobox_time'] = time();
					$infobox->insert($data);
				}
				else
				{
					$infobox->update($data, 'infobox_id = ' . $id);
				}

				$this->session->message = 'Komunikat został zapisany';
				$this->redirect('adm/Infobox');
			}
		}

		return View::getView('adm/infoboxSubmit', $result);

	}
}
?>
