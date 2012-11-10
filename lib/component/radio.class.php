<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Component_Radio extends Component_Abstract
{
	public function displayLayout(&$fieldData)
	{
		$element = new Form_Element_Text('default');
		$element->removeDecorators();

		$element->addDecorator('view', array(

			'view'					=> 'form/componentRadio',
			'attributes'			=> array(

						'items'					=> $this->getItems(),
						'default'				=> @$fieldData['field_default']
						)
			)
		);

		return $element;
	}

	public function displayForm(&$data)
	{
		$element = new Form_Element_Radio($data['field_name']);
		$element->setMultiOptions($this->getItems());
		$element->setValue(@$data['field_default']);

		return $element;
	}
}
?>