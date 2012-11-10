<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Notify_Page extends Notify_Abstract implements Notify_Interface
{
	private $subject;
	private $location;
	private $log;
	
	public function setSubject($subject)
	{
		$this->subject = (string) $subject;
		return $this;
	}
	
	public function getSubject()
	{
		return $this->subject;
	}
	
	public function setLocation($location)
	{
		$this->location = (string) $location;
		return $this;
	}
	
	public function getLocation()
	{
		return $this->location;
	}
	
	public function setLog($log)
	{
		if (empty($log))
		{
			$log = 'Brak informacji';
		}
		
		$this->log = (string) $log;
		return $this;
	}
	
	public function getLog()
	{
		return $this->log;
	}
}
?>