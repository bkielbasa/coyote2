<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Notify extends Context
{
	const PROFILE			=		1;
	const EMAIL				=		2;

	private $notify = array();

	function __construct(Notify_Interface $notify = null)
	{
		if (null != $notify)
		{
			$this->addNotify($notify);
		}
	}

	public function addNotify(Notify_Interface $notify)
	{
		$this->notify[] = $notify;
		return $this;
	}

	public function getNotifies()
	{
		return $this->notify;
	}

	public function trigger($triggerName = null)
	{
		$notify = &$this->getModel('notify');

		if ($triggerName)
		{
			$result = $notify->getByTrigger($triggerName)->fetchAll();

			if (!$result)
			{
				Log::add('Notify: brak powiadomienia. Trigger: ' . $triggerName, E_ERROR);
				return false;
			}
			$index = 0;

			foreach ($this->getNotifies() as $row)
			{
				if ($row->getRecipients())
				{
					$row->setNotifyId($result[$index]['notify_id']);
					$row->setIsEmail((bool) @$result[$index]['email_id']);

					$row->setMessage($result[$index]['notify_message']);

					if ($row->isEmail())
					{
						$row->setEmailSubject($result[$index]['email_subject']);
						$row->setEmailText($result[$index]['email_text']);

						$row->setEmailFormat($result[$index]['email_format']); // html lub plain
					}

					$row->notify();
				}

				$index++;
			}
		}
		else
		{
			foreach ($this->getNotifies() as $row)
			{
				if ($row->getRecipients())
				{
					$result = $notify->getByClass((string) $row)->fetchAssoc();

					$row->setNotifyId($result['notify_id']);
					$row->setIsEmail((bool) @$result['email_id']);

					$row->setMessage($result['notify_message']);

					if ($row->isEmail())
					{
						$row->setEmailSubject($result['email_subject']);
						$row->setEmailText($result['email_text']);

						$row->setEmailFormat($result['email_format']); // html lub plain
					}

					$row->notify();
				}
			}
		}
	}
}
?>