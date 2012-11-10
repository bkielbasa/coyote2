<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class User_Model extends Model
{
	protected $name = 'user';
	protected $prefix = 'user_';
	protected $primary = 'user_id';

	public function insert($data)
	{
		$time = time();

		$data += array(
			'user_regdate'		=> $time,
			'user_lastvisit'	=> $time,
			'user_ip'			=> $this->input->getIp()
		);

		parent::insert($data);
		return $this->db->nextId();
	}

	public function login($userId, $password, $autoLogin = true)
	{
		// ustawienie informacji o czasie logowania oraz IP
		$ip = sprintf("%s (%s, %s) %s", User::$ip, @gethostbyaddr(User::$ip), $this->input->server('HTTP_X_FORWARDED_FOR'), $this->input->getUserAgent());
		$this->update(array('user_ip_login' => $ip, 'user_lastvisit' => time()), 'user_id = ' . $userId);

		// faktyczne zalogowanie do systemu
		$this->db->update('session', array('session_user_id' => $userId), 'session_id = "' . User::$sid . '"');

		$sessiondata = array(
			'user_id'	=> $userId,
			'key'		=> md5($password)
		);

		// zapamietane usera. ustawienie ciastka
		$this->output->setCookie('data', User::encrypt(serialize($sessiondata)), $autoLogin ? strtotime('+1 year') : 0);
	}

	public function logout()
	{
		$this->db->update('user', array('user_lastvisit' => time()), 'user_id = ' . User::$id);
		// faktyczne wylogowanie z systemu
		$this->db->update('session', array('session_user_id' => User::ANONYMOUS), 'session_id = "' . User::$sid . '"');

		// usuniecie ciastka
		$this->output->setCookie('data', '', time() - 100000);
	}

	public function getGroups()
	{
		static $groups;

		if (!$groups)
		{
			$query = $this->db->query('SELECT group_id FROM auth_group WHERE user_id = ?', User::$id);
			$groups = $query->fetchCol();
		}

		return $groups;
	}

	private function getSqlField($fieldId)
	{
		$sql = 'SELECT field_name,
					   field_default,
					   component_name
				FROM field, component
				WHERE field_id = ' . $fieldId . '
						AND component_id = field_component';
		$query = $this->db->query($sql);
		$result = $query->fetchAssoc();

		$sql = 'SELECT option_name,
					   option_value
				FROM field_option
				WHERE option_field = ' . $fieldId;
		$query = $this->db->query($sql);
		$options = array();

		foreach ($query as $row)
		{
			$options[$row['option_name']] = $row['option_value'];
		}

		$sql = 'SELECT item_name,
					   item_value
				FROM field_item
				WHERE item_field = ' . $fieldId;
		$query = $this->db->query($sql);
		$items = array();

		foreach ($query as $row)
		{
			$items[$row['item_name']] = $row['item_value'];
		}

		$itemArr = array();
		foreach ($items as $name => $value)
		{
			$itemArr[] = "'" . $name . "'";
		}

		$columnType = 'VARCHAR(255)';
		switch ($result['component_name'])
		{
			case 'textarea':

				$columnType = 'VARCHAR';

				if (!empty($options['max']))
				{
					$columnType .= '(' . $options['max'] . ')';
				}
				else
				{
					$columnType .= '(255)';
				}

				break;

			case 'text':
				$columnType = 'VARCHAR';

				if (!empty($options['length']))
				{
					$columnType .= '(' . $options['length'] . ')';
				}
				else
				{
					$columnType .= '(255)';
				}

				break;

			case 'select':
			case 'radio':

				$columnType = 'ENUM(';

				if ($itemArr)
				{
					$columnType .= implode(',', $itemArr);
				}

				$columnType .= ')';

				break;

			case 'checkbox':

				$columnType = 'TINYINT(1) UNSIGNED';
				$result['field_default'] = (int)$result['field_default'];
				break;

			case 'multicheckbox':

				$columnType = 'SET(';

				if ($itemArr)
				{
					$columnType .= implode(',', $itemArr);
				}

				$columnType .= ')';

				break;

			case 'photo':

				$columnType = 'VARCHAR(20)';

			break;

			case 'date':

				$columnType = 'VARCHAR(10)';
			break;

			case 'int':

				$columnType = $options['dataType'];

				if ((int) $options['min'] >= 0)
				{
					$columnType .= ' UNSIGNED';
				}


			break;
		}

		return '`' . $result['field_name'] . '` ' . $columnType . ' NOT NULL DEFAULT "' . $result['field_default'] . '"';
	}

	public function createField($fieldId)
	{
		$sql = 'ALTER TABLE user ADD COLUMN ' . $this->getSqlField($fieldId);
		$this->db->query($sql);
	}

	public function changeField($fieldId, $oldFieldName)
	{
		$sql = "ALTER TABLE user CHANGE $oldFieldName " . $this->getSqlField($fieldId);
		$this->db->query($sql);
	}

	public function deleteField($fieldName)
	{
		$sql = "ALTER TABLE user DROP COLUMN $fieldName";
		$this->db->query($sql);
	}

	public function filter($userId = null, $userName = null, $email = null, $status = null, $active = null, $confirm = null, $regDate = null, $lastVisit = null, $ip = null, $loginIp = null, $order = null, $count = null, $limit = null)
	{
		$query = $this->select(($limit !== null || $count !== null ? 'SQL_CALC_FOUND_ROWS' : '') . ' *');
		$query->leftJoin('session', 'session_user_id = user_id');
		$query->where('user_id > 0');

		if ($userId)
		{
			$query->where('user_id = ?', $userId);
		}
		else
		{
			if ($email)
			{
				$query->where('user_email LIKE ?', str_replace('*', '%', $email));
			}
			if ($status > -1)
			{
				if ($status == 1)
				{
					$query->where('session_id IS NULL');
				}
				elseif ($status == 2)
				{
					$query->where('session_id IS NOT NULL');
				}
			}
			if ($active > -1)
			{
				$query->where('user_active = ?', $active);
			}
			if ($confirm > -1)
			{
				$query->where('user_confirm = ?', $confirm);
			}
			if ($regDate)
			{
				$query->where('user_regdate > ?', time() - $regDate);
			}
			if ($lastVisit)
			{
				$query->where('user_lastvisit > ?', time() - $lastVisit);
			}
			if ($userName)
			{
				$query->where('user_name LIKE ?', str_replace('*', '%', $userName));
			}
			if ($ip)
			{
				$query->where('(user_ip LIKE ? OR user_ip_login LIKE ?)', str_replace('*', '%', $ip), str_replace('*', '%', $ip));
			}
		}

		if ($order)
		{
			$query->order($order);
		}
		if ($count || $limit)
		{
			$query->limit($count, $limit);
		}

		return $query;
	}

	public function getFoundRows()
	{
		return (int)$this->db->query('SELECT FOUND_ROWS() AS totalItems')->fetchField('totalItems');
	}
}
?>