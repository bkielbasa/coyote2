<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Field_Option_Model extends Model
{
	protected $name = 'field_option';

	public function setFieldOptions($fieldId, $options)
	{
		foreach ($options as $name => $value)
		{
			$sql = "INSERT INTO field_option (option_field, option_name, option_value) VALUES($fieldId, '$name', '$value') ON DUPLICATE KEY UPDATE option_value = '$value'";
			$this->db->query($sql);
		}
	}

}

class Field_Filter_Model extends Model
{
	protected $name = 'field_filter';

	public function getFieldFilters($fieldId)
	{
		$result = array();
		$query = $this->select('filter_id')->where('field_id = ' . $fieldId)->get();

		foreach ($query as $row)
		{
			$result[] = $row['filter_id'];
		}
		return $result;
	}

	public function setFieldFilters($fieldId, $filters)
	{
		$this->delete('field_id = ' . $fieldId);
		$sql = array();
		
		if ($filters)
		{
			foreach ($filters as $filterId)
			{
				$sql[] = array(
					'field_id'			=> $fieldId,
					'filter_id'			=> $filterId
				);
			}

			if ($sql)
			{
				$this->db->multiInsert($this->name, $sql);
			}
		}
	}
}


class Field_Item_Model extends Model
{
	protected $name = 'field_item';
	protected $primary = 'option_id';

	public function setFieldItems($fieldId, array $items)
	{
		$items = array_combine($items['name'], $items['value']);
		$insert = $update = $delete = array();

		$fieldItems = array();

		$query = $this->select()->where("item_field = $fieldId")->get();
		foreach ($query as $row)
		{
			$fieldItems[$row['item_name']] = $row['item_value'];
		}

		$sql = array();

		foreach ((array)array_diff_key($items, $fieldItems) as $key => $value)
		{
			$sql[] = array(
				'item_field'	=> $fieldId,
				'item_name'		=> $key,
				'item_value'	=> $value
			);
		}
		if ($sql)
		{
			$this->db->multiInsert('field_item', $sql);
		}
		foreach ((array)array_diff_assoc($items, $fieldItems) as $key => $value)
		{
			$this->update(array('item_value' => $value), "item_field = $fieldId AND item_name = '$key'");
		}
		foreach ((array)array_diff_key($fieldItems, $items) as $key => $value)
		{
			$this->delete("item_field = $fieldId AND item_name = '$key'");
		}

	}
}

class Field_Model extends Model
{
	protected $name = 'field';
	protected $primary = 'field_id';
	protected $prefix = 'field_';

	protected $reference = array(

			'component'				=> array(


						'table'				=> 'component',
						'col'				=> 'component_id',
						'refCol'			=> 'field_component'
			),
			'module'				=> array(

						'table'				=> 'module',
						'col'				=> 'module_id',
						'refCol'			=> 'field_module'
			)
	);

	public $item;
	public $filter;
	public $option;

	function __construct()
	{
		$this->item = new Field_Item_Model;
		$this->filter = new Field_Filter_Model;
		$this->option = new Field_Option_Model;
	}

	public function down($fieldId)
	{
		$this->db->lock('field AS t1 WRITE', 'field AS t2 WRITE', 'field AS t3 WRITE');

		$sql = "UPDATE field AS t1, field AS t3
				JOIN field AS t2 ON t2.field_id = $fieldId
					SET t1.field_order = t1.field_order + 1, t3.field_order = t3.field_order -1
				WHERE t1.field_id = $fieldId AND (t3.field_module = t2.field_module AND t3.field_order = (t2.field_order + 1))";
		$this->db->query($sql);

		$this->db->unlock();
	}

	public function up($fieldId)
	{
		$this->db->lock('field AS t1 WRITE', 'field AS t2 WRITE', 'field AS t3 WRITE');

		$sql = "UPDATE field AS t1, field AS t3
				JOIN field AS t2 ON t2.field_id = $fieldId
					SET t1.field_order = t1.field_order - 1, t3.field_order = t3.field_order + 1
				WHERE t1.field_id = $fieldId AND (t3.field_module = t2.field_module AND t3.field_order = (t2.field_order - 1))";
		$this->db->query($sql);

		$this->db->unlock();
	}

	public function delete($ids)
	{
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		foreach ($ids as $id)
		{
			$sql = "UPDATE field t1 
					JOIN field t2 ON t2.field_id = $id
					SET t1.field_order = t1.field_order -1 					
					WHERE t1.field_module = t2.field_module AND t1.field_order > t2.field_order";
			$this->db->query($sql);

			parent::delete('field_id = ' . $id);
		}		
	}

	public function getFields($moduleId)
	{
		$sql = "SELECT field_id,
					   field_name,
					   field_text,
					   field_description,
					   field_default,
					   field_required,
					   field_display,
					   field_readonly,
					   field_auth,
					   field_validator,
					   component_name,
					   validator_regexp,
					   validator_message
				FROM (field, component)
				LEFT JOIN validator ON validator_id = field_validator
				WHERE field_module = $moduleId
						AND component_id = field_component
				ORDER BY field_order";
		$query = $this->db->query($sql);
		$field = array();

		while ($row = $query->fetchAssoc())
		{
			$field[$row['field_id']] = $row;
		}

		if ($field)
		{
			$fieldIds = implode(', ', array_keys($field));

			$sql = 'SELECT *
					FROM field_option
					WHERE option_field IN(' . $fieldIds . ')';
			$query = $this->db->query($sql);

			while ($row = $query->fetchAssoc())
			{
				$field[$row['option_field']][$row['option_name']] = $row['option_value'];
			}

			$sql = 'SELECT * 
					FROM field_item 
					WHERE item_field IN(' . implode(', ', array_keys($field)) . ')';
			$query = $this->db->query($sql);

			while ($row = $query->fetchAssoc())
			{
				$field[$row['item_field']]['items'][$row['item_name']] = $row['item_value'];
			}

			$sql = 'SELECT f.*,
						   ff.field_id
					FROM filter f, field_filter ff
					WHERE ff.field_id IN(' . implode(', ', array_keys($field)) . ')
						AND f.filter_id = ff.filter_id';
			$query = $this->db->query($sql);

			while ($row = $query->fetchAssoc())
			{
				$field[$row['field_id']]['filters'][] = $row['filter_name'];
			}
		}

		return $field;
	}
}

?>