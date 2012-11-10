<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Document extends Connector_Abstract implements Connector_Interface
{
	function __construct($data = array())
	{
		parent::__construct($data);
	}

	public function renderForm()
	{
		parent::renderForm();

		$fieldset = &$this->createFieldset('content', 'Zawartość');

		$element = $fieldset->createElement('text', 'page_subject')
							->setLabel('Tytuł dokumentu')
							->addFilter('trim')
							->addFilter('htmlspecialchars')
							->setRequired(true)
							->setDescription('Właściwy tytuł dokumentu (strony)')
							->setOrder(1);

		$element = $fieldset->createElement('text', 'page_title')
							->setLabel('Rozszerzony tytuł dokumentu')
							->addFilter('htmlspecialchars')
							->setDescription('Możesz podać wydłużony tytuł dokumentu (może wpłynąć na pozycjonowanie dokumentu)')
							->setOrder(2);

		$element = $fieldset->createElement('text', 'page_path')
							->setLabel('Ścieżka do dokumentu')
							->setDescription('Ścieżka, po której będzie identyfikowany dokument.')
							->addFilter(new Filter_Path)
							->setRequired(true)
							->addValidator(new Validate_Path($this->getId()))
							->setOrder(3);

		$element = $fieldset->createElement('text', 'tags')
							->setLabel('Tagi')
							->setDescription('Słowa kluczowe opisujące tę stronę')
							->addFilter(new Filter_Tag)
							->setOrder(4)
							->setValue($this->getId() && $this->getTags() ? implode(', ', $this->getTags()) : '');

		$element = $fieldset->createElement('text', 'page_parent')
							->setLabel('Dokument macierzysty')
							->setDescription('ID dokumentu macierzystego. Kliknij na ikonę, a następnie wybierz dokument macierzysty z drzewa dokumentów')
							->addFilter('int')
							->setOrder(5);

		$core = &Core::getInstance();

		$richtext = &$core->load->model('richtext');
		$query = $richtext->select('richtext_id, richtext_name')->get();
		$richtextList = array(0 => '(brak)');

		$richtextList += $query->fetchPairs();

		$element = $fieldset->createElement('select', 'page_richtext')
							->setLabel('RTF')
							->setMultiOptions($richtextList)
							->setValue((int) Config::getItem('page.richtext'))
							->addFilter('int')
							->setDescription('Wybierz edytor, który ma być używany do edycji tego tekstu')
							->setOrder(6)
							->setAttribute('id', 'rtf')
							->setMultiOptions($richtextList);

		$element = $fieldset->createElement('textarea', 'text_content')
							->setAttribute('id', 'text_content')
							->setAttribute('style', 'width: 96%')
							->setAttribute('cols', 150)
							->setAttribute('rows', 25)
							->setAttribute('class', 'editor')
							->setOrder(7);

		$element = $fieldset->createElement('text', 'log')
							->setAttribute('style', 'width: 370px')
							->setLabel('Opis zmian')
							->setDescription('Jeżeli wprowadzasz zmiany w dokumencie, dobrą praktyką jest opisanie co i dlaczego zmieniłeś')
							->addFilter('htmlspecialchars')
							->setOrder(8);

		$element = $fieldset->createElement('textarea', 'style')
							->setAttribute('style', 'width: 98%; margin-top: 20px; height: 200px')
							->setLabel('Style CSS')
							->setDescription('Możesz dołączyć dodatkowe style CSS, które będą dołączane do tej strony')
							->setOrder(9);

		if ($this->getId())
		{
			if (file_exists('store/css/page-' . $this->getId() . '.css'))
			{
				$stylesheet = file_get_contents('store/css/page-' . $this->getId() . '.css');
				$element->setValue($stylesheet);
			}
		}

		$fieldset = $this->createFieldset('setting', 'Ustawienia strony');

		$element = $fieldset->createElement('checkbox', 'page_publish')
							->setLabel('Opublikowany')
							->setDescription('Zaznacz jeżeli dokument ma być opublikowany (dostępny dla użytkownika)')
							->addFilter('int')
							->setValue((int) Config::getItem('page.publish') == 'true');

		$element = $fieldset->createElement('text', 'page_published')
							->setAttribute('class', 'date-pick')
							->setLabel('Data publikacji')
							->addConfig('hint', 'YYYY-MM-DD HH:MM');

		$element = $fieldset->createElement('text', 'page_unpublished')
							->setAttribute('class', 'date-pick')
							->setLabel('Data zakończenia publikacji')
							->addConfig('hint', 'YYYY-MM-DD HH:MM');

		$fieldset->createElement('hr', 'linekreak1');

		$moduleId = $this->getModuleId() ? $this->getModuleId() : $core->input->get->moduleId;
		$connectorId = $this->getConnectorId() ? $this->getConnectorId() : (int) $core->input->post->page_connector($core->input->get['connectorId']);

		$connector = &$core->load->model('connector');
		$connectorList = $connector->getConnectorList($moduleId);

		$element = $fieldset->createElement('select', 'page_connector')
							->setLabel('Łącznik')
							->addFilter('int')
							->setMultiOptions($connectorList)
							->setValue($connectorId);

		$content = &$core->load->model('content');
		$contentList = $content->getContentList();

		$element = $fieldset->createElement('select', 'page_content')
							->setLabel('Typ zawartości')
							->setDescription('Typ MIME dokumentu. System będzie wysyłał ten typ MIME w nagłówku podczas generowania strony')
							->addFilter('int')
							->setMultiOptions($contentList);

		$templateList = $this->getTemplateList();
		$templateAttributes = array();

		foreach ($templateList as $tplPath => $tplName)
		{
			if (strpos($tplPath, '.') === false)
			{
				$templateAttributes[$tplPath] = array('disabled' => 'disabled');
			}
		}
		array_unshift($templateList, '(brak szablonu)');

		$element = $fieldset->createElement('select', 'page_template')
							->setLabel('Szablon')
							->setMultiOptions($templateList)
							->setOptionAttributes($templateAttributes)
							->addFilter('htmlspecialchars')
							->setDescription('Szablon używany do wyświetlenia dokumentu')
							->setValue('documentView.php');

		$element = $fieldset->createElement('checkbox', 'page_cache')
							->setLabel('Zapisywany w cache')
							->addFilter('int')
							->setDescription('Zaznacz jeżeli treść dokumentu ma być zapisywana w cache')
							->setValue((int) Config::getItem('page.cache') == 'true');

		$fieldset = $this->createFieldset('meta', 'Znaczniki meta');

		$element = $fieldset->createElement('text', 'meta_title')
							->setLabel('Tytuł strony')
							->setDescription('Zawartość znacznka title. Jeżeli pozostawisz to pole puste, wyświetlany będzie tytuł dokumentu')
							->addFilter('trim')
							->addFilter('strip_tags')
							->addFilter('htmlspecialchars');

		$element = $fieldset->createElement('textarea', 'meta_keywords')
							->setAttribute('cols', 70)
							->setAttribute('style', 'width: 55%')
							->setAttribute('rows', 10)
							->setLabel('Słowa kluczowe')
							->setDescription('Zawartość znacznka keywords. Pamiętaj, że kontroler może nadpisać te wartości')
							->addFilter('trim')
							->addFilter('strip_tags')
							->addFilter('htmlspecialchars');

		$element = $fieldset->createElement('textarea', 'meta_description')
							->setAttribute('cols', 70)
							->setAttribute('style', 'width: 55%')
							->setAttribute('rows', 10)
							->setLabel('Opis strony (description)')
							->setDescription('Zawartość znacznka description. Pamiętaj, że kontroler może nadpisać te wartości')
							->addFilter('trim')
							->addFilter('strip_tags')
							->addFilter('htmlspecialchars');

		$this->setDefaults();
	}

	protected function getTplList($dir)
	{
		$result = array();
		$count = 0;

		foreach (scandir($dir) as $file)
		{
			if ($file{0} != '.')
			{
				$suffix = pathinfo($file, PATHINFO_EXTENSION);

				if ((is_dir($dir . $file) && $file != 'adm' && $file != 'js') || $suffix == 'php')
				{
					$result[++$count] = array(
						'isDir'			=> is_dir($dir . $file),
						'filename'		=> $file
					);

					$sort['dir'][$count] = is_dir($dir . $file) ? 0 : 1;
					$sort['name'][$count] = $file;
				}
			}
		}
		if ($result)
		{
			array_multisort($sort['dir'], $sort['name'], $result);
		}

		return $result;
	}

	protected function loadTplList($dir, &$result)
	{
		$dir .= '/';
		foreach ($this->getTplList($dir) as $row)
		{
			$key = preg_replace('/.*?' . Config::getItem('core.template') . '\//i', '', $dir);
			$indent = str_repeat('&nbsp;', count(explode('/', $key)) * 2);

			if ($row['isDir'])
			{
				$result[$key . $row['filename']] = $indent . $row['filename'];
				$this->loadTplList($dir . $row['filename'], $result);
			}
			else
			{

				$result[$key . $row['filename']] = $indent . $row['filename'];
			}
		}
	}

	protected function getTemplateList()
	{
		$result = array();
		$this->loadTplList('template', $result);

		$core = &Core::getInstance();

		foreach (scandir('module') as $moduleName)
		{
			if ($moduleName{0} != '.')
			{
				if ($core->module->isEnabled($moduleName))
				{
					if (file_exists("module/$moduleName/template"))
					{
						$tmp = array();
						$this->loadTplList("module/$moduleName/template", $tmp);

						$result = array_merge($result, $tmp);
					}
				}
			}
		}
		return $result;
	}
}
?>