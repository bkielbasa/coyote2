<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Login_Controller extends Page_Controller
{
	public $salt;

	function main()
	{
		$this->form = $this->getForm();

		if ($this->input->isPost())
		{
			$user = &$this->getModel('user');

			if ($this->form->isValid())
			{
				$result = $user->getByName($this->form->getValue('name'))->fetchAssoc();

				if (!$result['user_active'])
				{
					throw new UserErrorException('Twoje konto jest nieaktywne.');
				}

				/*
				 * Te instrukcje sa przeygotowane specjalnie dla serwisu
				 * 4programmers.net. W starej wersji serwisu haslo bylo haszowane
				 * algorytmem DES. W tym warunku sprawdzamy, czy haslo zapisane
				 * w bazie danych jest typu DES. Jezeli tak - zapisujemy pod postacia
				 * sha256.
				 */
				$password = &$result['user_password'];

				if (strlen($password) == 13 && substr($password, 0, 2) == 'ab')
				{
					if (!$result['user_salt'])
					{
						$result['user_salt'] = uniqid(mt_rand(), true);
					}

					$password = hash('sha256', $result['user_salt'] . $this->form->getValue('password'));
					$user->update(array('user_salt' => $result['user_salt'], 'user_password' => $password), 'user_id = ' . $result['user_id']);
				}
				elseif (!$result['user_salt'])
				{
					$result['user_salt'] = uniqid(mt_rand(), true);
					$password = hash('sha256', $result['user_salt'] . $this->form->getValue('password'));

					$user->update(array('user_salt' => $result['user_salt'], 'user_password' => $password), 'user_id = ' . $result['user_id']);
				}

				UserErrorException::__(Trigger::call('application.onUserLogin', $result['user_id'], $result['user_password']));

				$user->login($result['user_id'], $result['user_password'], (bool)$this->form->getValue('auto_login'));

				UserErrorException::__(Trigger::call('application.onUserLoginComplete', $result['user_id']));
				User::$id = $result['user_id'];

				Log::add($result['user_name'], E_UCP_LOGIN);
				$referer = $this->form->getValue('referer');

				$validate = new Validate_Url;
				if (!$validate->isValid($referer))
				{
					$referer = Url::base();
				}

				$host = parse_url($referer, PHP_URL_HOST);

				if (stripos($host, $this->input->getHost()) === false)
				{
					$referer = Url::base();
				}

				/*
				 * Poprawka dla Adsense :( Poniewaz Google nie oferuje kodu reklam dla HTTPS, w razie gdy link do przekierowania
				 * wskazuje na HTTPS - zamieniamy na HTTP
				 */
				if (parse_url($referer, PHP_URL_SCHEME) == 'https')
				{
					$referer = preg_replace('#^https#i', 'http', $referer);
				}

				/*
				 * Jezeli ustawione jest powiadomienie o udanym logowaniu, wyswietlamy do autora
				 * konta, e-mail informacyjny
				 */
				if ($result['user_alert_login'] && $result['user_confirm'] && $result['user_active'])
				{
					if (Config::getItem('email.login'))
					{
						$email = &$this->getModel('email');

						$emailData = $email->find((int) Config::getItem('email.login'))->fetchAssoc();
						if ($emailData)
						{
							$email->setValue('ip', User::$ip);
							$email->setValue('host', gethostbyaddr(User::$ip));
							$email->setValue('time', User::formatDate(time(), $result['user_dateformat'], false));
							$email->setValue('name', $result['user_name']);

							$email->send($emailData['email_name'], $result['user_email'], $result['user_name']);
						}
					}
				}

				// przekierowanie na poprzednia strone
				$this->redirect($referer);
			}
			else
			{
				Log::add($this->form->getValue('name'), E_UCP_LOGIN_FAILED);
				$result = $user->getByName($this->form->getValue('name'))->fetchAssoc();

				/*
				 * Jezeli ustawiony jest alert o nieprawidlowym logowaniu, wysylamy e-maila
				 * z informacja
				 */
				if ($result['user_alert_access'] && $result['user_confirm'] && $result['user_active'])
				{
					if (Config::getItem('email.invalid_login'))
					{
						$email = &$this->getModel('email');

						$emailData = $email->find((int) Config::getItem('email.invalid_login'))->fetchAssoc();
						if ($emailData)
						{
							$email->setValue('ip', User::$ip);
							$email->setValue('host', gethostbyaddr(User::$ip));
							$email->setValue('time', User::formatDate(time(), $result['user_dateformat'], false));
							$email->setValue('name', $result['user_name']);

							$email->send($emailData['email_name'], $result['user_email'], $result['user_name']);
						}
					}
				}
			}
		}

		return parent::main();
	}

	private function getSalt()
	{
		$session = &Load::loadClass('session');
		if (isset($session->loginSalt))
		{
			$this->salt = $session->loginSalt;
		}
		else
		{
			$this->salt = 'hash' . uniqid();
			$session->loginSalt = $this->salt;
		}

		return $this->salt;
	}

	private function getForm()
	{
		Load::loadFile('lib/validate.class.php');

		$form = new Forms('', Forms::POST);
		$form->disableErrors();

		$referer = '';
		if (isset($this->get->redirect))
		{
			$referer = $this->get['redirect'];
		}
		else
		{
			$referer = $this->input->getReferer();
		}
		$validate = new Validate_Url;
		if (!$validate->isValid($referer))
		{
			$referer = Url::base();
		}

		$host = parse_url($referer, PHP_URL_HOST);

		if (stripos($host, $this->input->getHost()) === false)
		{
			$referer = Url::base();
		}

		$form->createElement('hidden', 'referer')->setValue($referer)->setEnableDefaultDecorators(false);
		$form->createElement('hash', $this->getSalt());

		$username = $form->createElement('text', 'name')->setLabel('Nazwa użytkownika');
		$username->addFilter('trim');
		$username->addFilter('strip_tags');
		$username->addFilter(new Filter_Replace('<>"\''));

		$username->addValidator(new Validate_String(false, 2, 28));
		$username->addValidator(new Validate_User(false, true));

		$password = $form->createElement('password', 'password')->setLabel('Hasło');

		$validateString = new Validate_String(false, 2, 50);
		$validateString->setTemplate(Validate_String::TOO_SHORT, 'Podane hasło jest zbyt krótkie');
		$validateString->setTemplate(Validate_String::TOO_LONG, 'Podane hasło jest zbyt długie');

		$password->addFilter('strip_tags');
		$password->addFilter(new Filter_Replace('<>"\''));
		$password->addValidator($validateString);
		$password->addValidator(new Validate_Password($this->post->name));

		$form->createElement('checkbox', 'auto_login')->addAfterText(' Loguj przy każdej wizycie')->setLabel('&nbsp;');
		$form->createElement('submit')->setValue('Logowanie')->setLabel('&nbsp;');

		return $form;
	}
}
?>