<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Bookmark extends Connector_Document implements Connector_Interface
{
	public function renderForm()
	{
		parent::renderForm();

		$fieldset = &$this->getFieldset('setting');
		$fieldset->getElement('page_template')->setValue('bookmark.php');

		$this->setDefaults();
	}

	public function onAfterSave()
	{
		parent::onAfterSave();

		$connector = &$this->load->model('connector');

		$result = $connector->getByName('bookmark')->fetchAssoc();
		$route = &$this->load->model('route');
		$name = 'page_' . $this->getId();

		$data = array(
			'url'			=> $this->getLocation() . '/:action/*',
			'name'			=> $name,
			'page'			=> $this->getId(),
			'connector'		=> $result['connector_id'],
			'controller'	=> $result['connector_controller'],
			'folder'		=> $result['connector_folder'],
			'default'		=> array(

					'action'			=> $result['connector_action']
			),
			'requirements'	=> array(

					'action'			=> '([a-z]+)'
			)
		);

		if ($this->router->getRoute($name))
		{
			$route->update($name, $data);
		}
		else
		{
			$route->insert($data);		
		}	
	}

	public function delete()
	{
		$route = &$this->load->model('route');
		$route->delete('page_' . $this->getId());
		
		parent::delete();
	}
}
?>