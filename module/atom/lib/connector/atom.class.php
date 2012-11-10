<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Atom extends Connector_Abstract implements Connector_Interface
{
	public function renderForm()
	{
		parent::renderForm();
		
		$fieldset = $this->createFieldset('content', 'Zawartość');

		$fieldset->createElement('text', 'page_subject')
				 ->setLabel('Nazwa kanału')
				 ->addFilter('trim')
				 ->addFilter('htmlspecialchars')
				 ->setDescription('Tytuł/nazwa kanału. Np. \'Moja strona\'')
				 ->setRequired(true)
				 ->setValue(Config::getItem('site.title'));
		
		$fieldset->createElement('text', 'page_title')
				 ->setLabel('Rozszerzona nazwa kanaełu')
				 ->addFilter('trim')
				 ->addFilter('htmlspecialchars')
				 ->setDescription('Rozszerzona nazwa kanału, która będzie widniała w czytanikach użytkowników');
				 
		$fieldset->createElement('text', 'page_path')
				 ->setLabel('Ścieżka dokumentu')
				 ->setDescription('Ścieżka, po której będzie identyfikowany kanał. Jeżeli jej nie podasz system wygeneruje właściwą na podstawie tytułu tekstu')
				 ->addFilter(new Filter_Path)
				 ->addValidator(new Validate_Path($this->getId()))
				 ->setRequired(true)
				 ->setValue('atom.xml');

		$fieldset->createElement('text', 'page_parent')
				 ->setLabel('Dokument macierzysty')
				 ->setDescription('ID dokumentu macierzystego. Kliknij na ikonę, a następnie wybierz dokument macierzysty z drzewa dokumentów')
				 ->addFilter('int');
				 
		$this->setDefaults();
	}

	public function onBeforeSave()
	{
		if ($this->isRenderMode())
		{
			$values = $this->getValues();

			// dokument macierzysty (ID)
			$this->setParentId((int) $values['page_parent']);
			$this->setModuleId($this->module->getId('atom'));
			$this->setConnectorId($this->getConnectorId());
			$this->setContentType(Page::XML);
	
			$this->setSubject(@$values['page_subject']);
			$this->setTitle(@$values['page_title']);
			$this->setPath(@$values['page_path']);
	
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
	}
}
?>