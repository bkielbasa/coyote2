<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Pm_Model extends Model
{
	const INBOX				=			1;
	const SENTBOX			=			2;
	const TRASH				=			3;

	protected $name = 'pm';
	protected $prefix = 'pm_';
	protected $primary = 'pm_id';


	public function fetch($where = null, $order = null, $limit = null, $count = null)
	{
		$query = $this->select('pm.*,
								pt.pm_message,
								u1.user_name AS u1_name,
								u1.user_photo AS u1_photo,
								u1.user_regdate AS u1_regdate,
								u1.user_email AS u1_email,
								u2.user_name AS u2_name,
								u2.user_photo AS u2_photo');

		if ($where)
		{
			$query->where($where);
		}
		if ($order)
		{
			$query->order($order);
		}
		if ($limit || $count)
		{
			$query->limit($limit, $count);
		}
		$query->leftJoin('user u1', 'u1.user_id = pm.pm_from');
		$query->leftJoin('user u2', 'u2.user_id = pm.pm_to');
		$query->leftJoin('pm_text pt', 'pt.pm_text = pm.pm_text');

		return $query->get();
	}

    public function getUserMessages($userId, $offset, $limit)
    {
        $sql = "SELECT p2.pm_id, p2.pm_subject, p2.pm_time, p2.pm_folder, pm_message, user_name, user_photo, user_id
                FROM (SELECT MAX(pm_id) AS last_id FROM pm WHERE (pm_from = $userId and pm_folder = 2) or (pm_to = $userId and pm_folder = 1) GROUP BY (pm_from + pm_to)) AS p1
                INNER JOIN pm p2 ON p2.pm_id = p1.last_id
                INNER JOIN pm_text ON pm_text.pm_text = p2.pm_text
                INNER JOIN user ON user_id = IF(pm_from = $userId, pm_to, pm_from)
                ORDER BY p2.pm_id DESC
                LIMIT $offset, $limit";

        return $this->db->query($sql)->fetchAll();
    }

    public function getUserMessagesCount($userId)
    {
        $sql = "SELECT COUNT(*)
                FROM (SELECT COUNT(*) FROM pm WHERE (pm_from = $userId AND pm_folder = " . self::SENTBOX . ") OR (pm_to = $userId AND pm_folder = " . self::INBOX.  ") GROUP BY (pm_from + pm_to)) AS t1";

        $result = $this->db->query($sql)->fetchArray();
        return $result[0];
    }

	public function getUnreadMessagesCount($userId)
	{
		$sql = "SELECT p3.pm_from, COUNT(*)
				FROM pm p3
				WHERE (p3.pm_to = $userId AND p3.pm_folder = " . self::INBOX . ") AND (p3.pm_read IS NULL OR p3.pm_read = 0)
				GROUP BY p3.pm_from";

		return $this->db->query($sql)->fetchPairs();
	}

    public function getConversation($userId1, $userId2)
    {
        $sql = "SELECT pm_id, pm_subject, pm_time, pm_message, pm_read, pm_folder, pm_from, pm_to, pm.pm_text, user_name, user_id, user_photo
                FROM pm
                INNER JOIN user ON user_id = IF(pm_folder = 1, $userId2, $userId1)
                INNER JOIN pm_text ON pm_text.pm_text = pm.pm_text
                WHERE (pm_folder = 1 AND pm_from = $userId2 AND pm_to = $userId1) OR (pm_folder = 2 AND pm_from = $userId1 AND pm_to = $userId2)";

        return $this->db->query($sql)->fetchAll();
    }

	public function getUnread($userId)
	{
		return $this->fetch("pm_to = $userId AND pm_read = 0")->fetchAll();
	}

	private function submitMessage(&$message)
	{
		$this->db->insert('pm_text', array(
				'pm_message'						=> $message
			)
		);
		return $this->db->nextId();
	}

	public function submit($recipient, $subject, $message, $trunk = null)
	{
		$this->db->begin();
		$resultId = false;

		try
		{
			/**
			 * Tabela pm_text jest typu MyISAM wiec transakcja tutaj nic nie da
			 */
			$messageId = $this->submitMessage($message);

			/**
			 * Zapisanie wiadomosci w skrzynce odbiorczej adresata wiadomosci
			 */
			$data = array(
				'pm_from'							=> User::$id,
				'pm_to'								=> $recipient,
				'pm_time'							=> time(),
				'pm_subject'						=> $subject,
				'pm_folder'							=> self::INBOX,
				'pm_text'							=> $messageId,
				'pm_trunk'							=> !$trunk ? dechex(mt_rand(0, 0x7fffffff)) : $trunk
			);

			$this->insert($data);
			$id = $this->db->nextId();

			/**
			 * Zapisanie wiadomosci w skrzynce NADAWCZEJ nadawcy wiadomosci
			 */
			$data = array_merge($data, array(
//				'pm_read'							=> 1,
				'pm_folder'							=> self::SENTBOX
				)
			);
			$this->insert($data);
			$resultId = $this->db->nextId();

			$notify = new Notify(

				new Notify_Pm(
					array(
						'recipients' 	=> $recipient,
						'subject' 		=> $subject,
						'body' 			=> $message,
						'url'			=> url('@user?controller=Pm&action=View&id=' . $id) . '#pm' . $id
					)
				)
			);

			$notify->trigger('application.onPmSubmitComplete');
			$this->db->commit();
		}
		catch (Exception $e)
		{
			$this->db->rollback();

			throw new Exception($e->getMessage());
		}

		return $resultId;
	}

	public function submitGroup($groupIds, $subject, $message)
	{
		if (!is_array($groupIds))
		{
			$groupIds = array($groupIds);
		}

		if (!$groupIds)
		{
			return false;
		}
		$sql = 'SELECT COUNT(*)
				FROM auth_group
				WHERE group_id IN(' . implode(',', $groupIds) . ')
					AND user_id != ' . User::ANONYMOUS . '
						AND user_id != ' . User::$id . '
				GROUP BY user_id';
		if ($this->db->query($sql)->fetchField('COUNT(*)'))
		{
			$messageId = $this->submitMessage($message);

			$sql = 'INSERT INTO pm (pm_subject, pm_from, pm_to, pm_time, pm_folder, pm_text)
					SELECT "' . $subject . '", ' . User::$id . ', user_id, ' . time() . ', ' . self::INBOX . ', ' . $messageId . '
					FROM auth_group
					WHERE group_id IN(' . implode(',', $groupIds) . ')
						AND user_id != ' . User::ANONYMOUS . '
							AND user_id != ' . User::$id . '
					GROUP BY user_id';
			$this->db->query($sql);
		}
	}

	/**
	 * Metoda realizuje usuniecie wiadomosci prywanych wraz z ewentualnymi powiadomieniami
	 * @param array		$id		Tablica z ID wiadomosci (moze byc typ int)
	 * @param int		$folder	Id folderu (moze byc null)
	 * @param int		$userId	Id usera (moze byc null - wowczas przypisywane zostanie ID aktualnego usera)
	 */
	public function delete($id = array(), $folder = null, $userId = null)
	{
		if (!is_array($id))
		{
			$id = array($id);
		}
		if ($userId === null)
		{
			$userId = User::$id;
		}
		$where = '';

		if ($folder !== null)
		{
			if (Pm_Model::INBOX == $folder)
			{
				$where = 'pm_to = ' . $userId;
			}
			elseif (Pm_Model::SENTBOX == $folder)
			{
				$where = 'pm_from = ' . $userId;
			}
			$where .= ' AND pm_folder = ' . $folder;
			$where .= ' AND ';
		}

		$where .= 'pm_id IN(' . implode(',', $id) . ')';
		$query = $this->select()->where($where)->get();

		foreach ($query as $row)
		{
			$this->db->delete('notify_header', 'header_recipient = ' . $row['pm_to'] . ' AND header_url LIKE "User/Pm/View?id=' . $row['pm_id'] . '%"');
		}

		parent::delete($where);
	}

	/**
	 * Metoda realizuje usuniecie calego watku dyskusji z danym uzytkownikiem.
	 *
	 * @param array $id
	 * @param null $userId
	 */
	public function deleteThread($id = array(), $userId = null)
	{
		if (!is_array($id))
		{
			$id = array($id);
		}
		if ($userId === null)
		{
			$userId = User::$id;
		}

		$messages = $this->find($id)->fetchAll();
		foreach ($messages as $message)
		{
			$result = $this->getConversation($userId, ($message['pm_to'] == $userId ? $message['pm_from'] : $message['pm_to']));
			$messageId = array();

			foreach ($result as $row)
			{
				$messageId[] = $row['pm_id'];
				$this->db->delete('notify_header', 'header_recipient = ' . $userId . ' AND header_url LIKE "User/Pm/View?id=' . $row['pm_id'] . '%"');
			}

			parent::delete('pm_id IN(' . implode(',', $messageId) . ')');
		}
	}
}
?>