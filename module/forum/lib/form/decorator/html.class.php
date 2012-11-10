<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Decorator_Html extends Form_Decorator_Abstract
{
	protected $placement = self::APPEND;
	protected $html;
	
	public function setHtml($html)
	{
		$this->html = $html;
		return $this;
	}
	
	public function getHtml()
	{
		return $this->html;
	}
	
	public function display($content)
	{
		switch ($this->getPlacement())
		{
			case self::APPEND:

				$content .= $this->getSeparator() . $this->getHtml();
			break;

			case self::PREPEND:

				$content = $this->getHtml() . $this->getSeparator() . $content;
			break;

			case self::WRAP:

				
			break;
		}

		return $content;
	}
}
?>