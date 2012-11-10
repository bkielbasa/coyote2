<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Confirm extends Controller
{
	function main()
	{
		if (User::data('confirm'))
		{
			throw new Error(500, 'Twój adres e-mail jest już potwierdzony!');
		}
		$this->filter = new Filter_Input;

		Breadcrumb::add(url('@user'), 'Panel użytkownika');
		Breadcrumb::add(url('@user?controller=Confirm'), 'Weryfikacja adresu e-mail');

		if ($this->input->isPost())
		{
			$data['validator'] = array(

				'email'					=> array('email')
			);
			$data['filter'] = array(

				'email'					=> array('strip_tags', new Filter_Replace('<>"\''))
			);

			if (User::$id == User::ANONYMOUS)
			{
				$data['filter']['name'] = array('strip_tags', new Filter_Replace('<>"\''));
				$data['validator']['name'] = array('username');
			}


			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				$actkey = Text::random(10);

				$userId = User::$id;
				$userName = User::data('name');

				if ($userId == User::ANONYMOUS)
				{
					$user =&$this->getModel('user');
					$result = $user->getByName($this->post->name)->fetchAssoc();

					// uzytkownik probuje wyslac link aktywacyjny na konto juz potwierdzone
					if ($result['user_confirm'])
					{
						throw new Error(500, 'Podane konto zostało już potwierdzone przez użytkownika!');
					}

					// konto jest zablokowane
					if (!$result['user_active'])
					{
						throw new Error(500, 'Podane konto zostało zablokowane!');
					}

					$userId = $result['user_id'];
					$userName = $result['user_name'];
				}

				$email = &$this->getModel('email');

				$result = $email->find(Config::getItem('email.confirm'))->fetchAssoc();
				if (!$result)
				{
					throw new Exception('Szablon e-mail służący do potwierdzenia adresu e-mail nie mógł zostać znaleziony. Poinformuj administratora!');
				}

				$email->setValue('id', $userId);
				$email->setValue('key', $actkey);
				$email->setValue('name', $userName);

				if (!$email->send($result['email_name'], $this->post->email, $userName))
				{
					throw new Exception('Nie można wysłać e-maila. Poinformuj administratora!');
				}
				// utworzenie wpisu z kluczem aktywacyjnym konta
				$this->db->insert('actkey', array('actkey' => $actkey, 'user_id' => $userId));

				Box::information('Link aktywacyjny został wygenerowany', 'Na podany adres e-mail wygenerowany został link aktywacyjny, dzięki któremu możesz potwierdzić adres e-mail', url('@user'));
				exit;
			}
		}

		return true;
	}

	public function email()
	{
		$userId = isset($this->get->id) ? (int)$this->get->id : User::$id;
		$actkey = (string)$this->get->key;

		if (!$userId || !$actkey)
		{
			throw new Error(500, 'Błąd! Odnośnik nie jest prawidłowy!');
		}

		$user = &$this->getModel('user');
		$result = $user->find($userId)->fetchAssoc();

		if (!$result['user_active'])
		{
			throw new Error(500, 'Podane konto zostało zablokowane!');
		}
		if ($result['user_confirm'])
		{
			throw new Error(500, 'Podane konto zostało już potwierdzone przez użytkownika!');
		}
		$userName = $result['user_name'];

		$query = $this->db->select()->from('actkey')->where('actkey = "' . $actkey . '" AND user_id = ' . $userId)->get();
		if (!count($query))
		{
			throw new Error(500, 'Podany klucz aktywacyjny jest nieprawdiłowy!');
		}
		$data = array(
			'user_confirm'		=>	1
		);

		$result = $query->fetchAssoc();
		if ($result['user_email'])
		{
			$data['user_email'] = $result['user_email'];
		}

		$user->update($data, 'user_id = ' . $userId);

		$this->db->delete('actkey', 'user_id = ' . $userId);
		Box::information('Adres e-mail potwierdzony!', 'Adres e-mail został potwierdzony! Dziękujemy!', url('@user'));

		Log::add($userName, E_CONFIRM);
	}
}
?>