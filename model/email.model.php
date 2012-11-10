<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Email_Model extends Model
{
	const PLAIN			=		1;
	const HTML			=		2;

	protected $name = 'email';
	protected $primary = 'email_id';
	protected $prefix = 'email_';

	private $data = array();

	public function send($emailName, $recipient, $name = '', array $data = array())
	{
		if (!$recipient)
		{
			throw new Exception('Recipient param is missing!');
		}

		$query = $this->select()->where("email_name = '$emailName'")->get();
		if (!count($query))
		{
			return false;
		}
		$result = $query->fetchAssoc();

		$email = &$this->load->library('email');
		$email->setSubject($result['email_subject']);
		$email->setFrom(Config::getItem('site.email'), Config::getItem('email.from'));
		$email->setMessage($result['email_text']);

		if ($result['email_format']  == self::HTML)
		{
			$email->setContentType('text/html');
		}
		if (is_array($recipient))
		{
			$email->setRecipient($recipient);
		}
		else
		{
			$email->addRecipient($recipient, $name);
		}
		$data = array_merge($data, $this->data);
		$defaultVars = array(
			'SITE_TITLE'			=> Config::getItem('site.title'),
			'SITE_URL'				=> Url::base(),
			'SITE_EMAIL'			=> Config::getItem('site.email')
		);

		foreach ($defaultVars as $key => $value)
		{
			if (!isset($data[$key]))
			{
				$data[$key] = $value;
			}
		}

		$email->assign($data);
		return $email->send();
	}

	public function sendToUser($emailName, $userId, array $data = array())
	{
		$user = &$this->load->model('user');
		$query = $user->select()->where("user_id = $userId")->get();

		$result = $query->fetchAssoc();
		if (!$result['user_confirm'])
		{
			return false;
		}
		foreach ($result as $key => $value)
		{
			$data[substr($key, 5)] = $value;
		}

		return $this->send($emailName, $result['user_email'], $result['user_name'], $data);
	}

	public function setValue($key, $value = '')
	{
		if (!is_array($key))
		{
			$this->data[$key] = $value;
		}
		else
		{
			foreach ($key as $k => $v)
			{
				$this->data[$k] = $v;
			}
		}
	}

	public function getValue($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : false;
	}
}
?>