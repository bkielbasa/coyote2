<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Decorator_View extends Form_Decorator_Abstract
{
	protected $view;

	public function setView($view)
	{
		$this->view = $view;
		return $this;
	}

	public function getView()
	{
		return $this->view;
	}

	public function display($content)
	{
		$view = new View($this->getView());
		$view->assign($this->getAttributes());
		
		return $view->display(false);
	}
}
?>