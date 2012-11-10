<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Component extends Context
{
	protected $components = array();
	private $enableDisplayDescription = false;

	public function setEnableDisplayDescription($flag)
	{
		$this->enableDisplayDescription = (bool) $flag;
	}

	public function isDisplayDescriptionEnabled()
	{
		return $this->enableDisplayDescription;
	}

	public function &displayForm($moduleId)
	{
		Load::loadFile('lib/validate.class.php');

		$field = &$this->load->model('field');
		$this->form = new Forms('', Forms::POST);
		/**
		 * Domyslne dekoratory dla elementu <form> nie beda uzyte.
		 * Jednakze kontroler korzystajacy z tej klasy moze je dodac 
		 */
		$this->form->removeDecorators();

		foreach ($field->getFields($moduleId) as $row)
		{
			if (!$row['field_display'])
			{
				continue;
			}

			if ($row['field_auth'])
			{
				if (!Auth::get($row['field_auth']))
				{
					continue;
				}
			}			

			$className = 'Component_' . $row['component_name'];
			$object = new $className;
			if (isset($row['items']))
			{
				$object->setItems($row['items']);
			}
			$object->setDisplay($row['field_display']);
			$object->setReadonly($row['field_readonly']);

			$this->components[$row['field_name']] = $object;

			if (!$object->isReadOnly())
			{
				$element = $object->displayForm($row);

				if ($row['field_required'])
				{
					if ($row['component_name'] !== 'checkbox' && $row['component_name'] !== 'multicheckbox')
					{
						$element->setRequired(true);
					}
				}
				if ($row['field_validator'])
				{
					$matchValidator = new Validate_Match($row['validator_regexp']);
					$matchValidator->setTemplate(Validate_Match::NOT_MATCH, $row['validator_message']);

					$element->addValidator($matchValidator);
				}
				if (isset($row['filters']))
				{
					foreach ($row['filters'] as $filter)
					{
						$element->addFilter($filter);
					}
				}
			}
			else
			{
				$element = $this->getReadOnlyElement($row['field_name']);
			}

			$element->setLabel($row['field_text']);

			if ($this->isDisplayDescriptionEnabled())
			{
				$element->setDescription($row['field_description']);
			}

			if (!$element->getDecorators())
			{
				$element->setEnableDefaultDecorators(false);
				$element->setDecorators(array(

						'label'			=> array(

								'attributes'		=> array(

										'title'				=> $row['field_description']
								)
						),
						'errors'		=> array('tag' => 'ul'),
						'description'   => array('tag' => 'small'),
						'tag'			=> array('tag' => 'li', 'placement' => 'WRAP'),

					)
				);
			}

			$this->form->addElement($element);
		}

		return $this->form;
	}

	public function onSubmit()
	{
		$data = $this->form->getValues();

		foreach ($data as $fieldName => $value)
		{
			if ($this->components[$fieldName]->isReadonly())
			{
				unset($data[$fieldName]);
			}
			else
			{
				$data[$fieldName] = $this->components[$fieldName]->onSubmit($value);
			}
		}

		return $data;
	}

	protected function getReadOnlyElement($fieldName)
	{
		return new Form_Element_Span($fieldName);
	}
}
?>