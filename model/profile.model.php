<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Profile_Filter_Model extends Model
{
	protected $name = 'profile_filter';

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

class Profile_Data_Model extends Model
{
	public function getFieldsFormData($userId)
	{
		$sql = "SELECT field_name,
					   field_text,
					   field_description,
					   field_length,
					   field_min,
					   field_max,
					   field_default,
					   data_value,
					   component_name,
					   option_name,
					   option_value
				FROM (profile_field)
				LEFT JOIN profile_data ON data_field = field_id AND data_user = $userId
				LEFT JOIN profile_component ON component_id = field_component
				LEFT JOIN profile_option ON option_field = field_id AND option_name = data_value
				ORDER BY field_order";
		return $this->db->query($sql);				   

	}

	public function getFormValues($userId)
	{
		$data = array();

		$sql = "SELECT data_value,
					   field_name,
					   option_value
				FROM (profile_data, profile_field)
				LEFT JOIN profile_option ON option_field = data_field AND option_name = data_value
				WHERE data_user = $userId
					AND field_id = data_field";
		$query = $this->db->query($sql);

		while ($row = $query->fetchAssoc())
		{
			$data[$row['field_name']] = $row['data_value'];
		}

		return $data;
	}

	public function getValues($userId)
	{
	}

	public function setFieldsData($userId, array $data = array())
	{
		foreach ($data as $fieldName => $fieldValue)
		{			
			if (is_array($fieldValue))
			{
				$fieldValue = implode(',', $fieldValue);
			}
			$sql = "INSERT INTO profile_data (data_user, data_field, data_value)
					(
						SELECT $userId, field_id, '$fieldValue' 
						FROM profile_field
						WHERE field_name = '$fieldName'
					) ON DUPLICATE KEY UPDATE data_value = '$fieldValue'";
			$this->db->query($sql);
		}
	}

	public function setFieldData($userId, $fieldName, $fieldValue)
	{
		if (is_array($fieldValue))
		{
			$fieldValue = implode(',', $fieldValue);
		}
		$sql = "INSERT INTO profile_data (data_user, data_field, data_value)
				(
					SELECT $userId, field_id, '$fieldValue' 
					FROM profile_field
					WHERE field_name = '$fieldName'
				) ON DUPLICATE KEY UPDATE data_value = '$fieldValue'";
		$this->db->query($sql);
	}
}

class Profile_Model extends Model
{
	public $component;
	public $field;
	public $option;
	public $filter;
	public $data;

	function __construct()
	{
		$this->component = new Profile_Component_Model;
		$this->field = new Profile_Field_Model;
		$this->option = new Profile_Option_Model;
		$this->filter = new Profile_Filter_Model;
		//$this->data = new Profile_Data_Model;
	}
}
?>