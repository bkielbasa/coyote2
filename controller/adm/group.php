<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Group_Controller extends Adm
{
	function main()
	{
		$group = &$this->getModel('group');

		if ($this->input->getMethod() == Input::POST)
		{
			$this->post->setFilters('int');

			if ($delete = $this->post->delete)
			{
				$group->delete($delete);

				$this->message = 'Wybrane grupy zostały usunięte';
			}
		}
		$totalItems = $group->count();

		$this->group = $group->fetch(null, null, (int)$this->get['start'], 50)->fetch();
		$this->pagination = new Pagination('', $totalItems, 50, (int)$this->get['start']);

		return true;	
	}

	public function submit($id = 0)
	{
		$id = (int)$id;

		$group = &$this->load->model('group');
		$user = &$this->load->model('user');

		$result = array();

		if ($id)
		{ 
			$result = (array)$group->find($id)->fetchAssoc();

			if (!$result['group_type'])
			{
				Box::information('Błąd', 'Przepraszamy. Nie możesz edytować grup systemowych', '', 'adm/information_box');
				exit;
			}
		}		
		$this->filter = new Filter_Input;

		if ($this->input->getMethod() == Input::POST)
		{
			$data['validator'] = array(
				'name'			=> array(											
				
										array('string', false, 2, 50)	
								),
				'leader'		=> array(

										array('string', false, 2, 50),
										array('match', '/^[0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ _-]+$/'),
										'username'									
								)
			);
			$data['filter'] = array(
				'name'			=> array(

										'htmlspecialchars'
								),
				'desc'			=> array(

										'htmlspecialchars'
								)
			);
			$this->filter->setRules($data);
			
			if ($result)
			{
				if ($row['group_name'] != $this->post->name)
				{
					$this->filter->addValidator('name', 'groupname');
				}
			}
			else
			{
				$this->filter->addValidator('name', 'groupname');
			}			

			if ($this->filter->isValid($_POST))
			{
				$this->load->helper('array');

				$data = array_key_pad($this->filter->getValues(), 'group_');
				$data['group_leader'] = $user->getByName($this->post->leader)->fetchObject()->user_id;

				if ($id)
				{ 
					$group->update($data, "group_id = $id");
				}
				else
				{
					$id = $group->insert($data);
				}

				$this->redirect("adm/Group");
			}
		}

		$view = $this->load->view('adm/groupSubmit');

		if ($result)
		{
			$view->assign($result);

			$this->load->helper('sort');
			// dezaktywacja helpera. Lista userow nie bedzie podlegala sortowaniu
			Sort::disable();

			$this->user = $group->getMembers($id, (int)$this->get['start'], 50)->fetch();
			$totalItems = $group->count();

			$this->pagination = new Pagination('', $totalItems, 50, (int)$this->get['start']);

			$view->id = $id;
			$view->leader = $user->find($result['group_leader'])->fetchObject()->user_name;
		}

		return $view;
	}
}
?>