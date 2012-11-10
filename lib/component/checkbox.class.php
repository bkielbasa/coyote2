<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Component_Checkbox extends Component_Abstract
{
	public function displayLayout(&$fieldData)
	{
		$default = new Form_Element_Checkbox('default');
		$default->setLabel('Domyślna wartość');
		$default->setValue(@$fieldData['field_default']);

		return $default;
	}

	public function displayForm(&$data)
	{
		$element = new Form_Element_Checkbox($data['field_name']);
		$element->setValue((int)@$data['field_default']);

		return $element;
	}
}
?>