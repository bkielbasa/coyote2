<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Solr extends Connector_Document implements Connector_Interface
{
	public function renderForm()
	{
		parent::renderForm();

		$this->getFieldset('setting')->getElement('page_template')->setValue('solr.php');
	}

	public function onBeforeSave()
	{
		if ($this->getId())
		{
			$route = &$this->load->model('route');
			$route->delete(strtolower($this->getLocation()));
		}
		parent::onBeforeSave();

		$this->setContentType(Page::HTML);
	}

	public function onAfterSave()
	{
		$routeName = strtolower($this->getLocation());
		$connector = &$this->load->model('connector');

		$result = $connector->getByName('solr')->fetchAssoc();
		$route = &$this->load->model('route');

		$data = array(
			'url'			=> $this->getLocation() . '/:action',
			'name'			=> $routeName,
			'page'			=> $this->getId(),
			'connector'		=> $result['connector_id'],
			'controller'	=> $result['connector_controller'],
			'folder'		=> $result['connector_folder'],
			'default'		=> array(

					'action'			=> $result['connector_action']
			)
		);

		$route->insert($data);		
	}
}
?>