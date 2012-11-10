<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Notify_Notifier_Model extends Model
{
	protected $name = 'notify_notifier';


}

class Notify_User_Model extends Model
{
	protected $name = 'notify_user';

	public function getUsers($notifyId, $recipients)
	{
		$query = $this->db->select('u.user_id, u.user_name, u.user_email, u.user_confirm, uu.notifier')->from('notify_user uu')
					  ->innerJoin('user u', 'u.user_id = uu.user_id')
					  ->where('uu.notify_id = ' . $notifyId)
					  ->in('uu.user_id', $recipients);

		return $query->get(); // generowanie SQL
	}

	public function setUsers($userId, $notifyIds)
	{
		$this->delete("user_id = $userId");
		$sql = array();

		foreach ($notifyIds as $notifyId => $notifier)
		{
			$sql[] = array(
				'user_id'		=> (int) $userId,
				'notify_id'		=> (int) $notifyId,
				'notifier'		=> (int) $notifier
			);
		}

		if ($sql)
		{
			$this->db->multiInsert($this->name, $sql);
		}
	}

	public function getNotifiers($userId)
	{
		return $this->select('notify_id, notifier')->where("user_id = $userId")->fetchPairs();
	}
}

class Notify_Header_Model extends Model
{
	protected $name = 'notify_header';
	protected $prefix = 'header_';

	public function getHeaders($userId, $count = 0, $limit = 5)
	{
		$query = $this->select('header_id, header_message, header_url, header_time, header_read, user_id, user_name, user_photo')->where('header_recipient = ?', $userId);
		$query->leftJoin('user', 'user_id = header_sender');
		$query->order('header_id DESC')->limit($count, $limit);

		return $query->get();
	}

	/**
	 * Metda odznacza dane powiadomienie jako przeczytane bazujac na adresie URL oraz ID uzytkownika
	 * @apram string	$url Adres URL bedacy w tabeli notify_header
	 * @param int		$recipient Adres ID uzytkownika, do ktorego powiadomienie jest kierowane
	 */
	public function setReadByUrl($url, $recipient = null)
	{
		if ($recipient === null)
		{
			$recipient = User::$id;
		}

		/*
		 * To zapytanie poszukuje adresu URL podanego w parmetrze $url pomijajac przy tym ewentualny hash
		 * przy linku. Jezeli dane powiadomienie nalezy do danego uzytkownika i nie jest przypisane
		 * ustawiamy status jako "przeczytane" ustawiajac aktualny timestamp
		 */
		$count = count($this->update(array('header_read' => time()), 'header_recipient = ' . $recipient . ' AND header_read = 0 AND header_url LIKE "' . str_replace('*', '%', $url) . '"'));
		if ($count)
		{
			User::$data['user_notify_unread'] -= $count;
		}
	}
}

class Notify_Model extends Model
{
	protected $name = 'notify';
	protected $prefix = 'notify_';
	protected $primary = 'notify_id';

	public $header;
	public $user;
	public $notifier;

	function __construct()
	{
		$this->header = new Notify_Header_Model;
		$this->user = new Notify_User_Model;
		$this->notifier = new Notify_Notifier_Model;
	}

	public function getByTrigger($triggerName)
	{
		$query = $this->select()->from($this->name)
					  ->leftJoin('email', 'email_id = notify_email')
					  ->where('notify_trigger = ?', $triggerName);

		return $query->get();
	}

	public function getByClass($className)
	{
		$query = $this->select()->from($this->name)
			->leftJoin('email', 'email_id = notify_email')
			->where('notify_class = ?', $className);

		return $query->get();
	}
}
?>