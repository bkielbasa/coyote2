<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Logout extends Controller
{
	function main()
	{
		$referer = $this->input->getReferer();

		/*
		 * Poprawka dla Adsense :( Poniewaz Google nie oferuje kodu reklam dla HTTPS, w razie gdy link do przekierowania
		 * wskazuje na HTTPS - zamieniamy na HTTP
		 */
		if (parse_url($referer, PHP_URL_SCHEME) == 'https')
		{
			$referer = preg_replace('#^https#i', 'http', $referer);
		}

		if (isset($this->get->sid) && $this->get->sid == User::$sid)
		{
			UserErrorException::__(Trigger::call('application.onUserLogout', User::$id));

			$user = &$this->load->model('user');
			$user->logout(User::$id);

			UserErrorException::__(Trigger::call('application.onUserLogoutComplete', User::$id));
		}

		$this->redirect($referer);
		exit;
	}
}

?>