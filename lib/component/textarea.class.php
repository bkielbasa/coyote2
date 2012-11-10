<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Component_Textarea extends Component_Abstract
{
	public function displayLayout(&$fieldData)
	{
		$element[0] = new Form_Element_Text('rows');
		$element[0]->setLabel('Ilość wierszy')->setAttribute('size', 5);
		$element[0]->setValue(10);
		$element[0]->setValue(@$fieldData['rows']);
		$element[0]->addFilter('int');

		$element[1] = new Form_Element_Text('cols');
		$element[1]->setLabel('Ilość kolumn')->setAttribute('size', 5)->setValue(50);
		$element[1]->setValue(@$fieldData['cols']);
		$element[1]->addFilter('int');

		$element[2] = new Form_Element_Text('max');
		$element[2]->setLabel('Maksymalna długość tekstu')->setAttribute('size', 5);
		$element[2]->setValue(@$fieldData['max']);
		$element[2]->addFilter('int');

		$element[3] = new Form_Element_Text('min');
		$element[3]->setLabel('Minimalna długość tekstu')->setAttribute('size', 5);
		$element[3]->setValue(@$fieldData['min']);
		$element[3]->addFilter('int');

		$element[4] = new Form_Element_Text('default');
		$element[4]->setLabel('Domyślna wartość');
		$element[4]->setValue(@$fieldData['field_default']);

		return $element;		
	}

	public function displayForm(&$data)
	{
		if (!$data['field_readonly'])
		{
			$element = new Form_Element_Textarea($data['field_name']);
			$element->setLabel($data['field_text']);
			$element->addConfig('description', $data['field_description']);

			if (!empty($data['length']))
			{
				$element->setAttribute('maxlength', $data['length']);
				$element->setAttribute('size', $data['length']);
			}
			if (!empty($data['min']) || !empty($data['max']))
			{
				Load::loadFile('lib/validate.class.php');
				$element->addValidator(new Validate_String(empty($data['min']), (int)@$data['min'], (int)@$data['max']));
			}
			if (!empty($data['rows']))
			{
				$element->setAttribute('rows', $data['rows']);
			}
			if (!empty($data['cols']))
			{
				$element->setAttribute('cols', $data['cols']);
			}

			return $element;
		}
		else
		{
			$element = new Form_Element_Span($data['field_name']);
			$element->setLabel($data['field_text']);
			$element->addConfig('description', $data['field_description']);

			return $element;
		}
	}
}
?>