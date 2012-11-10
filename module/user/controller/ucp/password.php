<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Password_Controller extends Controller
{
	function main()
	{
		Breadcrumb::add(url('@user'), 'Panel użytkownika');
		Breadcrumb::add(url('@user?controller=Password'), 'Przypominanie hasła');

		$this->form = $this->getForm();

		if ($this->input->isPost())
		{
			if ($this->form->isValid())
			{
				$values = $this->form->getValues();

				$user = &$this->getModel('user');
				$result = $user->select()->where('user_name = "' . $values['name'] . '" AND user_email = "' . $values['email'] . '"')->get()->fetchAssoc();

				if (!$result)
				{
					$this->session->error = 'Podany adres e-mail lub login nie znajduje się w naszej bazie danych!';
					return true;
				}
				if (!$result['user_confirm'])
				{
					$this->session->error = 'Adres e-mail podany w profilu nie został potwierdzony. Hasło nie może zostać przesłane';
					return true;
				}
				if (!$result['user_active'])
				{
					$this->session->error = 'Podane konto zostało dezaktywowane';
					return true;
				}

				$actkey = Text::random(10);

				$email = &$this->getModel('email');

				$emailTpl = $email->find(Config::getItem('email.password'))->fetchAssoc();
				if (!$result)
				{
					throw new Exception('Szablon e-mail służący do potwierdzenia adresu e-mail nie mógł zostać znaleziony. Poinformuj administratora!');
				}

				$email->setValue('id', $result['user_id']);
				$email->setValue('key', $actkey);
				$email->setValue('name', $values['name']);
				$email->setValue('ip', $this->input->getIp());

				if (!$email->send($emailTpl['email_name'], $this->post->email, $values['name']))
				{
					throw new Exception('Nie można wysłać e-maila. Poinformuj administratora!');
				}
				// utworzenie wpisu z kluczem aktywacyjnym konta
				$this->db->insert('actkey', array('actkey' => $actkey, 'user_id' => $result['user_id']));

				$this->session->message = 'Klucz aktywacyjny służący do zmiany hasła, został przesłany na Twój adres e-mail!';
			}
		}

		return true;
	}

	public function change()
	{
		$id = (int)$this->get->id;
		$actkey = (string)$this->get->key;

		$query = $this->db->select()->from('actkey')->where("user_id = '$id' AND actkey = '$actkey'")->get();
		if (!count($query))
		{
			throw new Error(500, 'Klucz aktywacyjny jest nieprawidłowy!');
		}

		Load::loadFile('lib/validate.class.php', false);
		$this->form = new Forms('', Forms::POST);
		$password = $this->form->createElement('password', 'password')->setLabel('Nowe hasło');
		$password->addFilter('strip_tags');
		$password->addFilter(new Filter_Replace('<>"\''));

		$validateString = new Validate_String(false, 3, 50);
		$validateString->setTemplate(Validate_String::TOO_SHORT, 'Podane hasło jest zbyt krótkie');
		$validateString->setTemplate(Validate_String::TOO_LONG, 'Podane hasło jest zbyt długie');

		$password_c = $this->form->createElement('password', 'password_c')->setLabel('Hasło (powtórnie)');
		$password_c->addFilter('strip_tags');
		$password_c->addFilter(new Filter_Replace('<>"\''));
		$password_c->addValidator($validateString);
		$password_c->addValidator(new Validate_Equal($this->post->password));

		$this->form->createElement('submit')->setValue('Zmień hasło');

		if ($this->input->isPost())
		{
			if ($this->form->isValid())
			{
				// generoawnie nowego salta po zmianie hasla
				$salt = uniqid(mt_rand(), true);
				$password = hash('sha256', $salt . $this->post->password);
				$user = &$this->getModel('user');

				$user->update(array('user_password' => $password, 'user_salt' => $salt), 'user_id = ' . $id);
				$this->db->delete('actkey', "user_id = '$id' AND actkey = '$actkey'");

				Box::information('Hasło zostało zmienione', 'Hasło zostało zmienione, możesz się zalogować!', url('@homepage'));
				exit;
			}
		}

		return true;
	}

	private function getForm()
	{
		Load::loadFile('lib/validate.class.php');

		$form = new Forms('', Forms::POST);
		$form->disableErrors();

		$form->createElement('hash', 'hash');
		$username = $form->createElement('text', 'name')->setLabel('Nazwa użytkownika');
		$username->addFilter('trim')->addFilter('strip_tags');
		$username->addFilter(new Filter_Replace('<>"\''));

		$validateMatch = new Validate_Match('/^[0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ.=:|#_ ()[\]^-]+$/');
		$validateMatch->setTemplate(Validate_Match::NOT_MATCH, 'Wartość "%value%" jest nieprawidłową nazwą użytkownika');

		$username->addValidator(new Validate_String(false, 2, 28));
		$username->addValidator($validateMatch);
		$username->addValidator(new Validate_User);

		$form->createElement('text', 'email')
			 ->setLabel('Adres e-mail')
			 ->addValidator(new Validate_Email)
			 ->addFilter('strip_tags')
			 ->addFilter(new Filter_Replace('<>"\''));

		$form->createElement('submit')->setValue('Przypomij hasło')->setLabel('&nbsp;');

		return $form;
	}
}
?>