<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Auth_Model extends Model
{
	protected $name = 'auth_option';

	public function getOptions()
	{
		$query = $this->fetch();		

		while ($row = $query->fetchAssoc())
		{
			$acl_options[$row['option_id']] = $row;
		}
		return $acl_options;
	}

	public function setOption($text, $value, $label)
	{
		$sql = "INSERT INTO auth_option (option_text, option_label, option_default) VALUES('$text', '$label', '$value') ON DUPLICATE KEY UPDATE option_default = '$value'";
		$this->db->query($sql);		
	}

	public function user($user_id)
	{
		$sql = "SELECT data_option,
					   data_value
				FROM auth_data, auth_group
				WHERE user_id = $user_id
						AND data_group = group_id
				ORDER BY data_value";

		return $this->db->query($sql)->fetch();
	}

	public function save(&$data, $user_id)
	{
		if ($data)
		{
			$sql = 'UPDATE user
					SET user_permission = "' . base64_encode(serialize($data)) . '"
					WHERE user_id = ' . $user_id;

			return $this->db->query($sql);
		}
		else
		{
			return false;
		}
	}

	public function getGroupData($group_id)
	{
		$sql = "SELECT option_id,
					   option_text,
					   option_label,
					   data_option,
					   data_value
				FROM auth_option, auth_data
				WHERE data_group = $group_id
						AND option_id = data_option";
		return $this->db->query($sql);				
	}

	public function setGroupData($group_id, &$data)
	{
		foreach ($data as $k => $v)
		{
			$this->db->update('auth_data', array('data_value' => $v), "data_group = $group_id AND data_option = $k");
		}
	}
}
?>