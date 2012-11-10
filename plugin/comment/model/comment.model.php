<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Comment_Model extends Model
{
	protected $name = 'comment';
	protected $primary = 'comment_id';
	protected $prefix = 'comment_';

	protected $reference = array(

				'user'				=> array(
						
							'table'				=> 'user',
							'col'				=> 'user_id',
							'refCol'			=> 'comment_user'
				)
	);

	public function insert(&$data)
	{
		parent::insert($data);
		return $this->db->nextId();
	}
}
?>