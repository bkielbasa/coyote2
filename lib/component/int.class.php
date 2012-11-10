<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Component_Int extends Component_Abstract
{
	private $dataType = array(

		'tinyint'		=> 'TINYINT',
		'smallint'		=> 'SMALLINT',
		'mediumint'		=> 'MEDIUMINT',
		'int'			=> 'INT'
	);

	public function displayLayout(&$fieldData)
	{
		$element[0] = new Form_Element_Text('length');
		$element[0]->setLabel('Długość kontrolki')->setAttribute('size', 5);
		$element[0]->setValue(@$fieldData['length']);
		$element[0]->addFilter('int');

		$element[1] = new Form_Element_Text('min');
		$element[1]->setLabel('Minimalna wartość')->setAttribute('size', 5);
		$element[1]->setValue((int) @$fieldData['min']);
		$element[1]->addFilter('int');

		$element[2] = new Form_Element_Text('max');
		$element[2]->setLabel('Maksymalna wartość')->setAttribute('size', 5);
		$element[2]->setValue((int) @$fieldData['max']);
		$element[2]->addFilter('int');

		$element[3] = new Form_Element_Select('dataType');
		$element[3]->setLabel('Pojemność danych');
		$element[3]->addMultiOptions($this->dataType);
		$element[3]->setValue(@$fieldData['dataType']);

		$element[4] = new Form_Element_Text('default');
		$element[4]->setLabel('Domyślna wartość');
		$element[4]->setValue((int) @$fieldData['field_default']);
		$element[4]->addFilter('int');

		return $element;
	}

	public function displayForm(&$data)
	{
		$element = new Form_Element_Text($data['field_name']);
		$element->addFilter('int');

		if (!empty($data['length']))
		{
			$element->setAttribute('size', $data['length']);
		}
		if (!empty($data['min']) || !empty($data['max']))
		{
			$element->addValidator(new Validate_Int(false, (int)@$data['min'], (int)@$data['max']));
		}

		return $element;		
	}
}
?>