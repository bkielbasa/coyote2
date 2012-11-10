<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Base_Controller extends Install_Controller
{
	function main()
	{
		if ($this->session->install < 2)
		{
			$this->redirect('Requirement');
		}
		$this->filter = new Filter_Input;

		if ($this->input->isPost())
		{
			$data['validator'] = array(
			
					'site_title'			=> array(
														array('string', false)
											),
					'core_frontController'	=> array(
														array('string', true)
											),
					'site_host'				=> array(
														array('string', true)
											),
					'cookie_host'			=> array(
														array('string', true)
											),
					'cookie_prefix'			=> array(
														array('string', true)
											)
			);
			$data['filter'] = array(
			
					'site_title'			=> array('htmlspecialchars')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				$this->session->set($this->filter->getValues());
				
				$this->session->install = 3;
				$this->redirect('Db');				
			}
		}

		return  true;
	}
}
?>