<?php

class Notify_Pm extends Notify_Abstract implements Notify_Interface
{
	protected $body;
	protected $subject;

	public function setSubject($subject)
	{
		$this->subject = (string) $subject;
		return $this;
	}
	
	public function getSubject()
	{
		return $this->subject;
	}
	
	public function setBody($body)
	{
		$this->body = (string) $body;
		return $this;
	}
	
	public function getBody()
	{
		return $this->body;
	}
}
?>