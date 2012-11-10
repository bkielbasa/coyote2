<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Psw_Controller extends Controller
{
	function __start()
	{
		if (User::$id == User::ANONYMOUS)
		{
			$this->redirect(Path::connector('login') . '?redirect=' . url('@user?controller=Psw'));
		}
	}

	function main()
	{
		Breadcrumb::add(url('@user'), 'Panel użytkownika');
		Breadcrumb::add(url('@user?controller=Psw'), 'Zmiana hasła');

		$this->form = $this->getForm();

		if ($this->form->isValid())
		{
			// generowanie nowego salta
			$salt = uniqid(mt_rand(), true);
			$password = hash('sha256', $salt . $this->post['password_new']);

			$user = &$this->getModel('user');
			$user->update(array('user_salt' => $salt, 'user_password' => $password), 'user_id = ' . User::$id);

			$user->login(User::$id, $password, true);
			$this->session->message = 'Hasło zostało zmienione';
		}

		$this->form->setDefaults(array('password_o' => ''));

		return true;
	}

	private function getForm()
	{
		Load::loadFile('lib/validate.class.php');

		$form = new Forms('', Forms::POST);
		$form->createElement('hash', 'csrf');

		$validateString = new Validate_String(false, 3);
		$validateString->setTemplate(Validate_String::TOO_SHORT, 'Podane hasło jest zbyt krótkie');
		$validateString->setTemplate(Validate_String::TOO_LONG, 'Podane hasło jest zbyt długie');

		$validateEqual = new Validate_Equal($this->post->password_new);
		$validateEqual->setTemplate(Validate_Equal::NOT_EQUAL, 'Podane hasło musi być identyczne w dwóch polach');

		$password = $form->createElement('password', 'password_new')->setLabel('Hasło');
		$password->setDescription('Hasło musi mieć min. 3 znaki długości');
		$password->addFilter('strip_tags');
		$password->addFilter(new Filter_Replace('<>"\''));
		$password->addValidator($validateString);

		$password_c = $form->createElement('password', 'password_c')->setLabel('Hasło (powtórnie)');
		$password_c->setDescription('Wpisz ponownie swoje hasło');
		$password_c->addFilter('strip_tags');
		$password_c->addFilter(new Filter_Replace('<>"\''));
		$password_c->addValidator($validateString);
		$password_c->addValidator($validateEqual);

		$validatePassword = new Validate_Equal(User::data('password'));
		$validatePassword->setTemplate(Validate_Equal::NOT_EQUAL, 'Podane, dotychczasowe hasło jest nieprawidłowe');

		$password_o = $form->createElement('password', 'password_o')->setLabel('Hasło (stare)');
		$password_o->addFilter('strip_tags');
		$password_o->addFilter(new Filter_Hash('sha256', User::data('salt')));
		$password_o->addValidator($validateString);
		$password_o->addValidator($validatePassword);
		$password_o->setDescription('Jeżeli chcesz zmienić swoje hasło, musisz podać stare');

		$form->createElement('submit', '')->setValue('Zapisz zmiany');

		return $form;
	}
}
?>