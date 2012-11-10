<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Register extends Page_Controller
{
	public $hasAccount;

	public function main()
	{
		$user = &$this->getModel('user');

		/**
		 * W tym momencie sprawdzamy, czy uzytkownik z tego IP nie utworzyl juz
		 * wczesniej konta w serwisie. Jezeli tak, nie pozwalamy mu na zarejestrowanie
		 * kolejnego
		 */
		$query = $user->select()->where('user_ip = ? AND user_regdate > ?', $this->input->getIp(), (time() - Time::DAY))->get();

		$this->hasAccount = count($query);
		if ($this->hasAccount)
		{
			$this->account = $query->fetchAssoc();
		}

		if ($this->input->isPost())
		{
			if ($this->hasAccount)
			{
				exit('Hacking attempt');
			}
			$this->form = $this->getForm($this->post->token);

			/*
			 * Jezeli uzytkownik wybral opcje losowego wygenerowania hasla, to nalezy ustawic pole
			 * "adres e-mail" jako wymagane
			 */
			if (isset($this->post->random))
			{
				if (Config::getItem('user.confirm') == 'false')
				{
					$this->form->getElement('email')->setRequired(true);
					$this->form->getElement('email')->title = 'Na ten adres zostanie przesłany link aktywacyjny służący do potwierdzenia konta';

					$this->form->getElement('email')->addValidator(new Validate_Email);
					$this->form->getElement('password')->setRequired(false)->setValidators(array());
					$this->form->getElement('password_c')->setRequired(false)->setValidators(array());
				}
			}

			if ($this->form->isValid())
			{
				$actkey = '';

				$confirmRequired = false;
				if (Config::getItem('user.confirm') == 'true')
				{
					$actkey = Text::random(10);
					$confirmRequired = true;
				}

				$this->db->begin();
				$values = $this->form->getValues();

				try
				{
					$salt = uniqid(mt_rand(), true);

					if (isset($this->post->random))
					{
						$password = hash('sha256', $salt . $randomPassword = Text::random(15));
					}
					else
					{
						$password = hash('sha256', $salt . $values['password']);
					}

					$data = array(
						'name'				=> $values['name'],
						'email'				=> (string) $values['email'],
						'confirm'			=> false,
						'salt'				=> $salt,
						'password'			=> $password,
						'active'			=> $confirmRequired ? false : true
					);

					UserErrorException::__(Trigger::call('application.onUserRegister', array(&$data)));
					$this->load->helper('array');

					$data = array_key_pad($data, 'user_');
					// dodanie uzytkownika, przypisanie do odopwiedniej grupy itp
					$id = $user->insert($data);

					$email = &$this->getModel('email');

					if ($confirmRequired)
					{
						$result = $email->find(Config::getItem('email.confirm'))->fetchAssoc();
						if (!$result)
						{
							throw new Exception('Szablon e-mail służący do potwierdzenia adresu e-mail nie mógł zostać znaleziony. Poinformuj administratora!');
						}

						$email->setValue('id', $id);
						$email->setValue('key', $actkey);
						$email->setValue('name', $values['name']);

						if (!$email->send($result['email_name'], $values['email'], $values['name']))
						{
							throw new Exception('Nie można wysłać e-maila. Poinformuj administratora!');
						}
						// utworzenie wpisu z kluczem aktywacyjnym konta
						$this->db->insert('actkey', array('actkey' => $actkey, 'user_id' => $id));
					}

					$this->db->commit();

					$data += array('user_id' => $id);
					UserErrorException::__(Trigger::call('application.onUserRegisterComplete', array(&$data)));

					if (!$confirmRequired)
					{
						$user->login($id, $password, true);
					}

					Log::add($values['name'], E_REGISTER);
					$this->cache->remove('logins');

					if (isset($this->post->random))
					{
						$result = $email->find(Config::getItem('email.random'))->fetchAssoc();

						if ($result)
						{
							$email->setValue('name', $values['name']);
							$email->setValue('password', $randomPassword);

							$email->send($result['email_name'], $values['email'], $values['name']);
						}
					}
				}
				catch (Exception $e)
				{
					$this->db->rollback();

					Box::information('Błąd systemu', 'Rejestracja nieudana. Prosimy poinformować administratorów. <br /><br />Błąd: ' . $e->getMessage());
					exit;
				}

				if ($confirmRequired)
				{
					Box::information(__('Zarejestrowano'), __('Dziękujemy za rejestrację. Prosimy o potwierdzenie rejestracji poprzez kliknięcie w link aktywacyjny przesłany w e-mailu'), url('@activate'));
				}
				else
				{
					$referer = url("@user?id=$id");

					/*
					 * Poprawka dla Adsense :( Poniewaz Google nie oferuje kodu reklam dla HTTPS, w razie gdy link do przekierowania
					 * wskazuje na HTTPS - zamieniamy na HTTP
					 */
					if (parse_url($referer, PHP_URL_SCHEME) == 'https')
					{
						$referer = preg_replace('#^https#i', 'http', $referer);
					}

					Box::information(__('Zarejestrowano!'), __('Dziękujemy za rejestrację. Po kliknięciu przycisku OK, zostaniesz przeniesiony do swojego panelu użytkownika'), $referer);

					$email = &$this->getModel('email');

					$result = $email->find(Config::getItem('email.success'))->fetchAssoc();
					if ($result && !empty($values['email']))
					{
						$email->setValue('id', $id);
						$email->setValue('name', $values['name']);

						if (!$email->send($result['email_name'], $values['email'], $values['name']))
						{
							Log::add('Próba wysłania e-maila podczas rejestracji, zakończona niepowodzeniem', E_ERROR);
						}
					}

				}

				exit;
			}
			else
			{
				$name = $this->form->getValue('name');

				$validator = new Validate_Login(0, '/^[0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ._ -]+$/');
				$validator->setEnableDuplicate(true); // wylaczamy sprawdzenie, czy login istnieje (sprawdzamy tylko poprawnosc loginu)

				if ($this->form->getErrors('name') && !empty($name) && $validator->isValid($name))
				{
					if (strpos($name, ' ') !== false)
					{
						$login = array(
							str_replace(' ', '_', $name),
							str_replace(' ', '-', $name),
							str_replace(' ', '', $name)
						);
					}
					else
					{
						$login = array();
					}

					for ($i = 1; $i < 5; $i++)
					{
						$login[] = $name . $i;
					}
					$login[] = $name . '123';

					$user = &$this->getModel('user');
					$query = $user->select('user_name')->in('user_name', array_map(array('Text', 'quote'), $login))->get();
					if (count($query))
					{
						foreach ($query as $row)
						{
							unset($login[$row['user_name']]);
						}
					}

					if ($login)
					{
						if (count($login) > 3)
						{
							foreach (array_rand($login, 3) as $value)
							{
								$tmp[] = $login[$value];
							}
							$login = $tmp;
						}

						foreach ($login as $key => $value)
						{
							$login[$key] = Html::a('#', $value);
						}

						$element = $this->form->getElement('name');
						$element->setDescription('Dostępne loginy: ' . implode(', ', $login));
					}
				}
			}
		}
		else
		{
			$this->token = Text::random(10);
			$this->form = $this->getForm($this->token);
		}

		return parent::main();
	}

	private function getForm($token = null)
	{
		Load::loadFile('lib/validate.class.php');

		$form = new Forms('', Forms::POST);
		$form->name = 'register-form';
		$form->disableErrors();

		$form->createElement('hash', 'hash');
		$form->createElement('hidden', 'token')->setValue($token)->setEnableDefaultDecorators(false);

		$username = $form->createElement('text', 'name')->setLabel('Nazwa użytkownika')->setAttribute('id', 'userName');
		$username->title = 'Wpisz swoją unikalną nazwę użytkownika. Nazwa może zawierać litery, cyfry, spacje oraz znaki -_.';
		$username->addFilter('trim');
		$username->addFilter('strip_tags');
		$username->addFilter(new Filter_Replace('<>"\''));
		$username->addFilter(new Filter_PregReplace('/\s+/', ' '));

		$username->addValidator(new Validate_String(false, 2, 50));
		/*
		 * nazwa uzytkownika niezalezna od konfiguracji ustawien w pliku config.xml moze zawierac jedynie podane znaki:
		 */
		$username->addValidator(new Validate_Login(0, '/^[0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ._ -]+$/'));
		$username->setRequired(true);

		$password = $form->createElement('password', 'password')->setLabel('Hasło');
		$password->title = 'Hasło musi mieć min. 3 znaki długiści';

		$validateString = new Validate_String(false, 3, 50);
		$validateString->setTemplate(Validate_String::TOO_SHORT, 'Podane hasło jest zbyt krótkie');
		$validateString->setTemplate(Validate_String::TOO_LONG, 'Podane hasło jest zbyt długie');

		$password->addFilter('strip_tags');
		$password->addFilter(new Filter_Replace('<>"\''));
		$password->addValidator($validateString);
		$password->setRequired(true);

		$password_c = $form->createElement('password', 'password_c')->setLabel('Hasło (powtórnie)');
		$password_c->title = 'Wpisz ponownie swoje hasło';

		$validateString2 = new Validate_String(false, 3, 50);
		$validateString2->setTemplate(Validate_String::TOO_SHORT, 'Podane hasło jest zbyt krótkie');
		$validateString2->setTemplate(Validate_String::TOO_LONG, 'Podane hasło jest zbyt długie');

		$validateEqual = new Validate_Equal($this->post->password);
		$validateEqual->setTemplate(Validate_Equal::NOT_EQUAL, 'Podane hasło musi być identyczne w dwóch polach');

		$password_c->addFilter('strip_tags');
		$password_c->addFilter(new Filter_Replace('<>"\''));
		$password_c->addValidator($validateString2);
		$password_c->addValidator($validateEqual);
		$password_c->setRequired(true);

		$email = $form->createElement('text', 'email')->setLabel('E-mail');
		$email->title = 'Opcjonalnie. Adres e-mail powiązany z Twoim kontem. Wprowadź jeżeli chcesz otrzymywać powiadomienia';
		$email->addFilter('trim');
		$email->addFilter('strip_tags');
		$email->addFilter(new Filter_Replace('<>"\''));

		if (Config::getItem('user.confirm') == 'true')
		{
			$email->setRequired(true);
			$email->title = 'Na ten adres zostanie przesłany link aktywacyjny służący do potwierdzenia konta';

			$email->addValidator(new Validate_Email);
		}
		else
		{
			$email->addValidator(new Validate_Email(true));
		}

		if (Config::getItem('email.random'))
		{
			$checkbox = $form->createElement('checkbox', 'random')->addAfterText('Wygeneruj losowe hasło i wyślij na e-mail');
			$checkbox->setDescription('Zaznaczenie tego pola spowoduje wygenerowanie losowego hasła i wysłanie go na podany adres e-mail');
		}

		$spambot = $form->createElement('text', 'spambot', array('id' => 'spambot'))->setLabel('Kod')->setDescription('Przepisz kod: ' . $token)->setAttribute('class', 'spamrow');
		$spambot->addValidator(new Validate_Equal($token));

		$referer = $this->post->referer($this->input->getReferer());

		$validator = new Validate_Url;
		if (!$validator->isValid($referer))
		{
			$referer = Url::base();
		}

		$form->createElement('hidden', 'referer')->setValue($referer);
		$submit = $form->createElement('submit')->setValue('Rejestracja')->setLabel('&nbsp;');

		if ($this->hasAccount)
		{
			$submit->disabled = 'disabled';
		}
		return $form;
	}
}
?>