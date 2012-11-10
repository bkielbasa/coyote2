<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Component_Select extends Component_Abstract
{
	public function displayLayout(&$fieldData)
	{
		$element[0] = new Form_Element_Select('default');
		$element[0]->setLabel('Domyślna pozycja')->setAttribute('id', 'default');
		$element[0]->setMultiOptions((array) $this->getItems());
		$element[0]->setValue(@$fieldData['field_default']);

		$element[1] = new Form_Element_Text('Foobar');
		$element[1]->removeDecorators();
		$element[1]->addDecorator('view', array(
				
			'view'				=> 'form/componentSelect', 
			'attributes'		=> array(
				
						'items'				=>		$this->getItems()
						)
			)
		);

		return $element;		
	}

	public function displayForm(&$data)
	{
		$element = new Form_Element_Select($data['field_name']);
		$element->setMultiOptions($this->getItems());
		$element->setValue(@$data['field_default']);

		return $element;
	}
}
?>