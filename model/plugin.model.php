<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Plugin_Model extends Model
{
	protected $name = 'plugin';
	protected $primary = 'plugin_id';
	protected $prefix = 'plugin_';

	public function getModules($pluginId)
	{
		$sql = "SELECT *
				FROM module
				NATURAL JOIN module_plugin
				WHERE plugin_id = $pluginId";
		return $this->db->query($sql);
	}

	public function getPlugins()
	{
		$query = $this->fetch();
		$result = array();

		while ($row = $query->fetchAssoc())
		{
			$result[$row['plugin_name']] = $row;
		}

		return $result;
	}

	public function readdir()
	{
		$module = array();

		foreach (scandir('plugin/') as $dir)
		{
			if ($dir{0} != '.')
			{
				if (file_exists('plugin/' . $dir . '/' . $dir . '.xml'))
				{
					$xml = simplexml_load_file('plugin/' . $dir . '/' . $dir . '.xml');
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

		if (file_exists('plugin/' . $dir . '/' . $dir . '.xml'))
		{
			$xml = simplexml_load_file('plugin/' . $dir . '/' . $dir . '.xml');
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