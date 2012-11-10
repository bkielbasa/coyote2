<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Snippet_Model extends Model
{
	protected $name = 'snippet';
	protected $primary = 'snippet_id';

	protected $reference = array(

		'user'			=> array(

					'table'				=> 'user',
					'col'				=> 'user_id',
					'refCol'			=> 'snippet_user'
		)
	);

}
?>