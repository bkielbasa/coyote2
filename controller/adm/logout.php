<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Logout extends Adm
{
	function main()
	{
		$session = &$this->load->model('adm/adm_session');
		$session->delete('session_id = "' . User::$sid . '"');

		$this->redirect('adm');
	}
}

?>