<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Component_Text extends Component_Abstract
{
	public function displayLayout(&$fieldData)
	{
		$element[0] = new Form_Element_Text('length');
		$element[0]->setLabel('Długość kontrolki')->setAttribute('size', 5);
		$element[0]->setValue(@$fieldData['length']);
		$element[0]->addFilter('int');

		$element[1] = new Form_Element_Text('max');
		$element[1]->setLabel('Maksymalna długość tekstu')->setAttribute('size', 5);
		$element[1]->setValue(@$fieldData['max']);
		$element[1]->addFilter('int');

		$element[2] = new Form_Element_Text('min');
		$element[2]->setLabel('Minimalna długość tekstu')->setAttribute('size', 5);
		$element[2]->setValue(@$fieldData['min']);
		$element[2]->addFilter('int');

		$element[3] = new Form_Element_Text('default');
		$element[3]->setLabel('Domyślna wartość');
		$element[3]->setValue(@$fieldData['field_default']);

		return $element;	
	}

	public function displayForm(&$data)
	{
		$element = new Form_Element_Text($data['field_name']);

		if (!empty($data['length']))
		{
			$element->maxlength = $data['length'];
			$element->size = $data['length'];
		}
		if (!empty($data['min']) || !empty($data['max']))
		{
			$element->addValidator(new Validate_String(false, (int)@$data['min'], (int)@$data['max']));
		}

		return $element;		
	}
}
?>