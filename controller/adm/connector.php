<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector extends Adm
{
	function main()
	{
		$connector = &$this->getModel('connector');

		$this->connector = $connector->select()->leftJoin('module', 'module_id = connector_module')->get()->fetch();
		return true;
	}

	public function submit($id = 0)
	{
		$id = (int)$id;
		$connector = &$this->getModel('connector');

		$result = array();
		if ($id)
		{
			if (!$result = $connector->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Łącznik o danym ID nie istnieje!');
			}
		}

		$this->filter = new Filter_Input;
		$this->folder = array('' => '');

		foreach (scandir('controller/') as $dir)
		{
			if ($dir{0} != '.')
			{
				if (is_dir('controller/' . $dir))
				{
					$this->folder[$dir] = $dir;
				}
			}
		}
		foreach (array_keys($this->module->getModules()) as $module)
		{
			foreach ((array)@scandir("module/$module/controller/") as $dir)
			{
				if ($dir{0} != '.')
				{
					if (is_dir("module/$module/controller/$dir"))
					{
						$this->folder[$dir] = $dir;
					}
				}
			}
		}

		if ($this->input->isMethod(Input::POST))
		{
			$data['validator'] = array(

				'class'						=> array(
														array('string', false, 1, 50)
											),
				'controller'				=> array(
														array('string', false, 1, 50)
											),
				'action'					=> array(
														array('string', false, 1, 50)
											),
				'text'						=> array(
														array('string', false, 1, 50)
											)
			);
			$data['filter'] = array(

				'class'						=> array('trim', 'strip_tags', 'htmlspecialchars'),
				'controller'				=> array('trim', 'strip_tags', 'htmlspecialchars'),
				'action'					=> array('trim', 'strip_tags', 'htmlspecialchars'),
				'text'						=> array('trim', 'htmlspecialchars'),
				'folder'					=> array('trim', 'strip_tags', 'htmlspecialchars'),
				'name'						=> array('trim', 'strip_tags', 'htmlspecialchars')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				$this->load->helper('array');
				$data = array_key_pad($this->filter->getValues(), 'connector_');

				if ($id)
				{
					$connector->update($data, "connector_id = $id");
				}
				else
				{
					$connector->insert($data);
				}

				$this->message = 'Zmiany zostały zapisane';
			}		
		}

		return View::getView('adm/connectorSubmit', $result);
	}
}
?>