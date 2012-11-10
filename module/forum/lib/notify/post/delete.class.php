<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Notify_Post_Delete extends Notify_Abstract implements Notify_Interface
{
	private $subject;
	private $reasonName;
	private $reasonText;

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
		$this->reasonName = (string) $name;
		return $this;
	}
	
	public function getReasonName()
	{
		return $this->reasonName;
	}
	
	public function setReasonText($text)
	{
		$this->reasonText = (string) $text;
		return $this;
	}
	
	public function getReasonText()
	{
		return $this->reasonText;
	}
}
?>