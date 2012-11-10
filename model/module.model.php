<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Module_Plugin_Model extends Model
{
	protected $name = 'module_plugin';

	public function getPlugins($moduleId)
	{
		$result = array();

		$sql = 'SELECT plugin.*	
				FROM module_plugin
				JOIN plugin USING(plugin_id)
				WHERE module_id = ' . $moduleId;
		$query = $this->db->query($sql);

		foreach ($query as $row)
		{
			$result[$row['plugin_name']] = $row;
		}

		return $result;
	}	
}

class Module_Config_Model extends Model
{
	protected $name = 'module_config';

	public function getModuleConfig($moduleId, $pageId = null)
	{
		$sql = "SELECT field_name,
					   field_default,
					   config_value
				FROM field
				LEFT JOIN module_config ON config_field = field_id AND config_page " . ($pageId == null ? 'IS NULL' : "= $pageId") . "
				WHERE field_module = $moduleId";
		return $this->db->query($sql);
	}

	public function setModuleConfig($moduleId, $pageId = null, array $data = array())
	{
		$this->db->lock('module_config READ WRITE');

		$sql = "DELETE FROM module_config
				WHERE config_module = $moduleId AND config_page " . ($pageId == null ? 'IS NULL' : "= $pageId");
		$this->db->query($sql);

		$pageId = $this->db->quote($pageId);

		foreach ($data as $fieldName => $fieldValue)
		{			
			if (is_array($fieldValue))
			{
				$fieldValue = implode(',', $fieldValue);
			}
			$sql = "INSERT INTO module_config (config_module, config_field, config_page, config_value) 
						(SELECT $moduleId, field_id, $pageId, '$fieldValue' 
						FROM field 
						WHERE field_module = $moduleId 
							AND field_name = '$fieldName')";
			$this->db->query($sql);
		}

		$this->db->unlock();
	}
}


class Module_Model extends Model
{
	const DISABLED = -1;
	const SYSTEM = 0;
	const NORMAL = 1;

	protected $name = 'module';
	protected $primary = 'module_id';
	protected $prefix = 'module_';

	private $module_arr = array();

	public $plugin;
	public $config;

	function __construct()
	{
		$this->plugin = new Module_Plugin_Model;
		$this->config = new Module_Config_Model;
	}

	public function fetch()
	{
		if (!$this->module_arr)
		{
			$query = parent::fetch();
			while ($row = $query->fetchAssoc())
			{
				$this->module_arr[$row['module_name']] = $row;
			}
		}

		return $this->module_arr;
	}

	public function readdir()
	{
		$module = array();

		foreach (scandir('module/') as $dir)
		{
			if ($dir{0} != '.')
			{
				if (file_exists('module/' . $dir . '/' . $dir . '.xml'))
				{
					$xml = simplexml_load_file('module/' . $dir . '/' . $dir . '.xml');
					$module[$dir] = array(
						'name'		=> $xml->name,
						'text'		=> $xml->text,
						'version'	=> $xml->version,
						'system'	=> $xml->system,
						'author'	=> $xml->author
					);
				}
				else
				{
					$module[$dir] = array();
				}
			}
		}
		return $module;
	}

	public function readData($dir)
	{
		$module = array();

		if (file_exists('module/' . $dir . '/' . $dir . '.xml'))
		{
			$xml = simplexml_load_file('module/' . $dir . '/' . $dir . '.xml');
			$module = array(
				'name'		=> $xml->name,
				'text'		=> $xml->text,
				'version'	=> $xml->version,
				'system'	=> $xml->system,
				'author'	=> $xml->author
			);
		}

		return $module;
	}
}
?>