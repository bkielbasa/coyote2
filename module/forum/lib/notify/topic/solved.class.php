<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Notify_Topic_Solved extends Notify_Abstract implements Notify_Interface
{
	private $subject;

	public function setSubject($subject)
	{
		$this->subject = (string) $subject;
		return $this;
	}

	public function getSubject()
	{
		return $this->subject;
	}
}
?>