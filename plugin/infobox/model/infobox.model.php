<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Infobox_Marking_Model extends Model
{
	protected $name = 'infobox_marking';
}

class Infobox_Model extends Model
{
	protected $name = 'infobox';
	protected $primary = 'infobox_id';

	public $marking;

	function __construct()
	{
		$this->marking = new Infobox_Marking_Model;
	}

	public function getInfobox($userId = null)
	{
		if (!$userId)
		{
			$userId = User::$id;
		}

		$sql = "SELECT infobox.*
				FROM infobox
				LEFT JOIN infobox_marking ii ON ii.infobox_id = infobox.infobox_id AND ii.user_id = $userId
				WHERE infobox_enable = 1 AND infobox_time + infobox_lifetime > UNIX_TIMESTAMP() AND ii.user_id IS NULL
				ORDER BY infobox_priority DESC
				LIMIT 1";

		return $this->db->query($sql)->fetchAssoc();
	}
}
?>