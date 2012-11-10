<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Parser_Model extends Model
{
	protected $name = 'parser';
	protected $primary = 'parser_id';
	protected $prefix = 'parser_';

	public function delete($ids)
	{
		if (!is_array($ids))
		{
			$ids = array($ids);
		}
		
		foreach ($ids as $id)
		{
			$sql = "UPDATE parser t1
					LEFT JOIN parser t2 ON t2.parser_id = $id
					SET t1.parser_order = t1.parser_order -1
					WHERE t1.parser_order > t2.parser_order";
			$this->db->query($sql);

			parent::delete("parser_id = $id");
		}
	}

	public function down($parser_id)
	{
		$this->db->lock('parser AS t1 WRITE', 'parser AS t2 WRITE', 'parser AS t3 WRITE');

		$sql = "UPDATE parser AS t1, parser AS t3
				JOIN parser AS t2 ON t2.parser_id = $parser_id
					SET t1.parser_order = t1.parser_order + 1, t3.parser_order = t3.parser_order -1
				WHERE t1.parser_id = $parser_id AND t3.parser_order = (t2.parser_order + 1)";
		$this->db->query($sql);

		$this->db->unlock();
	}

	public function up($parser_id)
	{
		$this->db->lock('parser AS t1 WRITE', 'parser AS t2 WRITE', 'parser AS t3 WRITE');

		$sql = "UPDATE parser AS t1, parser AS t3
				JOIN parser AS t2 ON t2.parser_id = $parser_id
					SET t1.parser_order = t1.parser_order - 1, t3.parser_order = t3.parser_order +1
				WHERE t1.parser_id = $parser_id AND t3.parser_order = (t2.parser_order - 1)";
		$this->db->query($sql);

		$this->db->unlock();
	}
}
?>