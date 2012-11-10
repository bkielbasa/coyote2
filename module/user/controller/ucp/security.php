<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Security extends Controller
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
		Breadcrumb::add(url('@user?controller=Security'), 'Bezpieczeństwo');

		if ($this->input->isPost())
		{
			$ip = array();

			foreach ($this->post['ip'] as $element)
			{
				if (preg_match('#[0-9\*]{1,3}#', $element))
				{
					$ip[] = $element;
				}
			}
			if (!in_array(count($ip), array(0, 4, 8, 12)))
			{
				$count = count($ip);

				while (--$count % 4 == 0)
				{
					if ($count % 4 == 0)
					{
						$ip = array_slice($ip, 0, $count);
					}
				}
			}

			$user = &$this->getModel('user');
			$user->update(array('user_alert_login' => (bool) $this->post['alert']['login'], 'user_alert_access' => (bool) $this->post['alert']['access'], 'user_ip_access' => implode('.', $ip)), 'user_id = ' . User::$id);

			$this->session->message = 'Informacje zostały zapisane';
			$this->redirect('@user?controller=Security');
		}
		else
		{
			$this->ip = explode('.', User::data('ip_access'));
		}

		return true;
	}
}
?>