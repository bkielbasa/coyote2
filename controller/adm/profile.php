<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Profile extends Adm
{
	function main()
	{
		$field = &$this->getModel('field');
		$component = $this->getModel('component');

		if ($this->input->isMethod(Input::POST))
		{
			if ($delete = $this->post->delete)
			{
				$user = &$this->getModel('user');
				$query = $field->select('field_name')->where('field_id IN(' . implode(',', $delete) . ')')->get();

				try
				{
					foreach ($query as $row)
					{
						$user->deleteField($row['field_name']);
					}
				}
				catch (Exception $e)
				{
					Log::add('Nie można usunąć kolumny: ' . $e->getMessage(), E_ERROR);
				}

				$field->delete($delete);
				$this->message = 'Zaznaczone pola zostały bezpowrotnie usunięte';
			}
		}
		if ($this->get->id)
		{
			$field->{$this->get->mode}($this->get->id);
		}

		$this->component = array();
		foreach ($component->fetch() as $row)
		{
			$this->component[$row['component_id']] = $row['component_text'];
		}
		$this->field = $field->fetch('field_module = ' . $this->module->getId('user'), 'field_order')->fetch();

		return true;
	}

	public function submit($id = 0)
	{
		$id = (int)$id;

		$this->componentId = (int)$this->get->componentId;
		$result = array();

		$field = &$this->getModel('field');
		$this->items = array();

		if ($id)
		{
			if (!$result = $field->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Brak informacji o polu o tym ID!');
			}
			$optionList = array();

			$query = $field->option->fetch('option_field = ' . $id);
			foreach ($query as $row)
			{
				$optionList[$row['option_name']] = $row['option_value'];
			}

			$result = array_merge($result, $optionList);
			unset($optionList);
		}
		$component = &$this->getModel('component');
		$this->component = array();

		foreach ($component->fetch() as $row)
		{
			$this->component[$row['component_id']] = $row['component_text'];
		}

		$this->filter = new Filter_Input;

		$this->componentLayout = '';
		if ($this->componentId)
		{
			$itemList = array();

			$query = $field->item->fetch('item_field = ' . $id);
			foreach ($query as $row)
			{
				$itemList[$row['item_name']] = $row['item_value'];
			}
			Load::loadFile('lib/forms.class.php', false);

			$className = 'Component_' . $component->find($this->componentId)->fetchField('component_name');
			$component = new $className;
			$component->setItems($itemList);
			$elements = &$component->displayLayout($result);

			$this->componentLayout = new Forms;
			$this->componentLayout->setMethod(Forms::POST);
			$this->componentLayout->setEnableDefaultDecorators(false);

			if (!is_array($elements))
			{
				$this->componentLayout->addElement($elements);
			}
			else
			{
				foreach ($elements as $element)
				{
					$this->componentLayout->addElement($element);
				}
			}
		}


		if ($this->input->isMethod(Input::POST))
		{
			$data['validator'] = array(

				'name'			=> array(
											array('string', false, 1)
								),
				'text'			=> array(
											array('string', false, 1)
								)
			);
			$data['filter'] = array(

				'name'			=> array('strip_tags', 'htmlspecialchars'),
				'text'			=> array('htmlspecialchars'),
				'description'	=> array('htmlspecialchars'),
				'validator'		=> array('int'),
				'display'		=> array('int'),
				'readonly'		=> array('int'),
				'required'		=> array('int'),
				'profile'		=> array('int')
			);
			$this->filter->setRules($data);
			$formValidation = $this->componentLayout->isValid();

			if ($this->filter->isValid($_POST) && $formValidation)
			{
				if (!isset($this->post->default))
				{
					$default = '';
				}
				else
				{
					$default = $this->post['default'];

					if (is_array($default))
					{
						$default = implode(',', $default);
					}
				}

				$data = $this->filter->getValues();
				$data += array(
					'default'			=> $default,
					'auth'				=> (string)$this->post->auth,
					'display'			=> (int)$this->post->display,
					'readonly'			=> (int)$this->post->readonly,
					'required'			=> (int)$this->post->required,
					'profile'			=> (int)$this->post->profile
				);

				$this->load->helper('array');
				$data = array_key_pad($data, 'field_');
				$user = &$this->getModel('user');

				if (!$id)
				{
					$data += array(
						'field_module'			=> $this->module->getId('user'),
						'field_component'		=> $this->componentId,

					);
					$field->insert($data);
					$fieldId = $this->db->nextId();
				}
				else
				{
					$field->update($data, "field_id = $id");
					$fieldId = $id;
				}
				$field->filter->setFieldFilters($fieldId, $this->post->filter);

				if ($this->post->item)
				{
					$field->item->setFieldItems($fieldId, $this->post->item);
				}

				$data = $this->componentLayout->getValues();
				unset($data['default']);

				if ($data)
				{
					$field->option->setFieldOptions($fieldId, $data);
				}

				try
				{
					if (!$id)
					{
						$user->createField($fieldId);
					}
					else
					{
						$user->changeField($fieldId, $result['field_name']);
					}
				}
				catch (Exception $e)
				{
					Log::add($e->getMessage(), E_ERROR);
				}

				$this->redirect('adm/Profile');
			}
		}
		$this->fieldFilters = array();

		if ($id)
		{
			$this->fieldFilters = $field->filter->getFieldFilters($id);
		}

		$validator = &$this->getModel('validator');
		$this->validator = $validator->getValidatorList();
		array_unshift($this->validator, '--');

		$filter = &$this->getModel('filter');
		$this->filters = $filter->getFilterList();

		$auth = &$this->getModel('auth');
		$this->auth = array('' => '');

		foreach ($auth->getOptions() as $row)
		{
			$this->auth[$row['option_text']] = sprintf('[%s] %s', $row['option_text'], $row['option_label']);
		}

		return View::getView('adm/profileSubmit', $result);
	}
}
?>