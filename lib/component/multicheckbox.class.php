<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Component_Multicheckbox extends Component_Abstract
{
	private $element;

	public function displayLayout(&$fieldData)
	{
		$element = new Form_Element_Multicheckbox('default');
		$element->setDecorators(array(

			'view'			=> array(
				
					'view'			=> 'form/componentMulticheckbox',
					'attributes'	=> array(

						'items'		=> $this->getItems(),
						'default'	=> @explode(',', @$fieldData['field_default'])
					)
				)
			)
		);

		return $element;		
	}

	public function displayForm(&$data)
	{
		$this->element = new Form_Element_Multicheckbox($data['field_name']);
		$this->element->setMultiOptions($this->getItems());
		$this->element->setValue(@$data['field_default']);

		return $this->element;
	}

	public function onSubmit($value)
	{
		$elementName = $this->element->getName();
		if (!isset($this->input->post->$elementName))
		{
			$value = '';
		}

		if (is_array($value))
		{
			$value = implode(',', $value);
		}

		return $value;
	}
}
?>