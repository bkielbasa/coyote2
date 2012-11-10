<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Model extends Model
{
	protected $name = 'connector';
	protected $prefix = 'connector_';
	protected $primary = 'connector_id';

	public function getConnectorList($moduleId = null)
	{
		$query = $this->select('connector_id, connector_text, connector_module');
		if ($moduleId !== null)
		{
			$query->where("connector_module = $moduleId");
		}
		
		$query = $query->get();
		$result = array();

		foreach ($query as $row)
		{
			$result[$row['connector_module']][$row['connector_id']] = $row['connector_text'];
		}

		if ($moduleId !== null)
		{
			return $result[$moduleId];
		}
		else
		{
			return $result;
		}
	}
	
	public function getId($connectorName)
	{
		$query = $this->select('connector_id')->where('connector_name = ?', $connectorName)->get();
		if (!count($query))
		{
			return false;
		}
		else
		{
			return $query->fetchField('connector_id');
		}
	}
	
}
?>