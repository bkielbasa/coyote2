<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Element_Span extends Form_Element_Abstract implements IElement
{
	public function setUserValue($value)
	{
		return $value;
	}

	public function getXhtml()
	{
		return Html::tag('span', true, $this->attributes, (string)$this->value);
	}
}
?>