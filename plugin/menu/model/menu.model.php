<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Menu_Item_Model extends Model
{
	protected $name = 'menu_item';
	protected $prefix = 'item_';
	protected $primary = 'item_id';

	public function insert(&$data)
	{
		$parentId = $data['item_parent'];
		$menuId = $data['item_menu'];
		
		if (!$parentId)
		{
			$rightId = (int) $this->select('MAX(right_id)')->where('item_menu = ' . $menuId)->fetchField('MAX(right_id)');
			
			$rightId++;		
		}
		else
		{
			$query = $this->select('left_id, right_id')->where("item_id = $parentId")->get();
			list($leftId, $rightId) = $query->fetchArray();
			
			$this->db->query("UPDATE menu_item SET right_id = right_id + 2 WHERE item_menu = $menuId AND right_id >= $rightId");
			$this->db->query("UPDATE menu_item SET left_id = left_id + 2 WHERE item_menu = $menuId AND left_id > $rightId");
		}
		
		$leftId = $rightId;
		$rightId++;
		
		$data['left_id'] = $leftId;
		$data['right_id'] = $rightId;			
		
		parent::insert($data);
		return $this->db->nextId();
	}
	
	private function move($mode, $itemId)
	{
		$data = $this->select()->where("item_id = $itemId")->get()->fetchAssoc();
		if (!$data)
		{
			return false;
		}
		
		$sql = 'SELECT item_id,
					   item_name,
					   left_id,
					   right_id
				FROM menu_item
				WHERE item_menu = ' . $data['item_menu'] . ' 
					AND ' . ($mode == 'up' ? ('right_id < ' . $data['right_id'] . ' ORDER BY right_id DESC') : ('left_id > ' . $data['left_id'] . ' ORDER BY left_id ASC')) . ' 
				LIMIT 1';
		$query = $this->db->query($sql);
		$target = $query->fetchAssoc();

		if (!$target)
		{
			return false;
		}

		if ($mode == 'up')
		{
			$leftId = $target['left_id'];
			$rightId = $data['right_id'];

			$diffUp = $data['left_id'] - $target['left_id'];
			$diffDown = $data['right_id'] + 1 - $data['left_id'];

			$moveUpLeft = $data['left_id'];
			$moveUpRight = $data['right_id'];
		}
		else
		{
			$leftId = $data['left_id'];
			$rightId = $target['right_id'];

			$diffUp = $data['right_id'] + 1 - $data['left_id'];
			$diffDown = $target['right_id'] - $data['right_id'];

			$moveUpLeft = $data['right_id'] + 1;
			$moveUpRight = $target['right_id'];
		}

		$this->db->lock('menu_item WRITE');
		$sql = "UPDATE menu_item
				SET left_id = left_id + CASE
					WHEN left_id BETWEEN {$moveUpLeft} AND {$moveUpRight} THEN -{$diffUp}
					ELSE {$diffDown}
				END,
				right_id = right_id + CASE
					WHEN right_id BETWEEN {$moveUpLeft} AND {$moveUpRight} THEN -{$diffUp}
					ELSE {$diffDown}
				END
				WHERE
					item_menu = $data[item_menu] AND
					left_id BETWEEN {$leftId} AND {$rightId}
					AND right_id BETWEEN {$leftId} AND {$rightId}";
		$this->db->query($sql);
		$this->db->unlock();

		return true;
	}

	public function down($itemId)
	{
		return $this->move('down', $itemId);
	}

	public function up($itemId)
	{
		return $this->move('up', $itemId);
	}

	public function delete($ids)
	{
		if (!is_array($ids))
		{
			$ids = array($ids);
		}

		foreach ($ids as $id)
		{
			$query = $this->select('left_id, right_id, item_menu')->where("item_id = $id")->get();
			list($leftId, $rightId, $menuId) = $query->fetchArray();
			
			try
			{
				$this->db->begin();
				
				if ($leftId && $rightId)
				{				
					$this->db->query("UPDATE menu_item SET right_id = right_id - 2 WHERE item_menu = $menuId AND right_id > $rightId");
					$this->db->query("UPDATE menu_item SET left_id = left_id - 2 WHERE item_menu = $menuId AND left_id > $rightId");
				}
				
				parent::delete("item_id = $id");
				$this->db->commit();
			}
			catch (Exception $e)
			{
				$this->db->rollback();
			}
		}
	}
	
	public function getItems($menuId)
	{
		$user = &$this->getModel('user');
		
		$query = $this->db->select('m.*,		
		(
			SELECT  COUNT(*) -1
			FROM    menu_item AS parent
			WHERE   parent.item_menu = m.item_menu AND m.left_id BETWEEN parent.left_id AND parent.right_id
		) AS item_depth'	
		)->from('menu_item m');
		$query->innerJoin('menu_group gg', 'gg.group_id IN(' . implode(',', $user->getGroups()) . ')');
		$query->where("m.item_menu = $menuId AND m.item_enable = 1 AND m.item_id = gg.item_id");
		$query->order('m.left_id');
		$query->group('m.item_id');
		
		return $query;
	}
	
	public function getItemsDepth($menuId)
	{
		$query = $this->db->select('node.*,
		(
			SELECT  COUNT(*) -1
			FROM    menu_item AS parent
			WHERE   parent.item_menu = node.item_menu AND node.left_id BETWEEN parent.left_id AND parent.right_id
		) AS item_depth'	
			
		)->from('menu_item AS node');
		$query->where("node.item_menu = $menuId");
		$query->order('node.left_id ASC');
		
		return $query->get();		
	}
	
	public function getItemsAsHtml($menuId)
	{
		$query = $this->getItemsDepth($menuId);				
		$result = array();
		
		foreach ($query as $row)
		{
			$result[$row['item_id']] = str_repeat('&nbsp;', 2 * $row['item_depth']) . $row['item_name'];
		}
		
		return $result;		
	}
}

class Menu_Group_Model extends Model
{
	protected $name = 'menu_group';
}

class Menu_Model extends Model
{
	protected $name = 'menu';
	protected $prefix = 'menu_';
	protected $primary = 'menu_id';

	public $item;
	public $group;

	function __construct()
	{
		$this->item = new Menu_Item_Model;
		$this->group = new Menu_Group_Model;
	}
}
?>