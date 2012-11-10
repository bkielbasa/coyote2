<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Notify_Topic_Move extends Notify_Abstract implements Notify_Interface
{
	private $subject;
	private $reasonName;
	private $reasonText;
	private $forum;

	public function setSubject($subject)
	{
		$this->subject = (string) $subject;
		return $this;
	}
	
	public function getSubject()
	{
		return $this->subject;
	}
	
	public function setReasonName($name)
	{
		if (empty($name))
		{
			$name = 'Moderator nie podał powodu';
		}

		$this->reasonName = (string) $name;
		return $this;
	}
	
	public function getReasonName()
	{
		return $this->reasonName;
	}
	
	public function setReasonText($text)
	{
		if (empty($text))
		{
			$text = 'Moderator nie podał powodu';
		}

		$this->reasonText = (string) $text;
		return $this;
	}
	
	public function getReasonText()
	{
		return $this->reasonText;
	}

	public function setForum($forum)
	{
		$this->forum = (string) $forum;
		return $this;
	}

	public function getForum()
	{
		return $this->forum;
	}
}
?>