<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Ban_Controller extends Adm
{
	function main()
	{
		$ban = &$this->load->model('ban');

		if ($this->input->getMethod() == Input::POST)
		{
			if ($delete = $this->post->delete)
			{
				$ban->delete('ban_id IN(' . implode(',', $delete) . ')');
			}
		}
		$start = (int) $this->get['start'];

		Sort::setDefaultSort('ban_id', Sort::DESC);

		$this->ban = $ban->getBans(Sort::getSortAsSQL(), $start, 50)->fetchAll();
		$this->pagination = new Pagination('', $ban->count(), 50, $start);

		return true;
	}

	public function submit($id = 0)
	{
		$id = (int)$id;

		$ban = &$this->getModel('ban');
		$result = array();

		if ($id)
		{
			if (!$result = $ban->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Wpis o tym ID nie istnieje!');
			}
		}

		$this->userId = $this->get->id;
		$this->userIp = $this->get->ip;

		if ($this->userId)
		{
			$user = &$this->getModel('user');
			if ($userData = $user->find($this->userId)->fetchAssoc())
			{
				$this->userId = $userData['user_name'];
			}
		}

		$this->filter = new Filter_Input;

		if ($this->input->isPost())
		{
			$data['validator'] = array(
				'user'			=> array(
											array('string', false, 2, 50),
											array('match', Config::getItem('user.name'))
								),
				'email'			=> array(
											array('string', true),
											array('email', true)
								),
				'ip'			=> array(
											array('string', true, 5, 50)
								)

			);
			$data['filter'] = array(
				'reason'		=> array('htmlspecialchars')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				$this->load->helper('array');
				$user = &$this->getModel('user');

				$data = $this->filter->getValues();

				try
				{
					if ($this->post->timeout)
					{
						$data['expire'] = mktime(0, 1, 1, (int)$this->input->post->timeout_month, (int)$this->input->post->timeout_day, (int)$this->input->post->timeout_year);
					}
					else
					{
						$data['expire'] = 0;
					}
					$data['user'] = $user->getByName($this->post->user)->fetchField('user_id');

					if (!$data['user'])
					{
						$data['user'] = User::ANONYMOUS;
					}

					$data = array_key_pad($data, 'ban_');

					if (!$id)
					{
						$data += array('ban_creator' => User::$id);
						$ban->insert($data);

						Log::add('Dodano nową blokadę', E_BAN_SUBMIT);
					}
					else
					{
						$ban->update($data, "ban_id = $id");
						Log::add('Uaktualniono blokadę', E_BAN_SUBMIT);
					}

					$session = &$this->getModel('session');
					// sprawdzenie, czy zalogowany jest uzytkownik odpowiadajacy wzorcowi
					if ($sid = $session->isSession(null, $data['ban_user'], ($data['ban_ip'] ? $data['ban_ip'] : null)))
					{
						$session->delete("session_id = '$sid'");
					}

					$this->redirect('adm/Ban');
				}
				catch (Exception $e)
				{
					Log::add('Błąd podczas uaktualniania danych: ' . $e->getMessage(), E_ERROR);

					echo $e->getMessage();
					exit;
				}
			}
		}

		if ($id && $result['ban_expire'])
		{
			$this->expire = getdate($result['ban_expire']);
		}
		else
		{
			$this->expire = getdate(strtotime('+1 day'));
		}

		return View::getView('adm/banSubmit', $result);
	}
}
?>