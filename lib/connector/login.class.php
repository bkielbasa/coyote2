<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Login extends Connector_Document implements Connector_Interface
{
	public function renderForm()
	{
		parent::renderForm();
		
		$this->getFieldset('setting')->getElement('page_template')->setValue('login.php');
	}
}
?>