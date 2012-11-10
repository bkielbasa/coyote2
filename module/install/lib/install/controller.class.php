<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Install_Controller extends Controller
{
	function __start()
	{
		header("cache-Control: no-cache, must-revalidate"); 
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");	

		if (!ini_get('short_open_tag'))
		{
			echo 'Dyrektywa shor_open_tag ma wartoœæ OFF. Do prawid³owego dzia³ania systemu, nale¿y w³¹czyæ tê opcjê!';
			exit;
		}
		$this->load->library('session');
		$this->load->helper('url');

		$this->session->start();
	}
}
?>