<?php

class Adm_Session_Model extends Model
{
	protected $name = 'adm_session';
	protected $primary = 'session_id';
	protected $prefix = 'session_';

	public function insert($data)
	{
		$sql = 'INSERT INTO ' . $this->name . ' ' . $this->db->sqlBuildQuery('INSERT', $data);
		$sql .= ' ON DUPLICATE KEY UPDATE session_user_id = ' . $data['session_user_id'];

		return $this->db->query($sql);
	}

	public function get()
	{
		if ($data = $this->db->select('s.*')->from('adm_session ss, session s')->where('ss.session_user_id = ' . User::$id . ' AND ss.session_id = "' . User::$sid . '" AND s.session_id = ss.session_id AND s.session_user_id = ss.session_user_id')->get()->fetchObject())
		{
			$this->update(array('session_time' => time()), 'session_id = "' . User::$sid . '"');
		}
		return $data;
	}

	public function gc()
	{
		$this->delete('session_time < ' . strtotime('-30 minutes'));
		return $this->get();
	}

	public function fetch()
	{
		return $this->db->select('s.*, user_name')->from('adm_session ss, session s, user')->where('s.session_id = ss.session_id AND user_id = ss.session_user_id')->order('s.session_stop DESC')->get();
	}
}
?>