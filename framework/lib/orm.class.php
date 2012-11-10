<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Prosty ORM
 * Zalazek klasy
 */
class Orm extends Model
{
	public function locate($col, $value)
	{		
		$result = parent::locate($col, $value);
		if ($result->getTotalRows() > 1)
		{ 
			while ($row = $result->fetchObject())
			{ 
				$object[$row->{$col}] = $row;
			}
		}
		else
		{
			$object = $result->fetchObject();
		}

		if (isset($this->references))
		{			
			if (!is_array($value))
			{
				$value = array($value);
			}

			foreach ($this->references as $name => $row)
			{ 
				$query = $this->db->select(isset($row['cols']) ? $row['cols'] : DB::SQL_WILDCARD)->from($row['table'])->in($row['col'], $value)->get(); 
				if (count($value) > 1)
				{
					while ($rowset = $query->fetchObject())
					{
						if (isset($object[$rowset->$row['col']]->$name))
						{
							if (!is_array($object[$rowset->$row['col']]->$name))
							{
								$object[$rowset->$row['col']]->$name = array($object[$rowset->$row['col']]->$name);
							}							
							array_push($object[$rowset->$row['col']]->$name, $rowset);
						}
						else
						{
							$object[$rowset->$row['col']]->$name = $rowset;
						}
					}
				}
				else
				{
					$object->$name = $query->fetch(Db::FETCH_OBJ);				
				}
			}
		}

		return $object;
	}	
}
?>