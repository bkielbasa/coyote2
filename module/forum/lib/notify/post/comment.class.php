<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Notify_Post_Comment extends Notify_Abstract implements Notify_Interface
{
	private $comment;
	private $subject;
	
	public function setComment($comment)
	{
		$this->comment = (string) $comment;
		return $this;
	}
	
	public function getComment()
	{
		/*
		 * Jezeli e-mail jest w formie "czystego" tekstu, chcemy aby zostal
		 * wyswietlony w formie oryginalnego tekstu
		 */
		if ($this->getEmailFormat() == self::PLAIN)
		{
			$this->comment = htmlspecialchars_decode($this->comment);			
		}
		
		return $this->comment;
	}

	/**
	 * Ustawia temat watku. Temat jest juz przefiltrowany pod katem kodu xhtml
	 * @param string $subject	Temat watku
	 */
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