<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Scheduler_Model extends Model
{
	protected $name = 'scheduler';
	protected $prefix = 'scheduler_';
	protected $primary = 'scheduler_id';

	public function submit($moduleId, $name, $className, $method, $frequency, $description = '')
	{
		parent::insert(array(
			'scheduler_module'		=> $moduleId,
			'scheduler_name'		=> $name,
			'scheduler_class'		=> $className,
			'scheduler_method'		=> $method,
			'scheduler_frequency'	=> $frequency,
			'scheduler_description'	=> $description
			)
		);
	}

	public function setFrequency($name, $frequency)
	{
		$query = $this->select('scheduler_id')->where('scheduler_name = ?', $name)->get();
		if (!count($query))
		{
			return false;
		}
		else
		{
			$this->update(array('scheduler_frequency' => $frequency, 'scheduler_time' => null), "scheduler_name = '$name'");
			return true;
		}
	}

	public function getFrequency($name)
	{
		$query = $this->select('scheduler_frequency')->where('scheduler_name = ?', $name)->get();
		if (!count($query))
		{
			return false;
		}
		else
		{
			return $query->fetchField('scheduler_frequency');
		}
	}

	public function setLunch($schedulerId)
	{
		$this->update(array('scheduler_lunch' => time()), "scheduler_id = $schedulerId");
	}

	public function getJobs()
	{
		$sql = 'SELECT *
				FROM scheduler
				WHERE scheduler_enable = 1
					AND scheduler_lock = 0 AND

					CASE WHEN
						scheduler_frequency IS NOT NULL THEN scheduler_lunch + scheduler_frequency < UNIX_TIMESTAMP()
					ELSE
						scheduler_time = "' . date('H:i:00') . '"
					END';

		return $this->db->fetchAll($sql);
	}

	public function deleteById($id)
	{
		$this->delete("scheduler_id = $id");
	}

	public function deleteByName($name)
	{
		$this->delete("scheduler_name = '$name'");
	}

	public function setLock($schedulerId)
	{
		$this->update(array('scheduler_lock' => 1), "scheduler_id = $schedulerId");
	}

	public function setUnlock($schedulerId)
	{
		$this->update(array('scheduler_lock' => 0), "scheduler_id = $schedulerId");
	}
}
?>