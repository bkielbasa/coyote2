<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Walidator sprawdza poprawnosc nazwy uzytkownika
 * Sprawdza m.in. czy konto o danym loginie istnieje w bazie danych
 */
class Validate_User extends Validate_Abstract implements IValidate
{
	const INVALID_NAME		=		1;
	const NOT_EXIST			=		2;
	const NOT_ACTIVE		=		3;
	const DISALLOW_NAME		=		4;
	const INVALID_LOGIN		=		5;
	const INVALID_IP		=		6;
	const BAN				=		7;

	protected $templates = array(

			self::INVALID_NAME			=> '"%value%" jest niepoprawną nazwą użytkownika',
			self::NOT_EXIST				=> 'Konto o loginie "%value%" nie istnieje',
			self::NOT_ACTIVE			=> 'Konto o loginie "%value%" nie jest aktywne',
			self::DISALLOW_NAME			=> 'Nazwa użytkownika "%value%" nie jest prawidłowa',
			self::INVALID_LOGIN			=> 'Nazwa użytkownika zawiera nieprawidłowe znaki',
			self::INVALID_IP			=> 'Twój adres IP nie jest na liście dozwolonych adresów IP z jakich możesz mieć dostęp do tego konta',
			self::BAN					=> 'Konto "%value%" zostało zablokowane przez administratorów'

	);
	protected $enableAnonymous = false;
	protected $enableIpValidate = true;
	protected $enableBanValidate = true;
	protected $disallow = array();

	function __construct($enableAnonymous = false, $enableIpValidate = false, $enableBanValidate = true)
	{
		$this->setEnableAnonymous($enableAnonymous);
		$this->setEnableIpValidate($enableIpValidate);
		$this->setEnableBanValidate($enableBanValidate);
	}

	public function setEnableAnonymous($flag)
	{
		$this->enableAnonymous = (bool) $flag;
		return $this;
	}

	public function isAnonymousEnabled()
	{
		return $this->enableAnonymous;
	}

	public function setEnableIpValidate($flag)
	{
		$this->enableIpValidate = (bool) $flag;
		return $this;
	}

	public function isIpValidateEnabled()
	{
		return $this->enableIpValidate;
	}

	public function setEnableBanValidate($flag)
	{
		$this->enableBanValidate = (bool) $flag;
		return $this;
	}

	public function isBanValidateEnabled()
	{
		return $this->enableBanValidate;
	}

	public function addDisallow($disallow)
	{
		$this->disallow[] = Text::toLower($disallow);
		return $this;
	}

	public function setDisallow(array $disallow)
	{
		$disallow = array_map(array('Text', 'toLower'), $disallow);

		$this->disallow = $disallow;
		return $this;
	}

	public function getDisallow()
	{
		return $this->disallow;
	}

	public function isValid($value)
	{
		if (strlen(trim($value)))
		{
			$this->setValue($value);

			$user = &Core::getInstance()->load->model('user');

			if (strtolower($value) == 'anonim' && !$this->isAnonymousEnabled())
			{
				$this->setError(self::INVALID_NAME);
			}
			else if (in_array(Text::toLower($value), $this->getDisallow()))
			{
				$this->setError(self::DISALLOW_NAME);
			}
			else if (Config::getItem('user.name') && !preg_match(Config::getItem('user.name'), $value))
			{
				$this->setMessage(self::INVALID_LOGIN);
			}
			else
			{
				$query = $user->getByName($value);

				if (!count($query))
				{
					$this->setError(self::NOT_EXIST);
				}
				else
				{
					$isActive = $query->fetchField('user_active');

					if (!$isActive)
					{
						$this->setError(self::NOT_ACTIVE);
					}
				}

				if ($this->isIpValidateEnabled())
				{
					if ($query->fetchField('user_ip_access'))
					{
						$forbidden = true;
						$ip = explode('.', $query->fetchField('user_ip_access'));

						for ($i = 0; $i < count($ip); $i += 4)
						{
							$regIp = str_replace('*', '.*', str_replace('.', '\.', implode('.', array_slice($ip, $i, 4))));

							if (preg_match('#^' . $regIp . '$#', User::$ip))
							{
								$forbidden = false;
								break;
							}
						}

						if ($forbidden)
						{
							$this->setError(self::INVALID_IP);
						}
					}
				}

				if ($this->isBanValidateEnabled())
				{
					$ban = &Core::getInstance()->load->model('ban');

					if ($ban->isBanned($query->fetchField('user_id'), User::$ip, $query->fetchField('user_email')))
					{
						$this->setError(self::BAN);
					}
				}
			}
		}

		return ! $this->hasErrors();
	}
}
?>