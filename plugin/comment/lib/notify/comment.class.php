<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Notify_Comment extends Notify_Abstract implements Notify_Interface
{
	private $subject;
	private $comment;
	private $enableAnonymous;
	private $userName;
	private $enableHtml;
	
	public function setSubject($subject)
	{
		$this->subject = (string) $subject;
		return $this;
	}
	
	public function getSubject()
	{
		return $this->subject;
	}
	
	public function setComment($comment)
	{
		$this->comment = (string) $comment;
		return $this;
	}
	
	public function getComment()
	{
		$filter = new Filter_Html(array(
			'allowedTags'	=> array('b', 'i', 'u', 'del', 'hr', 'sup', 'sub', 'code', 'kbd', 'tt', 'pre', 'strong', 'a' => 'href')
			)
		);

		if (!$this->isHtmlEnabled())
		{
			// usuwamy znaczniki html
			$filter->setAllowedTags(array());
		}
		elseif ($this->isHtmlEnabled() == 1)
		{
			if ($this->getSenderId() == User::ANONYMOUS)
			{
				// usuwamy znaczniki html
				$filter->setAllowedTags(array());	
			}
		}
		
		$value = $filter->filter($this->comment);
		if ($this->getEmailFormat() == self::HTML)
		{
			$value = str_replace('  ', '&nbsp;', nl2br($value));
		}

		return $value;
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
	
	public function setEnableHtml($flag)
	{
		$this->enableHtml = $flag;
		return $this;
	}
	
	public function isHtmlEnabled()
	{
		return $this->enableHtml;		
	}
	
	public function setUserName($userName)
	{
		$this->userName = (string) $userName;
		return $this;
	}
	
	public function getUserName()
	{
		return $this->userName;
	}
	
	public function notify()
	{
		if ($this->isAnonymousEnabled() && User::$id == User::ANONYMOUS)
		{
			$this->setSenderName($this->getUserName());
		}
			
		return parent::notify();
	}
}
?>