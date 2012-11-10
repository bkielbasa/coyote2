<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Walidator sprawdza, czy konto o podanej nazwie istnieje (nazwa jest juz zajeta)
 */
class Validate_Login extends Validate_Abstract implements IValidate
{
	const INVALID_NAME			=		1;
	const USER_ALREADY_EXIST	=		2;
	const INVALID_LOGIN			=		3;

	protected $templates = array(

			self::INVALID_NAME			=> '"%value%" jest niepoprawną nazwą użytkownika',
			self::USER_ALREADY_EXIST	=> 'Konto o loginie "%value%" już istnieje',
			self::INVALID_LOGIN			=> 'Nazwa użytkownika zawiera nieprawidłowe znaki'
	);

	private $userId;
	private $enableDuplicate = false;

	function __construct($userId = 0, $match = null, $enableDuplicate = false)
	{
		$this->userId = $userId;
		$this->setMatch($match);
		$this->setEnableDuplicate($enableDuplicate);
	}

	public function setMatch($match)
	{
		$this->match = $match;
		return $this;
	}

	public function getMatch()
	{
		return $this->match;
	}

	public function setEnableDuplicate($flag)
	{
		$this->enableDuplicate = (bool) $flag;
		return $this;
	}

	public function isDuplicateEnabled()
	{
		return $this->enableDuplicate;
	}

	public function isValid($value)
	{
		$this->setValue($value);
		if (!$this->getMatch() && Config::getItem('user.name'))
		{
			$this->setMatch(Config::getItem('user.name'));
		}

		$user = &Core::getInstance()->load->model('user');

		if (strtolower($value) == 'anonim')
		{
			$this->setError(self::INVALID_NAME);
		}
		else if ($this->getMatch() && !preg_match($this->getMatch(), $value))
		{
			$this->setError(self::INVALID_LOGIN);
		}
		else if (!$this->isDuplicateEnabled())
		{
			$query = $user->getByName($value);

			if (count($query))
			{
				$userId = $query->fetchField('user_id');

				if ($this->userId != $userId)
				{
					$this->setError(self::USER_ALREADY_EXIST);
				}
			}
		}

		return ! $this->hasErrors();
	}
}
?>