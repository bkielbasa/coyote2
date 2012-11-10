<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Visit extends Controller
{
	function __start()
	{
		if (User::$id == User::ANONYMOUS)
		{
			$this->redirect(Path::connector('login') . '?redirect=' . url('@user'));
		}
	}

	function main()
	{
		Breadcrumb::add(url('@user'), 'Panel użytkownika');
		Breadcrumb::add(url('@user?controller=Visit'), 'Ostatnie wizyty');

		$this->getModel('session');

		$session = new Session_Log_Model;
		$this->visit = array();

		$query = $session->select('log_start, log_stop, log_ip, log_page')->where('log_user = ' . User::$id)->order('log_stop DESC')->limit(100)->get();

		foreach ($query as $row)
		{
			$row['log_url'] = url(trim($row['log_page'], '/'));
			$url = parse_url($row['log_url']);

			$row['log_page'] = $url['path'] . (!empty($url['query']) ? ('?' . $url['query']) : '');

			$this->visit[] = $row;
		}

		return true;
	}
}
?>