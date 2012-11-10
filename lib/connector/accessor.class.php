<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Accessor extends Connector_Abstract implements Connector_Interface
{
	public function renderForm()
	{
		$fieldset = &$this->createFieldset('content', 'Zawartość');
		
		$element = $fieldset->createElement('text', 'text_content')
							->setLabel('Adres URL')
							->setDescription('Ścieżka do innego dokumentu lub adres URL')
							->setValue('http://')
							->addValidator(new Validate_String(false, 3))
							->addValidator(new Validate_Url)
							->setOrder(1);
		
		$element = $fieldset->createElement('text', 'page_subject')
							->setLabel('Tytuł dokumentu')
							->addFilter('trim')
							->addFilter('htmlspecialchars')
							->setRequired(true)
							->setDescription('Właściwy tytuł dokumentu (strony)')
							->setOrder(2);
							
		$element = $fieldset->createElement('text', 'page_path')
							->setLabel('Ścieżka do dokumentu')
							->setDescription('Ścieżka, po której będzie identyfikowany dokument.')
							->addFilter(new Filter_Path)
							->setRequired(true)
							->addValidator(new Validate_Path($this->getId()))
							->setOrder(3);
							
		$element = $fieldset->createElement('text', 'page_parent')
							->setLabel('Dokument macierzysty')
							->setDescription('ID dokumentu macierzystego. Kliknij na ikonę, a następnie wybierz dokument macierzysty z drzewa dokumentów')
							->addFilter('int')
							->setOrder(4);
							
		$element = $fieldset->createElement('text', 'log')
							->setAttribute('style', 'width: 370px')
							->setLabel('Opis zmian')
							->setDescription('Jeżeli wprowadzasz zmiany w dokumencie, dobrą praktyką jest opisanie co i dlaczego zmieniłeś')
							->addFilter('htmlspecialchars')
							->setOrder(5);
							
		$fieldset = $this->createFieldset('setting', 'Ustawienia strony');
		
		$element = $fieldset->createElement('checkbox', 'page_publish')
							->setLabel('Opublikowany')
							->setDescription('Zaznacz jeżeli dokument ma być opublikowany (dostępny dla użytkownika)')
							->addFilter('int')
							->setValue((int) Config::getItem('page.publish') == 'true');

		$element = $fieldset->createElement('text', 'page_published')
							->setAttribute('class', 'date-pick')
							->setLabel('Data publikacji')
							->addConfig('hint', 'DD/MM/YYYY HH:mm');
							
		$element = $fieldset->createElement('text', 'page_unpublished')
							->setAttribute('class', 'date-pick')
							->setLabel('Data zakończenia publikacji')
							->addConfig('hint', 'DD/MM/YYYY HH:mm');
							
		$fieldset->createElement('hr', 'linekreak1');
		
		$moduleId = $this->getModuleId() ? $this->getModuleId() : $this->input->get->moduleId;
		$connectorId = $this->getConnectorId() ? $this->getConnectorId() : (int) $this->input->post->page_connector($this->input->get['connectorId']);

		$connector = &$this->getModel('connector');
		$connectorList = $connector->getConnectorList($moduleId);
		
		$element = $fieldset->createElement('select', 'page_connector')
							->setLabel('Łącznik')
							->addFilter('int')
							->setMultiOptions($connectorList)
							->setValue($connectorId);

		$this->setDefaults();
	}
	
	public function onBeforeSave()
	{
		parent::onBeforeSave();
		
		$this->setContentType(0); // content-type nie jest wazny w przypadku tego lacznika
	}

	public function getContent($parseContent = false)
	{
		return $this->content;
	}
}
?>