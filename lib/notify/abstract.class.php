<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

abstract class Notify_Abstract extends Context implements Notify_Interface
{
	const PLAIN			=		1;
	const HTML			=		2;

	protected $recipients;
	protected $senderId;
	protected $senderName;
	protected $url;
	protected $message;
	protected $emailSubject;
	protected $emailText;
	protected $emailFormat;
	protected $isEmail;

	protected $defaultVars = array();

	function __construct(array $data = array())
	{
		foreach ($data as $method => $value)
		{
			if (method_exists($this, 'set' . $method))
			{
				$this->{'set' . $method}($value);
			}
		}

		if (!$this->getSenderId())
		{
			$this->setSenderId(User::$id);
		}
	}

	public function addRecipient($recipient)
	{
		$this->recipients[] = $recipient;
		return $this;
	}

	public function setRecipients($recipients)
	{
		if (!is_array($recipients))
		{
			$recipients = array($recipients);
		}

		$this->recipients = $recipients;
		return $this;
	}

	public function getRecipients()
	{
		return $this->recipients;
	}

	public function setSenderId($senderId)
	{
		$this->senderId = (string) $senderId;
		return $this;
	}

	public function getSenderId()
	{
		return $this->senderId;
	}

	public function setSenderName($senderName)
	{
		$this->senderName = (string) $senderName;
		return $this;
	}

	public function getSenderName()
	{
		return $this->senderName;
	}

	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}

	public function getUrl($absolute = true)
	{
		return url($this->url, $absolute);
	}

	public function setMessage($message)
	{
		$this->message = (string) $message;
		return $this;
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function setNotifyId($notifyId)
	{
		$this->notifyId = (int) $notifyId;
		return $this;
	}

	public function getNotifyId()
	{
		return $this->notifyId;
	}

	public function setEmailSubject($emailSubject)
	{
		$this->emailSubject = (string) htmlspecialchars_decode($emailSubject);
		return $this;
	}

	public function getEmailSubject()
	{
		return $this->emailSubject;
	}

	public function setEmailText($emailText)
	{
		$this->emailText = (string) $emailText;
		return $this;
	}

	public function getEmailText()
	{
		return $this->emailText;
	}

	public function setEmailFormat($emailFormat)
	{
		$this->emailFormat = $emailFormat;
		return $this;
	}

	public function getEmailFormat()
	{
		return $this->emailFormat;
	}

	public function setIsEmail($flag)
	{
		$this->isEmail = (bool) $flag;
		return $this;
	}

	public function isEmail()
	{
		return $this->isEmail;
	}

	public function notifyEmail()
	{
		if (!$this->isEmail())
		{
			return false;
		}

		$notify = &$this->getModel('notify');
		$query = $notify->user->getUsers($this->getNotifyId(), $this->getRecipients());

		/*
		 * Jezeli ten warunek zostanie spelniony, sa osoby, ktore powinny otrzymac
		 * e-maila z informacja o powiadomieniiu. Nalezy przygotowac tresc wiadomosci
		 */
		if (count($query))
		{
			$this->setEmailSubject($this->assignVars($this->getEmailSubject()));

			$email = &$this->load->library('email');
			$email->setSubject($this->getEmailSubject());
			$email->setFrom(Config::getItem('site.email'), Config::getItem('email.from'));
			$email->setMessage($this->getEmailText());

			if ($this->getEmailFormat()  == self::HTML)
			{
				$email->setContentType('text/html');
			}
			else
			{
				$this->defaultVars = array_map('htmlspecialchars_decode', $this->defaultVars);
			}

			foreach ($query as $row)
			{
				$data = array_merge($this->defaultVars, $row);

				$email->assign($data);
				$email->addRecipient($row['user_email'], $row['user_name']);
			}

			return $email->send();
		}
	}

	protected function initializeVars()
	{
		if (!$this->getSenderName())
		{
			$this->setSenderName(User::data('name'));
		}

		$this->defaultVars = array(
			'sender_name'		=> $this->getSenderName(),
			'sender'			=> $this->getSenderName(),
			'site_url'			=> Url::base(),
			'site_title'		=> Config::getItem('site.title')
		);

		foreach (get_class_methods($this) as $methodName)
		{
			if (substr($methodName, 0, 3) == 'get')
			{
				$reflect = new ReflectionMethod($this, $methodName);
				if (!$reflect->getNumberOfRequiredParameters())
				{
					$value = $this->$methodName();
					if (is_string($value) || is_numeric($value))
					{
						$this->defaultVars += array(strtolower(substr($methodName, 3, strlen($methodName))) => $value);
					}
				}

				unset($reflect);
			}
		}
	}

	protected function assignVars($value)
	{
		$vars = array();

		foreach ($this->defaultVars as $key => $v)
		{
			$vars['{' . $key . '}'] = $v;
		}
		return str_ireplace(array_keys($vars), array_values($vars), $value);
	}

	public function notify()
	{
		$this->initializeVars();
		$sql = array();

		$this->setMessage($this->assignVars($this->getMessage()));

		$notify = &$this->getModel('notify');
		$query = $notify->user->getUsers($this->getNotifyId(), $this->getRecipients());

		$email = &$this->getLibrary('email');

		foreach ($query as $row)
		{
			if ($row['notifier'] & Notify::PROFILE)
			{
				$sql[] = array(
					'header_notify'			=> $this->getNotifyId(),
					'header_sender'			=> $this->getSenderId(),
					'header_recipient'		=> $row['user_id'],
					'header_time'			=> time(),
					'header_message'		=> (string) $this->getMessage(), // tytul - nie tresc
					'header_url'			=> (string) $this->getUrl(false)
				);
			}

			if (($row['notifier'] & Notify::EMAIL) && $row['user_confirm'])
			{
				$data = array_merge($this->defaultVars, $row);

				$email->assign($data);
				$email->addRecipient($row['user_email'], $row['user_name']);
			}
		}

		if ($sql)
		{
			$this->db->multiInsert('notify_header', $sql);
		}

		/*
		 * Wyslanie powiadomienia na e-mail, dla osob, ktore maja uaktywniona
		 * taka opcje
		 */
		if ($email->getRecipient())
		{
			$this->setEmailSubject($this->assignVars($this->getEmailSubject()));

			$email->setSubject($this->getEmailSubject());
			$email->setFrom(Config::getItem('site.email'), Config::getItem('email.from'));
			$email->setMessage($this->getEmailText());

			if ($this->getEmailFormat()  == self::HTML)
			{
				$email->setContentType('text/html');
			}
			else
			{
				$this->defaultVars = array_map('htmlspecialchars_decode', $this->defaultVars);
			}

			$email->send();
		}
	}

	public function __toString()
	{
		return get_class($this);
	}
}
?>