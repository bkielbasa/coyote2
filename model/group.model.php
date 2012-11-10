<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Group_Model extends Model
{
	const SPECIAL = 0;
	const NORMAL = 1;

	protected $name = '`group`';
	protected $primary = 'group_id';
	protected $prefix = 'group_';

	public function insert(&$data)
	{
		parent::insert($data);
		return $this->db->nextId();
	}

	public function fetch($where = null, $order = null, $count = null, $limit = null)
	{
		$sql = "SELECT g.*,
					   COUNT(gg.user_id) AS group_members
				FROM `group` g
				LEFT JOIN auth_group gg ON gg.group_id = g.group_id				
				GROUP BY g.group_id";
		if ($count)
		{
			$sql .= "LIMIT $count, $limit";
		}
		return $this->db->query($sql);
	}

	public function delete($group_arr)
	{
		$delete = array();

		$group_arr = $this->find($group_arr)->fetch();
		foreach ($group_arr as $row)
		{
			if ($row['group_type'] != self::SPECIAL)
			{
				$delete[] = $row['group_id'];
			}
		}

		if ($delete)
		{
			parent::delete('group_id IN(' . implode(',', $delete) . ')');
		}
	}

	public function addUser($group_id, $user_id)
	{
		$query = $this->db->select()->from('auth_group')->where("group_id = $group_id AND user_id = $user_id")->get();
		if (count($query))
		{
			return false;
		}		

		$this->db->insert('auth_group', array('group_id' => $group_id, 'user_id' => $user_id));
		return true;
	}

	public function delUser($group_id, $user_id)
	{
		$leader = $this->select('group_leader')->where("group_id = $group_id")->get()->fetchField('group_leader');
		if ($leader == $user_id)
		{
			// nie mozna usunac uzytkownika jezeli jest liderem grupy
			return false;
		}

		$this->db->delete('auth_group', "group_id = $group_id AND user_id = $user_id");
		return true;
	}

	public function setLeader($group_id, $user_id)
	{
		$this->update(array('group_leader' => $user_id), "group_id = $group_id");
	}

	public function getMembers($group_id, $start = 0, $stop = 50)
	{
		$sql = "SELECT u.*,
					   s.*
				FROM (user u, auth_group g)
				LEFT JOIN session s ON (s.session_user_id = u.user_id)
				WHERE g.group_id = $group_id AND u.user_id = g.user_id
				ORDER BY session_id, 
						 user_id ASC
				LIMIT $start, $stop";
		return $this->db->query($sql);
	}
}
?>