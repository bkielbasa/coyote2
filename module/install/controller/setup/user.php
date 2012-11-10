<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class User_Controller extends Install_Controller
{
	function main()
	{
		if ($this->session->install < 4)
		{
			$this->redirect('Db');
		}
		$this->filter = new Filter_Input;

		if ($this->input->isPost())
		{
			$data['validator'] = array(

				'name'					=> array(
													array('string', false),
													array('match', '/^[0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ.=:|#_ ()[\]^-]+$/')
										),
				'password'				=> array(
													array('string', false, 3, 50)
										),
				'password_c'			=> array(
													array('string', false, 3, 50),
													array('equal', $this->post->password)
										),
				'email'					=> array(
													array('email', true)
										)
			);
			$data['filter'] = array(

				'name'					=> array('trim')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				if (strcasecmp($this->post->name, 'anonim') == 0)
				{
					throw new Exception('Login jest nieprawidłowy');
				}

				$this->session->set($this->filter->getValues());

				$this->session->install = 5;
				$this->redirect('Setup');
			}
		}

		return true;
	}
}
?>