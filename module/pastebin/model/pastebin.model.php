<?php
/**
 * @package 4programmers.net
 * @version $Id: pastebin.model.php 3195 2010-06-23 07:44:35Z adam $
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class Pastebin_Model extends Model
{
	protected $name = 'pastebin';
	protected $primary = 'pastebin_id';
	protected $prefix = 'pastebin_';

	protected $reference = array(

			'user'					=> array(

							'table'				=> 'user',
							'col'				=> 'user_id',
							'refCol'			=> 'pastebin_user'
			)
	);
}
?>