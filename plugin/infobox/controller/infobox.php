<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Infobox_Controller extends Controller
{
	public function close()
	{
        $this->output->setHttpHeader('Access-Control-Allow-Origin', '*');

		if (!$this->input->isAjax())
		{
//			exit;
		}
		if (User::$id == User::ANONYMOUS)
		{
			exit('Anonim');
		}

		$id = (int) $this->get->id;
		if (!$id)
		{
			exit('Brak ID');
		}

		$infobox = &$this->getModel('infobox');
		$infobox->marking->insert(array('infobox_id' => $id, 'user_id' => User::$id));

		exit('OK');
	}
}
?>
