<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Block_Item_Model extends Model
{
	protected $name = 'block_item';	

	public function getItemList($pluginId)
	{
		$query = $this->select('item_id, item_text')->where('item_plugin = ' . $pluginId)->get();
		$items = array();

		foreach ($query as $row)
		{
			$items[$row['item_id']] = $row['item_text'];
		}

		return $items;
	}
}

class Block_Group_Model extends Model
{
	protected $name = 'block_group';	
}

class Block_Model extends Model
{
	protected $name = 'block';
	protected $primary = 'block_id';
	protected $prefix = 'block_';

	public $group;
	public $item;

	function __construct()
	{
		$this->group = new Block_Group_Model;
		$this->item = new Block_Item_Model;
	}

	public function fetch($where = null, $order = null, $start = null, $limit = null)
	{
		$query = $this->select('block.*, t1.trigger_name, item_data, plugin_id, plugin_name, plugin_text');
		$query->from($this->name);
		$query->leftJoin('plugin', 'plugin_id = block_plugin');
		$query->leftJoin('`trigger` t1', 't1.trigger_id = block_trigger');
		$query->leftJoin('block_item', 'item_id = block_item');
		
		if ($where)
		{
			$query->where($where);
		}
		if ($order)
		{
			$query->order($order);
		}
		if ($start)
		{
			$query->limit($start, $limit);
		}

		return $query;
	}
	
	public function getBlocks()
	{
		$user = &$this->getModel('user');
		
		$query = $this->fetch('block.block_id = g.block_id', 'block.block_order');
		$query->innerJoin('block_group g', 'g.group_id IN(' . implode(',', $user->getGroups()) . ')');
		
		return $query->get();		
	}

	public function delete($ids)
	{
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		
		foreach ($ids as $id)
		{
			$sql = "UPDATE block t1
					LEFT JOIN block t2 ON t2.block_id = $id
					SET t1.block_order = t1.block_order -1
					WHERE t1.block_order > t2.block_order AND t1.block_region = t2.block_region";
			$this->db->query($sql);

			$style = $this->select('block_style')->where("block_id = $id")->get()->fetchField('block_style');
			if ($style)
			{
				@unlink("store/css/$style.css");
			}

			parent::delete("block_id = $id");
		}
	}

	public function down($block_id)
	{
		$this->db->lock('block AS t1 WRITE', 'block AS t2 WRITE', 'block AS t3 WRITE');

		$sql = "UPDATE block AS t1, block AS t3
				JOIN block AS t2 ON t2.block_id = $block_id
					SET t1.block_order = t1.block_order + 1, t3.block_order = t3.block_order -1
				WHERE t1.block_id = $block_id AND (t3.block_region = t2.block_region AND t3.block_order = (t2.block_order + 1))";
		$this->db->query($sql);

		$this->db->unlock();
	}

	public function up($block_id)
	{
		$this->db->lock('block AS t1 WRITE', 'block AS t2 WRITE', 'block AS t3 WRITE');

		$sql = "UPDATE block AS t1, block AS t3
				JOIN block AS t2 ON t2.block_id = $block_id
					SET t1.block_order = t1.block_order - 1, t3.block_order = t3.block_order +1
				WHERE t1.block_id = $block_id AND (t3.block_region = t2.block_region AND t3.block_order = (t2.block_order - 1))";
		$this->db->query($sql);

		$this->db->unlock();
	}

	public function updateRegion($blockId, $region)
	{
		$query = $this->select('block_order, block_region')->where("block_id = $blockId")->get();
		if (!count($query))
		{
			return false;
		}
		$result = $query->fetchAssoc();

		if ($result['block_region'] == $region)
		{
			return false;
		}
		$sql = "SELECT (IFNULL(MAX(block_order), 0) + 1) AS newOrder
				FROM block
				WHERE block_region = '$region'";
		$newOrder = (int)$this->db->query($sql)->fetchField('newOrder');

		$sql = "UPDATE block t1
				LEFT JOIN block t2 ON t2.block_id = $blockId
				SET t1.block_order = t1.block_order -1
				WHERE t1.block_order > t2.block_order AND t1.block_region = t2.block_region";
		$this->db->query($sql);

		$sql = "UPDATE block AS t1
				SET t1.block_region = '$region', t1.block_order = $newOrder
				WHERE t1.block_id = $blockId";
		$this->db->query($sql);
	}
}
?>