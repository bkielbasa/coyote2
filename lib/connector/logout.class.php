<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Logout extends Connector_Abstract implements Connector_Interface
{
	public function renderForm()
	{
		parent::renderForm();
		$fieldset = &$this->createFieldset('content', 'Zawartość');
		
		$element = $fieldset->createElement('text', 'page_path')
							->setLabel('Ścieżka do dokumentu')
							->setDescription('Ścieżka, po której będzie identyfikowany dokument.')
							->addFilter(new Filter_Path)
							->setRequired(true)
							->addValidator(new Validate_Path($this->getId()))
							->setOrder(1);
							
		$element = $fieldset->createElement('text', 'page_parent')
							->setLabel('Dokument macierzysty')
							->setDescription('ID dokumentu macierzystego. Kliknij na ikonę, a następnie wybierz dokument macierzysty z drzewa dokumentów')
							->addFilter('int')
							->setOrder(2);
							
		$input = &Load::loadClass('input');

		$connectorId = $this->getConnectorId() ? $this->getConnectorId() : (int)$input->post->page_connector($input->get['connectorId']);
		$fieldset->createElement('hidden', 'page_connector')->setValue($connectorId);

		$this->setDefaults();
	}

	public function onBeforeSave()
	{
		$values = $this->getValues();

		// dokument macierzysty (ID)
		$this->setParentId((int)$values['page_parent']);

		// tytul strony
		$this->setSubject(@$values['page_path']);
		$this->setPath(@$values['page_path']);
		$this->setModuleId($this->module->getId('user'));
		$this->setConnectorId(@$values['page_connector']);
		$this->setIp($this->input->getIp());
		$this->setIsPublished(true);
		$this->setIsCached(false);
	}
}
?>