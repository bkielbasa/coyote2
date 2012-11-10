<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Bookmark_View extends Connector_Document implements Connector_Interface
{
	private $bookmarkId;
	private $url;
	private $host;
	
	function __construct($data = array())
	{
		parent::__construct($data);		
				
		if ($this->getId())
		{
			$bookmark = &$this->getModel('bookmark');
			$result = $bookmark->getByPage($this->getId())->fetchAssoc();
			
			$this->setUrl($result['bookmark_url']);
			$this->setBookmarkId($result['bookmark_id']);
		}
		else
		{
			/*
			 * Ustawienie wartosci domyslnych
			 */
			$this->setModuleId($this->module->getId('bookmark'));
			$this->setContentType(Page::HTML);
			$this->setTemplate('bookmarkView.php');
		}
	}
	
	private function setBookmarkId($bookmarkId)
	{
		$this->bookmarkId = $bookmarkId;
		return $this;
	}
	
	public function getBookmarkId()
	{
		return $this->bookmarkId;
	}
	
	public function setUrl($url)
	{
		$this->url = $url;		
		$this->setHost(parse_url($url, PHP_URL_HOST));
		
		return $this;
	}
	
	public function getUrl()
	{
		return $this->url;
	}
	
	public function setHost($host)
	{
		$this->host = $host;
		return $this;
	}
	
	public function getHost()
	{
		return $this->host;
	}
	
	public function renderForm()
	{
		parent::renderForm();

		$fieldset = &$this->getFieldset('content');
		$fieldset->removeElement('page_path');

		$url = $fieldset->createElement('text', 'url');
		$url->setLabel('Adres URL')
			->setDescription('Wymagany URL zakładki')
			->setOrder(-1)
			->setValue('http://')
			->setRequired(true);

		$url->addValidator(new Validate_Url);

		if (!$this->getId())
		{
			$url->addValidator(new Validate_Bookmark);
		}
		else
		{
			$url->setValue($this->getUrl());
		}
		
		$fieldset = &$this->getFieldset('setting');
		$fieldset->getElement('page_template')->setValue('bookmarkView.php');

		$this->setDefaults();
	}

	public function onBeforeSave()
	{
		parent::onBeforeSave();
		
		if ($this->isRenderMode())
		{
			$values = $this->getValues();			
			$this->setUrl(@$values['url']);
		}
		
		$bookmark = &$this->load->model('bookmark');

		if (!$this->getId())
		{			
			$bookmark->insert(array(
				'bookmark_url'			=> $this->getUrl(),
				'bookmark_host'			=> $this->getHost()
				)
			);

			$this->setBookmarkId($this->db->nextId());
		}

		$this->setFields(array('path' => $this->getBookmarkId() . ',' . self::encodePath($this->getSubject())));
	}

	public function onAfterSave()
	{
		$this->db->update('bookmark', array('bookmark_page' => $this->getId()), "bookmark_id = {$this->bookmarkId}");
	}

	public function delete()
	{
		$this->db->delete('bookmark', "bookmark_page = " . $this->getId());
		parent::delete();		
	}
}
?>