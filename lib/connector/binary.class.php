<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Binary extends Connector_Abstract implements Connector_Interface
{
	public function renderForm()
	{
		$fieldset = $this->createFieldset('content', 'Zawartość');
		
		$element = $fieldset->createElement('text', 'page_path')
							->setLabel('Ścieżka dokumentu')
							->setDescription('Ścieżka, po której będzie identyfikowany będzie plik')
							->addFilter(new Filter_Path)
							->addValidator(new Validate_Path($this->getId()))
							->setRequired(true)
							->setOrder(1);
							
		$element = $fieldset->createElement('text', 'page_parent')
							->setLabel('Dokument macierzysty')
							->setDescription('ID dokumentu macierzystego. Kliknij na ikonę, a następnie wybierz dokument macierzysty z drzewa dokumentów')
							->addFilter('int')
							->setOrder(2);

		$this->setDefaults();
	}

	public function onBeforeSave()
	{
		$values = $this->getValues();

		$connector = &$this->load->model('connector');
		$connectorId = $connector->getByName('binary')->fetchObject()->connector_id;

		// dokument macierzysty (ID)
		$this->setParentId((int) $values['page_parent']);
		$this->setModuleId($this->module->getId('main'));
		$this->setConnectorId($connectorId);
		$this->setContentType(0);

		$this->setSubject(@$values['page_path']);
		
		$path = new Path;
		$this->setPath($path->encode(@$values['page_path']));

		// brak tresci
		$this->setContent('');

		$this->setLog('');
		$this->setIp($this->input->getIp());

		// opublikowany domyslnie
		$this->setIsPublished(true);
		// brak cachowania
		$this->setIsCached(false);
		// brak szablonu
		$this->setTemplate('');
	}
	
	public function getContent($parseContent = false)
	{
		return false;
	}
}
?>