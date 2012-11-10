<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Email_Controller extends Adm
{
	function main()
	{
		$email = &$this->getModel('email');
		
		if ($this->input->isPost())
		{
			$delete = $this->post->delete;
			
			if ($delete)
			{
				$email->delete('email_id IN(' . implode(',', $delete) . ')');
			}
			
			$this->session->message = 'Zaznaczone szablony zostały usunięte';
		}

		$this->email = $email->fetch()->fetch();
		return true;
	}

	public function submit($id = 0)
	{
		$id = (int)$id;
		$result = array();

		$email = &$this->getModel('email');

		if ($id)
		{
			if (!$result = $email->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Podany szablon nie istnieje!');
			}
		}
		$this->format = array(
			Email_Model::PLAIN				=> 'text/plain',
			Email_Model::HTML				=> 'text/html'
		);

		$this->filter = new Filter_Input;

		if ($this->input->isMethod(Input::POST))
		{
			$data['validator'] = array(

				'name'					=> array(
														array('string', false, 1, 50)
										),
				'subject'				=> array(
														array('string', false, 1, 255)
										),
				'description'			=> array(
														array('string', true, 1, 255)
										),
				'text'					=> array(
														array('string', false, 10)
										)
			);
			$data['filter'] = array(

				'name'					=> array('htmlspecialchars'),
				'description'			=> array('htmlspecialchars'),
				'title'					=> array('htmlspecialchars'),
				'text'					=> array(new Filter_Xss),
				'format'				=> array('int')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				load_helper('array');
				$data = array_key_pad($this->filter->getValues(), 'email_');

				if ($id)
				{
					$email->update($data, "email_id = $id");
				}
				else
				{
					$email->insert($data);
				}

				$this->redirect('adm/Email');
			}
		}

		return View::getView('adm/emailSubmit', $result);
	}

	public function send($id)
	{
		$id = (int)$id;
		$email = &$this->getModel('email');

		$result = array();
		if (!$result = $email->find($id)->fetchAssoc())
		{
			throw new AcpErrorException('Brak szablonu e-mail o podanym ID!');
		}

		$this->filter = new Filter_Input;
		if ($this->input->isMethod(Input::POST))
		{
			$data['validator'] = array(
				'email'						=> array(
														array('email')
											)
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				if ($email->send($result['email_name'], $this->post->email))
				{
					$this->message = 'E-mail został prawidłowo wysłany!';
				}
				else
				{
					$this->message = 'Wystąpił błąd podczas przesyłania wiadomości.';
				}
			}
		}

		return View::getView('adm/emailSend', $result);
	}
}
?>