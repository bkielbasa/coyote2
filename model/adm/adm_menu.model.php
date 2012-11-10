<?php

class Adm_Menu_Model extends Model
{
	protected $name = 'adm_menu';
	protected $primary = 'menu_id';
	protected $prefix = 'menu_';

	public function delete($where = null)
	{
		list($parent_id, $order) = $this->select('menu_parent, menu_order')->where($where)->get()->fetchArray();
		parent::delete($where);
		$parent_id = (int)$parent_id;

		if ($order)
		{
			$sql = "UPDATE adm_menu SET menu_order = menu_order -1 WHERE menu_parent = $parent_id AND menu_order > $order";
			$this->db->query($sql);
		}
	}

	public function getId($controller, $action = '')
	{
		return $this->db->select('menu_id, menu_parent, menu_auth')->from('adm_menu')->where("menu_controller = '$controller'" . ($action ? " AND menu_action = '$action'" : ''))->get();
	}

	public function getMenu()
	{
		$sql = "SELECT m1.*
					FROM adm_menu AS m1					
					ORDER BY m1.menu_parent ASC, m1.menu_order ASC";
		$query = $this->db->query($sql);

		return $query;
	}

	public function down($menuId)
	{
		$this->db->lock('adm_menu AS t1 WRITE', 'adm_menu AS t2 WRITE', 'adm_menu AS t3 WRITE');

		$sql = "UPDATE adm_menu AS t1, adm_menu AS t3
				JOIN adm_menu AS t2 ON t2.menu_id = $menuId
					SET t1.menu_order = t1.menu_order + 1, t3.menu_order = t3.menu_order -1
				WHERE t1.menu_id = $menuId AND (t3.menu_parent = t2.menu_parent AND t3.menu_order = (t2.menu_order + 1))";
		$this->db->query($sql);

		$this->db->unlock();
	}

	public function up($menuId)
	{
		$this->db->lock('adm_menu AS t1 WRITE', 'adm_menu AS t2 WRITE', 'adm_menu AS t3 WRITE');

		$sql = "UPDATE adm_menu AS t1, adm_menu AS t3
				JOIN adm_menu AS t2 ON t2.menu_id = $menuId
					SET t1.menu_order = t1.menu_order - 1, t3.menu_order = t3.menu_order + 1
				WHERE t1.menu_id = $menuId AND (t3.menu_parent = t2.menu_parent AND t3.menu_order = (t2.menu_order - 1))";
		$this->db->query($sql);

		$this->db->unlock();
	}
}
?>