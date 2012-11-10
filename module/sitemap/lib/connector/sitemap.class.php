<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Sitemap extends Connector_Abstract implements Connector_Interface
{
	public function renderForm()
	{
		$fieldset = $this->createFieldset('content', 'Zawartość');

		$fieldset->createElement('text', 'page_subject', array(
			),
			array(
				'Label'			=> 'Tytuł dokumentu',
				'Filters'		=> array('trim', 'htmlspecialchars'),
				'Description'	=> 'Tytuł strony. Zalecana nazwa: sitemap.xml',
				'Required'		=> true,
				'Value'			=> 'sitemap.xml'
			)
		);

		$fieldset->createElement('text', 'page_parent', array(
			),
			array(
				'Label'			=> 'Dokument macierzysty',
				'Description' 	=> 'ID dokumentu macierzystego. Kliknij na ikonę, a następnie wybierz dokument macierzysty z drzewa dokumentów',
				'Filters'		=> array('int')
			)
		);

		if ($this->getId())
		{
			$isCached = file_exists('cache/sitemap_' . $this->getId());

			$fieldset->createElement('span', 'info1', array(
				),
				array(
					'Label'		=> 'Cache',
					'Value'		=> $isCached ? User::formatDate(filemtime('cache/sitemap_' . $this->getId())) : 'Brak',
					'Description' => 'Informuje o dacie ostatniego generowania mapy'
				)
			);

			if ($isCached)
			{
				$fieldset->createElement('span', 'info2', array(
					),
					array(
						'Label'	=> 'Rozmiar pliku',
						'Value'	=> Text::fileSize(filesize('cache/sitemap_' . $this->getId()))
					)
				);
			}
		}
	}

	public function onBeforeSave()
	{
		$values = $this->getValues();

		// dokument macierzysty (ID)
		$this->setParentId((int) $values['page_parent']);
		$this->setModuleId($this->module->getId('sitemap'));
		$this->setConnectorId($this->getConnectorId());
		$this->setContentType(Page::XML);

		$this->setSubject(@$values['page_subject']);
		$this->setPath(@$values['page_subject']);

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
	
	public function delete()
	{
		@unlink('cache/sitemap_' . $this->getId());
		
		parent::delete();
	}
}
?>