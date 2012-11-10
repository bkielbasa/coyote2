<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Session_Log_Model extends Model
{
	protected $name = 'session_log';

	public function filter($userName, $sessionStart, $sessionStop, $userIp, $order, $count, $limit)
	{
		$query = $this->select('SQL_CALC_FOUND_ROWS session_log.*, user_name')->leftJoin('user', 'user_id = log_user');

		if ($userName)
		{
			$query->where('log_user IN(SELECT user_id FROM user WHERE user_name LIKE ?)', str_replace('*', '%', $userName));
		}
		if ($sessionStart)
		{
			$query->where('log_start > ' . time() - (int) $sessionStart);
		}
		if ($sessionStop)
		{
			$query->where('log_stop > ' . time() - (int) $sessionStop);
		}
		if ($userIp)
		{
			$query->where('log_ip LIKE ?', str_replace('*', '%', $userIp));
		}

		$query->order($order);
		$query->limit($count, $limit);

		return $query;
	}
}


class Session_Model extends Model
{
	protected $name = 'session';
	protected $primary = 'session_id';
	protected $prefix = 'session_';

	protected $reference = array(

			'user'			=>		array(

						'col'			=> 'u.user_id',
						'refCol'		=> 'session_user_id',
						'table'			=> 'user u'

			)

	);

	public function getByUserId($id)
	{
		$sql = 'SELECT *
				FROM user
				WHERE user_id = "' . $id . '"';
		return $this->db->query($sql);
	}

	public function getBySessionId($sid)
	{
		$sql = 'SELECT *
				FROM session, user
				WHERE session_id = "' . $sid . '"
						AND user_id = session_user_id';
		return $this->db->query($sql);
	}

	public function getByIp($ip)
	{
		$sql = 'SELECT session_id,
					   session_user_id,
					   session_browser
				FROM session
				WHERE session_ip = "' . $ip . '"
				ORDER BY session_user_id';
		return $this->db->query($sql);
	}

	public function update($session_id, $user_id, $ip, $page, $robot, $browser)
	{
		$time = time();

		$insertSql = array(
			'session_id'		=> $session_id,
			'session_ip'		=> $ip,
			'session_user_id'	=> $user_id,
			'session_start'		=> $time,
			'session_stop'		=> $time,
			'session_page'		=> $page,
			'session_robot'		=> $robot,
			'session_browser'	=> $browser
		);

		$sql = 'INSERT INTO ' . $this->name . ' ' . $this->db->sqlBuildQuery('INSERT', $insertSql);
		$sql .= ' ON DUPLICATE KEY UPDATE session_stop = "' . $insertSql['session_stop'] . '", ' . (!$this->input->isAjax() ? 'session_page = "' . $page . '", ' : '') . ' session_ip = "' . $ip . '", session_browser = "' . $browser . '", session_robot = "' . $robot . '", session_user_id = "' . $user_id . '"';

		return $this->db->query($sql);
	}

	public function gc()
	{
		$time = time();
		parent::delete('session_stop < ' . ($time - Config::getItem('session.length')));

		/* ustawienie konfiguracji: zapis czas ostatniego wywolania funkcji GC */
		$this->db->query('CALL SET_CONFIG("session.last_gc", "' . $time . '")');
	}

	/**
	 * Sprawdza, czy zalogowany jest uzytkownik odpowiadajy wzorcowi
	 * @param string $session_id ID sesji
	 * @param int $user_id ID uzytkownika
	 * @param string $ip IP uzytkownika
	 * @return bool|string ID sesji jezeli taki uzytkownik jest w systemie lub FALSE - jezeli nie
	 */
	public function isSession($session_id = null, $user_id = null, $ip = null)
	{
		$where_arr = array();

		$sql = 'SELECT session_id
				FROM session
				WHERE ';
		if ($session_id != null)
		{
			$where_arr[] = 'session_id = "' . $session_id . '" ';
		}
		if ($ip != null)
		{
			$where_arr[] = '"' . $ip . '" REGEXP session_ip ';
		}
		if ($user_id != null && $user_id != User::ANONYMOUS)
		{
			$where_arr[] = 'session_user_id = ' . $user_id;
		}
		if ($where_arr)
		{
			$sql .= implode(' AND ', $where_arr);
			return $this->db->query($sql)->fetchField('session_id');
		}
		else
		{
			return false;
		}
	}
}
?>