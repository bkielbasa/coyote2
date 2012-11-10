<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Index extends Controller
{
	function __start()
	{
		if (User::$id == User::ANONYMOUS)
		{
			$this->redirect(Path::connector('login') . '?redirect=' . url('@user'));
		}
	}

	function main()
	{
		Breadcrumb::add(url('@user'), 'Panel użytkownika');

		$this->form = $this->getForm();

		$field = &$this->getModel('field');

		$component = new Component;
		$component->setEnableDisplayDescription(true);

		$components = &$component->displayForm($this->module->getId('user'));
		$components->setDefaults(User::$data);

		$user = &$this->getModel('user');
		$groupIds = $user->getGroups();

		$group = &$this->getModel('group');
		$groups = $group->select('group_id, group_name')->in('group_id', $groupIds)->where('group_id > 2')->fetchPairs();

		foreach ($groups as $id => $group)
		{
			if ($group == 'ADMIN')
			{
				$groups[$id] = 'Administratorzy';
			}
		}

		if (sizeof($groups) > 1)
		{
			$this->form->createElement('select', 'group')
						->setLabel('Domyślna grupa')
						->setMultiOptions($groups)
						->setDescription('Jesteś przydzielony do więcej niż jednej grupy. Z tej listy możesz wybrać domyślną grupę')
						->setValue(User::data('group'));
		}


		/**
		 * Usystematyzowanie elementow typu checkbox. Normalnie dekorator
		 * zaklada, ze nazwa pola bedzie wyswietlana po lewej. Dokonujemy poprawki
		 * przenoszac te wartosc ZA element checkbox
		 */
		foreach ($components->getElements() as $element)
		{
			if ('Form_Element_Checkbox' == get_class($element))
			{
				$element->addAfterText('  ' . $element->getLabel());
				$element->setLabel('');
			}
		}

		if ($this->input->isPost())
		{
			if ($this->form->isValid() && $components->isValid())
			{
				$data = array_merge($this->form->getValues(), $component->onSubmit());
				foreach ($data as $key => $v)
				{
					if (preg_match('#^notify#', $key))
					{
						unset($data[$key]);
						continue;
					}
					if (!preg_match('#^user_.*$#', $key))
					{
						unset($data[$key]);
						$data['user_' . $key] = $v;
					}
				}

				unset($data['user_csrf']);

				if (User::data('email') != $this->post->email)
				{
					$data['user_confirm'] = false;

					if (Config::getItem('user.confirm') == 'true')
					{
						$this->sendConfirmEmail($this->post->email);

						unset($data['user_email']);
						$this->session->message = 'Informacje w profilu zostały zaktualizowane. Na podany adres e-mail został wysłany link służacy do potwierdzenia adresu e-mail';
					}
				}

				$user->update($data, 'user_id = ' . User::$id);
				Log::add("Uaktualniono dane użytkownika #" . User::$id . " (" . User::data('name') . ')', E_USER_UPDATE);

				if (!isset($this->session->message))
				{
					$this->session->message = 'Informacje w profilu zostały uaktualnione!';
				}
				unset($this->session->hash);

				$this->redirect('@user');
			}
		}

		$this->form->addElements($components);
		return true;
	}

	private function sendConfirmEmail($emailAddress)
	{
		$email = &$this->getModel('email');

		$result = $email->find(Config::getItem('email.confirm'))->fetchAssoc();
		if (!$result)
		{
			throw new Exception('Szablon e-mail służący do potwierdzenia adresu e-mail nie mógł zostać znaleziony. Poinformuj administratora!');
		}
		$actkey = Text::random(10);

		$email->setValue('id', User::data('id'));
		$email->setValue('key', $actkey);
		$email->setValue('name', User::data('name'));

		if (!$email->send($result['email_name'], $emailAddress, User::data('name')))
		{
			throw new Exception('Nie można wysłać e-maila. Poinformuj administratora!');
		}
		// utworzenie wpisu z kluczem aktywacyjnym konta
		$this->db->insert('actkey', array('actkey' => $actkey, 'user_id' => User::$id, 'user_email' => $emailAddress));
	}

	private function getForm()
	{
		Load::loadFile('lib/validate.class.php');

		$form = new Forms('', Forms::POST);
		$form->setEnableDefaultDecorators(false);
		$form->createElement('hash', 'csrf');

		$email = $form->createElement('text', 'email')->setLabel('E-mail')->setValue(User::data('email'));
		$email->addFilter('trim');
		$email->addFilter('strip_tags');
		$email->addFilter(new Filter_Replace('<>"\''));

		if (Config::getItem('user.confirm') == 'true')
		{
			$email->addConfig('require', true);
			$email->addValidator(new Validate_Email);
			$email->setDescription('Jeżeli chcesz zmienić adres e-mail, na nową skrzynkę zostanie wygenerowany klucz aktywacyjny');
		}
		else
		{
			$email->addValidator(new Validate_Email(true));
		}

		$dateFormats = array(
			'%d-%m-%Y %H:%M',
			'%Y-%m-%d %H:%M',
			'%m/%d/%y %H:%M',
			'%d-%m-%y %H:%M',
			'%d %b %y %H:%M',
			'%d %B %Y, %H:%M'
		);
		$dateformat = $form->createElement('select', 'dateformat')->setLabel('Format daty');
		$dateformat->addValidator(new Validate_InArray($dateFormats));

		foreach ($dateFormats as $value)
		{
			$dateformat->addMultiOption($value, Time::format(time(), $value));
		}
		$dateformat->setValue(User::data('dateformat'));

		return $form;
	}
}
?>