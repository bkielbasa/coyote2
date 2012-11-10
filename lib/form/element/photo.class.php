<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Element_Photo extends Form_Element_File implements IElement
{
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	public function getXhtml()
	{ 
		$xhtml = '';
		
		if ($this->getValue())
		{
			$xhtml .= Form::hidden($this->getName() . '_data', $this->getValue());

			$xhtml .= Html::img(Url::__('store/_a/' . $this->getValue()), array('width' => $this->getConfig('thumbnailWidth'))) . '</li>';
			$xhtml .= '<li><label>&nbsp;</label>' . Form::checkbox($this->getName() . '_delete', 1, false) . ' Usuń zdjęcie</li><li><label>&nbsp;</label>';
		}
		
		$xhtml .= Form::file($this->getName(), '', $this->attributes);
		return $xhtml;
	}
}
?>