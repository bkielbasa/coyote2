<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class User_Controller extends Adm
{
	function main()
	{
		$this->userTime = array(
			0			=> 'Wszystkie wpisy',
			Time::HOUR	=> 'Ostatnia godzina',
			Time::DAY	=> 'Ostatnie 24 godz.',
			Time::WEEK	=> 'Ostatni tydzień',
			Time::WEEK * 2	=> 'Ostatnie 2 tygodnie',
			Time::MONTH	=> 'Ostatnie 4 tygodnie',
			Time::MONTH * 6	=> 'Ostatnie 6 miesięcy',
			Time::YEAR	=> 'Ostatni rok'
		);

		// ustawienie domyslnego trybu sortowania
		Sort::setDefaultSort('user_id', Sort::ASC);
		$user = &$this->getModel('user');

		$this->user = $user->filter($this->get->id, $this->get->name, $this->get->email, $this->get->status, $this->get->active, $this->get->confirm, $this->get->regdate, $this->get->lastvisit, $this->get->ip, $this->get->login, Sort::getSortAsSQL(), (int)$this->get['start'], 25)->fetchAll();
		$this->pagination = new Pagination('', $this->count = $user->getFoundRows(), 25, (int)$this->get['start']);

		return true;
	}

	public function submit($id = 0)
	{
		$id = (int)$id;

		$user = &$this->load->model('user');
		$result = array();

		if ($id)
		{
			if (!$result = $user->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Użytkownik o tym ID nie istnieje!');
			}
		}
		$field = &$this->getModel('field');

		$component = new Component;
		$form = &$component->displayForm($this->module->getId('user'));
		$form->setEnableDefaultDecorators(false);

		if ($id)
		{
			$form->setDefaults($result);
		}

		$this->group = $this->groupList = array();

		// pobranie listy grup do ktorych mozna przypisac uzytkownika
		$query = $this->db->select('g.group_id, g.group_name, g.group_type, gg.user_id')->from('`group` g')->leftJoin('auth_group gg', 'gg.user_id = ' . $id . ' AND gg.group_id = g.group_id')->get();
		foreach ($query as $row)
		{
			if ($row['group_type'] != Group_Model::SPECIAL)
			{
				$this->group[$row['group_id']] = $row;
			}

			if ($row['user_id'] && $row['group_id'] > 1)
			{
				$this->groupList[$row['group_id']] = $row['group_name'];
			}
		}

		$this->filter = new Filter_Input;

		if ($this->input->getMethod() == Input::POST)
		{
			if (!Auth::get('a_user'))
			{
				throw new AcpErrorException('Nie posiadasz uprawnień do edycji danych użytkowników!');
			}

			$data['validator'] = array(

				'name'			=> array(

										array('string', false, 2, 50),
										array('login', $id)
								),
				'email'			=> array(

										array('email', true)
								)
			);
			$data['filter'] = array(

				'active'		=> array('int'),
				'confirm'		=> array('int'),
				'group'			=> array('int')
			);

			if (Auth::get('a_password'))
			{
				$data['validator'] += array(

					'password'		=> array(

											array('string', true, 2, 50)
									),

					'password_confirm'	=> array(

											array('string', true, 2, 50),
											array('equal', $this->post->password)
									),
				);
			}

			$this->filter->setRules($data);
			$form->setUserData();

			$formValidation = $form->isValid();

			if ($this->filter->isValid($_POST) && $formValidation)
			{
				$this->load->helper('array');
				$data = array_key_pad($this->filter->getValues(), 'user_');

				try
				{
					if (!isset($this->groupList[$data['user_group']]))
					{
						unset($data['user_group']);
					}
					unset($data['user_password_confirm'], $data['user_password']);

					// ID: 3 = grupa administratorzy
					if (in_array(3, array_keys($this->groupList)))
					{
						if (!in_array(3, $user->getGroups()))
						{
							throw new AcpErrorException('Tylko administratorzy mogą edytować konta administratorów');
						}
					}

					if (Auth::get('a_password'))
					{
						if ($this->input->post->password)
						{
							$salt = uniqid(mt_rand(), true);

							$data['user_password'] = hash('sha256', $salt . $this->post->password);
							$data['user_salt'] = $salt;
						}
					}

					$data = array_merge($data, $component->onSubmit());
					UserErrorException::__(Trigger::call('application.onUserSubmit', array(&$data)));

					$ip = array();

					foreach ($this->post['ip'] as $element)
					{
						if (preg_match('#[0-9\*]{1,3}#', $element))
						{
							$ip[] = $element;
						}
					}
					if (!in_array(count($ip), array(0, 4, 8, 12)))
					{
						$count = count($ip);

						while (--$count % 4 == 0)
						{
							if ($count % 4 == 0)
							{
								$ip = array_slice($ip, 0, $count);
							}
						}
					}

					$data['user_ip_access'] = implode('.', $ip);

					if (!$id)
					{
						$id = $user->insert($data);

						Log::add('Dodano nowego użytkownika: "' . $data['user_name'] . '"', E_REGISTER);
						$this->redirect('adm/User');
					}
					else
					{
						$user->update($data, "user_id = $id");
						Log::add("Uaktualniono dane użytkownika #$id ($data[user_name])", E_USER_UPDATE);
					}

					$this->cache->remove('logins');

					$this->session->message = 'Zmiany zostały zapisane';
					$this->redirect('adm/User');
				}
				catch (Exception $e)
				{
					Log::add('Błąd podczas uaktualniania danych: ' . $e->getMessage(), E_ERROR);

					echo $e->getMessage();
					exit;
				}
			}
		}

		$this->form = $form;

		if ($id)
		{
			Sort::setDefaultSort('log_id', Sort::DESC);
			$this->load->helper('array');

			$log = &$this->getModel('log');
			$this->log = $log->filter(null, null, null, $id, null, null, Sort::getSortAsSQL(), (int) $this->get['start'], 25);
			$this->pagination = new Pagination('', $log->getFoundRows(), 25, (int)$this->get['start']);

			$this->logType = $log->getLogTypes();

			foreach ($this->log as $index => $row)
			{
				if ($row['log_type'] == E_USER_UPDATE)
				{
					// lame regexp... zamiana ID usera na dzialajcy link w opisie zdarzenia
					$row['log_message'] = preg_replace('~(.*?)#(\d+) \((.*)\)~', '$1 #$2 (<a href="' . Url::site() . 'adm/User/Submit/$2">$3</a>)', $row['log_message']);
					$this->log[$index] = $row;
				}
			}
		}

		$this->ip = explode('.', $result['user_ip_access']);

		return View::getView('adm/userSubmit', $result);
	}

	public function __group()
	{
		$userId = (int)$this->get->id;
		$userGroup = array();

		$query = $this->db->select('g.group_id, gg.user_id')->from('`group` g')->where('g.group_type != ' . Group_Model::SPECIAL)->leftJoin('auth_group gg', 'gg.user_id = ' . $userId . ' AND gg.group_id = g.group_id')->get();
		foreach ($query as $row)
		{
			if ($row['user_id'])
			{
				$userGroup[] = $row['group_id'];
			}
		}
		$group = &$this->getModel('group');
		$message = array();

		try
		{
			foreach ((array)array_diff((array)$this->get->g, $userGroup) as $groupId)
			{
				if (!$result = $group->find($groupId)->fetchAssoc())
				{
					throw new Exception("Grupa o tej nazwie, nie istnieje!");
				}
				if ($result['group_type'] == Group_Model::SPECIAL)
				{
					throw new Exception("Nie można zapisać użytkownika do grupy systemowej");
				}

				$group->addUser($result['group_id'], $userId);
				$message[] = 'Dodano użytkownika do grupy ' . $result['group_name'];
			}
			foreach ((array)array_diff($userGroup, (array)$this->get->g) as $groupId)
			{
				if (!$result = $group->find($groupId)->fetchAssoc())
				{
					throw new Exception("Grupa o tej nazwie, nie istnieje!");
				}
				if ($result['group_type'] == Group_Model::SPECIAL)
				{
					throw new Exception('Nie można usunąć użytkownika z grupy systemowej!');
				}
				if ($result['group_leader'] == $userId)
				{
					throw new Exception('Nie można usunąć użytkownika grupy ' . $result['group_name'] . '. Jest on jej liderem!');
				}

				$group->delUser($groupId, $userId);
				$message[] = 'Usunięto użytkownika z grupy ' . $result['group_name'];
			}

			if (!$message)
			{
				$message[] = 'Żadne zmiany nie zostały wprowadzone';
			}
		}
		catch (Exception $e)
		{
			echo json_encode(array(
				'error'		=> $e->getMessage()
				)
			);
		}
		if ($message)
		{
			echo json_encode(array(
				'message' => $message
				)
			);
		}

		exit;
	}

	public function logged()
	{
		$this->load->helper('sort');
		Sort::setDefaultSort('session_start', Sort::ASC);

		$query = array();
		if ($this->input->get->sort)
		{
			$query['sort'] = $this->input->get->sort;
		}
		if ($this->input->get->order)
		{
			$query['order'] = $this->input->get->order;
		}

		$session = &$this->load->model('session');
		$totalItems = $session->count();

		$totalRobots = $session->select('COUNT(*)')->where('session_robot != ""')->fetchField('COUNT(*)');

		$view = $this->load->view('adm/userLogged');
		$view->assign(array(
			'user'				=> $session->fetch(null, Sort::getSortAsSQL(), (int)$this->get['start'], 50)->fetch(),
			'pagination'		=> new Pagination(url('adm/User/Logged?' . http_build_query($query)), $totalItems, 50, (int)$this->get['start']),

			'totalRobots'		=> $totalRobots,
			'totalItems'		=> $totalItems
			)
		);

		echo $view;
	}

	public function visit()
	{
		$this->purge = array(

			Time::MINUTE * 30		=> '30 minut',
			Time::DAY				=> '1 dzień',
			Time::WEEK				=> '7 dni',
			Time::WEEK * 2			=> '14 dni',
			Time::MONTH				=> '30 dni',
			Time::MONTH * 2			=> '60 dni'
		);
		$this->date = array(

			0								=> 'Dowolna data',
			Time::HOUR						=> '1 godz.',
			Time::HOUR * 2					=> '2 godz.',
			Time::DAY						=> '24 godz.',
			Time::WEEK						=> '7 dni',
			Time::WEEK * 2					=> '2 tyg.',
			Time::MONTH						=> '31 dni'

		);

		$session = new Session_Log_Model;

		if ($this->input->isPost())
		{
			if ($this->post->purge)
			{
				$session->delete('log_stop < ' . (time() - (int) $this->post->purge) . (isset($this->post->anonymous) ? ' AND log_user = ' . User::ANONYMOUS : ''));
				$this->message = 'Wybrane rekordy zostały usunięte';
			}
		}
		Sort::setDefaultSort('log_stop', Sort::DESC);

		$this->visit = $session->filter($this->get->user, $this->get->start, $this->get->stop, $this->get->ip, Sort::getSortAsSQL(), (int)$this->get['start'], 50)->fetchAll();
		$totalItems = $this->db->query('SELECT FOUND_ROWS() as total')->fetchField('total');

		$this->pagination = new Pagination('', $totalItems, 50, (int)$this->get['start']);

		return true;
	}
}

?>