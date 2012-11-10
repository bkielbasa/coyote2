<?php

class Forum_Controller extends Controller
{
	public function install()
	{
		$xml = simplexml_load_file('module/forum/forum.xml');
		$setup = new Setup;

		if (($error = $setup->setupInstaller($xml)) === true)
		{
			$id = $setup->addModule((string) $xml->name, (string) $xml->text, (string) $xml->version);
			$setup->importConfig($id, $xml);

			$component = &$this->load->model('component');
			$query = $component->select()->get();
			$components = array();

			foreach ($query as $row)
			{
				$components[$row['component_name']] = $row['component_id'];
			}
			$field = &$this->load->model('field');
			$moduleId = $this->module->getId('user');

			$user = &$this->getModel('user');

			foreach ($xml->setup->user->field as $object)
			{
				$componentId = $components[(string)$object->attributes()->type];
				if (!$componentId)
				{
					continue;
				}
				$sql['name'] = $sql['value'] = array();

				$name = (string)$object->name;
				$auth = (string)$object->auth;
				$text = (string)$object->text;
				$description = (string)$object->title;
				$default = (string)$object->values->attributes()->default;
				$readonly = (bool) $object->readonly;

				$field->insert(array(
					'field_module'			=> $moduleId,
					'field_component'		=> $componentId,
					'field_name'			=> $name,
					'field_text'			=> $text,
					'field_description'		=> $description,
					'field_auth'			=> $auth,
					'field_default'			=> $default,
					'field_readonly'		=> $readonly
					)
				);
				$fieldId = $this->db->nextId();

				$values = $object->values;
				if (isset($values->value))
				{
					foreach ($values->value as $value)
					{
						$sql['name'][] = (string) $value->attributes()->id;
						$sql['value'][] = (string) $value[0];
					}
				}

				if ($sql['name'])
				{
					$field->item->setFieldItems($fieldId, $sql);
				}

				$options = array();
				foreach ($object->option as $option)
				{
					$options[(string) $option->name] = (string) $option->value;
				}

				if ($options)
				{
					$field->option->setFieldOptions($fieldId, $options);
				}

				if (isset($object->filter))
				{
					$filters = array();

					foreach ($object->filter as $filter)
					{
						$filters[] = (string) $filter;
					}
					$query = $this->db->select('filter_id')->in('filter_name', Text::quote($filters))->get('filter');
					$filters = $query->fetchCol();

					$field->filter->setFieldFilters($fieldId, $filters);
				}
				$user->createField($fieldId);
			}
			@mkdir('store/forum', 0777);

			Box::information('Moduł zainstalowany', "Moduł <i>forum</i> został poprawnie zainstalowany", url('adm/Module'), 'adm/information_box');
		}
		else
		{
			throw new AcpErrorException($error);
		}
	}

	public function uninstall()
	{
		$xml = simplexml_load_file('module/forum/forum.xml');
		$setup = new Setup;

		if (($error = $setup->setupUninstaller($xml,  (bool) $this->post->delete)) == true)
		{
			if ((bool) $this->post->delete)
			{
				$moduleId = $this->module->getId('user');
				$user = &$this->getModel('user');

				foreach ($xml->setup->user->field as $object)
				{
					$this->db->delete('field', 'field_name = "' . (string) $object->name . '" AND field_module = ' . $moduleId);
					$user->deleteField((string) $object->name);
				}
			}

			$setup->deleteModule('forum');

			Box::information('Moduł odinstalowany', "Moduł <i>forum</i> został poprawnie odinstalowany", url('adm/Module'), 'adm/information_box');
		}
		else
		{
			throw new AcpErrorException($error);
		}
	}
}
?>