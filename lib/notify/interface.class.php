<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

interface Notify_Interface
{
	public function addRecipient($recipient);
	public function setRecipients($recipients);
	public function getRecipients();
	public function setSenderId($senderId);
	public function getSenderId();
	public function setSenderName($senderName);
	public function getSenderName();
	public function setUrl($url);
	public function getUrl();	
	public function setMessage($message);
	public function getMessage();
	public function setEmailText($emailText);
	public function getEmailText();
	public function setEmailFormat($emailFormat);
	public function getEmailFormat();
	public function setEmailSubject($emailSubject);
	public function getEmailSubject();
	public function setIsEmail($flag);
	public function isEmail();
	
	public function notify();
	public function notifyEmail();
}
?>