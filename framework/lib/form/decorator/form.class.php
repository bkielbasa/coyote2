<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Dekorator tworzacy znacznik <form>
 */
class Form_Decorator_Form extends Form_Decorator_Abstract
{
	protected $placement = 'WRAP';

	public function display($content)
	{
		$this->addAttribute('action', $this->getElement()->getAction());
		$attributes = array_merge($this->getAttributes(), $this->getElement()->getAttributes());

		$content = Html::tag('form', true, $attributes, $content);
		return $content;
	}
}
?>