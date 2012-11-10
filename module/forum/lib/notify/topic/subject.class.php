<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Notify_Topic_Subject extends Notify_Abstract implements Notify_Interface
{
	private $subject;
	/**
	 * Nowa nazwa (tytul) watku
	 */
	private $newSubject;

	public function setSubject($subject)
	{
		$this->subject = (string) $subject;
		return $this;
	}

	public function getSubject()
	{
		return $this->subject;
	}

	public function setNewSubject($subject)
	{
		$this->newSubject = $subject;
		return $this;
	}

	public function getNewSubject()
	{
		return $this->newSubject;
	}
}
?>