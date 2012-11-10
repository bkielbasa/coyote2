<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Index extends Adm
{
	function main()
	{
		$session = &$this->getModel('adm/adm_session');
		$this->sessions = $session->fetch()->fetchAll();
		$this->data = $session->get();

		$log = &$this->getModel('log');
		$this->logTypes = $log->getLogTypes();

		$this->log = $log->filter(null, array(E_ACP_LOGIN, E_ACP_LOGIN_FAILED), null, null, null, null, 'log_id DESC', (int)$this->get['start'], 25);
		$this->pagination = new Pagination('', $log->getFoundRows(), 25, (int)$this->get['start']);

		load_helper('array');
		return true;
	}
}
?>