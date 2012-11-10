<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Ban_Model extends Model
{
	protected $name = 'ban';
	protected $prefix = 'ban_';
	protected $primary = 'ban_id';

	protected $reference = array(

			'user'			=>		array(

						'col'			=> 'u.user_id',
						'refCol'		=> 'ban_user',
						'table'			=> 'user u'

			),

			'moderator'		=>		array(

						'col'			=> 'a.user_id',
						'refCol'		=> 'ban_creator',
						'table'			=> 'user a'

			)

	);

	function __construct()
	{
		$this->col = 'ban.*, u.user_id AS u1_id, u.user_name AS u1_name, u.user_email AS u1_email, a.user_id AS u2_id, a.user_name AS u2_name, a.user_email AS u2_email';
	}

	public function getBans($order, $limit, $count)
	{
		$query = $this->select($this->col)->leftJoin('user u', 'u.user_id = ban_user')->leftJoin('user a', 'a.user_id = ban_creator')
					  ->order($order)
					  ->limit($limit, $count);

		return $query;
	}

	public function find()
	{
		if (!$this->primary)
		{
			throw new Exception('Primary key is not set');
		}
		if (func_num_args() == 1)
		{
			$args = func_get_arg(0);

			if (!is_array($args))
			{
				$args = array($args);
			}
		}
		else
		{
			$args = func_get_args();
		}

		$sql = 'SELECT ' . $this->col . '
				FROM ' . $this->name . '
				LEFT JOIN user u ON u.user_id = ban_user
				LEFT JOIN user a ON a.user_id = ban_creator
				WHERE ' . $this->primary . ' IN(' . implode(',', $args) . ')';
		return $this->db->query($sql);
	}

	public function getBanId($userId, $ip, $email = '')
	{
		$sql = 'SELECT ban_id, ban_expire
				FROM ban
				WHERE "' . $ip . '" REGEXP ban_ip
						OR (ban_user = "' . $userId . '" AND ban_user != ' . User::ANONYMOUS . ')';
		if ($email)
		{
			$sql .= ' OR ban_email = "' . $email . '"';
		}
		return $this->db->query($sql);
	}

	public function isBanned($userId, $ip, $email = '')
	{
		$isBanned = false;
		$query = $this->getBanId($userId, $ip, $email);

		if (count($query))
		{
			$isBanned = false;
			$rowset = $query->fetchAll();

			foreach ($rowset as $row)
			{
				if (!$row['ban_expire'])
				{
					$isBanned = true;
					break;
				}
				else
				{
					if (time() > $row['ban_expire'])
					{
						$this->db->query('DELETE FROM ban WHERE ban_id = ' . $row['ban_id']);
					}
					else
					{
						$isBanned = true;
					}
				}
			}
		}
		else
		{
			$isBanned = false;
		}

		return $isBanned;
	}
}
?>