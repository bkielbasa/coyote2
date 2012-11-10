<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Validator_Model extends Model
{
	protected $name = 'validator';
	protected $prefix = 'validator_id';

	public function getValidatorList()
	{
		$result = array();

		$query = $this->select()->get();
		foreach ($query as $row)
		{
			$result[$row['validator_id']] = $row['validator_name'];
		}

		return $result;
	}
}
?>