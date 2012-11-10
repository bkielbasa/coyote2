<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Walidator sprawdzajacy poprawnosc hasla do konta danego uzytkownika
 */
class Validate_Password extends Validate_Abstract implements IValidate
{
	const INVALID_PASSWORD		=		1;

	protected $templates = array(


				self::INVALID_PASSWORD		=> 'Podano nieprawidłowe hasło'
	);
	protected $userName;

	function __construct($userName = '')
	{
		$this->setUseName($userName);
	}

	public function setUseName($userName)
	{
		$this->userName = $userName;
	}

	public function getUserName()
	{
		return $this->userName;
	}

	public function isValid($value)
	{
		$isValid = false;
		if (!$this->getUserName())
		{
			return false;
		}

		$this->setValue($value);
		$user = &Core::getInstance()->load->model('user');

		if ($result = $user->getByName($this->getUserName())->fetchAssoc())
		{
			$hash = null;

			if (strlen($result['user_password']) == 13
				&& substr($result['user_password'], 0, 2) == 'ab')
			{
				$hash = crypt($value, 'ab');
			}
			else
			{
				$hash = hash('sha256', $result['user_salt'] . $value);
			}
			if ($hash !== $result['user_password'])
			{
				$user->update(array('user_ip_invalid' => sprintf('%s z IP %s (%s)', date('d-m-Y H:i'), User::$ip, gethostbyaddr(User::$ip))), 'user_id = ' . $result['user_id']);
			}
			else
			{
				$isValid = true;
			}
		}

		if (!$isValid)
		{
			$this->setMessage(self::INVALID_PASSWORD);
		}

		return ! $this->hasErrors();
	}
}
?>