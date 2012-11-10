<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Homepage extends Connector_Document  implements Connector_Interface
{
	private function getPagesCount()
	{
		$connector = &$this->getModel('connector');
		$result = $connector->getByName('homepage')->fetchAssoc();

		$page = &$this->getModel('page');
		$query = $page->select('page_id')->where('page_connector = ' . $result['connector_id'])->get();

		return (bool) count($query);		
	}

	public function renderForm()
	{
		if (!$this->getId())
		{
			if ($this->getPagesCount())
			{
				throw new AcpErrorException('W systemie może istnieć tylko jedna kopia strony głównej!');
			}			
		}
		parent::renderForm();

		$fieldset = &$this->getFieldset('content');
		$fieldset->removeElement('page_path');
		$fieldset->removeElement('page_parent');
		$fieldset->removeElement('page_title');

		$fieldset = &$this->getFieldset('setting');
		$fieldset->removeElement('page_publish');
		$fieldset->removeElement('page_published');
		$fieldset->removeElement('page_unpublished');
		$fieldset->removeElement('linekreak1');

		$fieldset->getElement('page_template')->setValue('homepage.php');
		$this->setDefaults();
	}

	public function onBeforeSave()
	{
		if (!$this->getId())
		{
			if ($this->getPagesCount())
			{
				throw new AcpErrorException('W systemie może istnieć tylko jedna kopia strony głównej!');
			}			
		}

		$moduleId = $this->module->getId('main');
		parent::onBeforeSave();

		// dokument macierzysty (ID)
		$this->setParentId(0);
		$this->setModuleId($moduleId);

		// drugi tytul
		$this->setTitle('');

		$this->setPath(null);
		$this->setIsPublished(true);
	}

	public function onAfterSave()
	{
		parent::onAfterSave();

		$connector = &$this->getModel('connector');

		$result = $connector->getByName('homepage')->fetchAssoc();
		$route = &$this->getModel('route');
		$route->delete('homepage');

		$data = array(
			'url'			=> '/',
			'name'			=> 'homepage',
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

	public function delete()
	{
		$route = &$this->getModel('route');
		$route->delete('homepage');
		
		parent::delete();
	}

}
?>