<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

abstract class Connector_Abstract extends Context implements Connector_Interface
{
	protected $data = array();
	protected $parsers = array();
	protected $attachments = array();
	protected $groups = null; // <-- domyslnie - null

	protected $renderMode;

	protected $pageId;
	protected $parentId;
	protected $moduleId;
	protected $connectorId;
	protected $subject;
	protected $title;
	protected $children;
	protected $depth;
	protected $isPublished = null;
	protected $publishDate;
	protected $unpublishDate;
	protected $isCached = null;
	protected $richTextId = null;
	protected $isDeleted;
	protected $template;
	protected $path;
	protected $location;
	protected $time;
	protected $editTime;

	protected $textId;
	protected $content;
	protected $log;
	protected $ip;
	protected $userId;

	protected $cache;
	protected $cacheTime;

	protected $metaTitle;
	protected $metaKeywords;
	protected $metaDescription;

	protected $tags = false;

	protected $contentType;

	protected $connectorClass;
	protected $connectorController;
	protected $connectorAction;
	protected $connectorFolder;
	protected $connectorName;

	protected $fieldsets = array();

	function __construct($data = array())
	{
		if ($data)
		{
			$this->parseArray($data);
		}
		else
		{
			$connector = &$this->getModel('connector');
			$className = strtolower(substr(get_class($this), 10));

			$result = $connector->getByClass($className)->fetchAssoc();
			if ($result)
			{
				$this->parseArray($result);
			}
		}

		parent::__construct();
	}

	public function isAllowed()
	{
		$page = &$this->getModel('page');
		return $page->isAllowed($this->getId(), User::$id);
	}

	public function setGroups(array $groupIds)
	{
		$this->groups = $groupIds;
		return $this;
	}

	public function getGroups()
	{
		if ($this->groups === null)
		{
			$page = &$this->getModel('page');
			$this->groups = $page->group->getGroups($this->getId());
		}

		return $this->groups;
	}

	public function getAttachments()
	{
		if (!$this->attachments && $this->getTextId())
		{
			/**
			 * @todo Zastapic wywolaniem metody z modelu!
			 */
			$query = $this->db->select('attachment_id')->from('page_attachment')->where('text_id = ' . $this->getTextId() . ' AND attachment_id IS NOT NULL')->get();

			if (count($query))
			{
				foreach ($query as $row)
				{
					$this->attachments[] = new Attachment($row['attachment_id']);
				}
			}
		}

		return $this->attachments;
	}

	public function addAttachment($attachment)
	{
		$this->attachments[] = $attachment;
		return $this;
	}

	public function setAttachments($attachments)
	{
		$this->attachments = $attachments;
		return $this;
	}

	public function setParsers(array $parserIds)
	{
		$this->parsers = $parserIds;
		return $this;
	}

	public function getParsers()
	{
		if (!$this->parsers)
		{
			if ($this->getId())
			{
				$query = $this->db->select('parser_id')->from('page_parser')->where('page_id = ?', $this->getId())->get();
				$this->parsers = $query->fetchCol();
			}
		}

		return $this->parsers;
	}

	protected function parseArray(array $array)
	{
		$this->data = &$array;

		$this->setFields(array(
			'pageId'				=> @$array['page_id'],
			'parentId'				=> @$array['page_parent'],
			'moduleId'				=> @$array['page_module'],
			'subject'				=> @$array['page_subject'],
			'title'					=> @$array['page_title'],
			'depth'					=> @$array['page_depth'],
			'isPublished'			=> @$array['page_publish'],
			'publishDate'			=> @$array['page_published'],
			'unpublishDate'			=> @$array['page_unpublished'],
			'isCached'				=> @$array['page_cache'],
			'richTextId'			=> @$array['page_richtext'],
			'isDeleted'				=> @$array['page_delete'],
			'template'				=> @$array['page_template'],
			'path'					=> @$array['page_path'],
			'time'					=> @$array['page_time'],
			'editTime'				=> @$array['page_edit_time'],

			'textId'				=> @$array['page_text'],
			'content'				=> @$array['text_content'],
			'userId'				=> @$array['text_user'],

			'log'					=> @$array['text_log'],
			'ip'					=> @$array['text_ip'],

			'cache'					=> @$array['cache_content'],
			'cacheTime'				=> @$array['cache_time'],

			'contentType'			=> @$array['content_type'],

			'metaTitle'				=> @$array['meta_title'],
			'metaKeywords'			=> @$array['meta_keywords'],
			'metaDescription'		=> @$array['meta_description'],

			'location'				=> @$array['location_text'],
			'children'				=> @$array['location_children'],

			'connectorId'			=> @$array['connector_id'],
			'connectorClass'		=> @$array['connector_class'],
			'connectorController'	=> @$array['connector_controller'],
			'connectorAction'		=> @$array['connector_action'],
			'connectorFolder'		=> @$array['connector_folder'],
			'connectorName'			=> @$array['connector_name']
			)
		);

		return $this;
	}

	protected function setFields(array $fields)
	{
		foreach ($fields as $field => $value)
		{
			$this->$field = $value;
		}

		return $this;
	}

	public function &getData()
	{
		return $this->data;
	}

	public function getController()
	{
		return $this->connectorController;
	}

	public function getAction()
	{
		return $this->connectorAction;
	}

	public function getFolder()
	{
		return $this->connectorFolder;
	}

	public function getConnectorName()
	{
		return $this->connectorName;
	}

	public function getId()
	{
		return $this->pageId;
	}

	public function setParentId($parentId)
	{
		if (!$parentId)
		{
			$parentId = null;
		}

		$this->parentId = $parentId;
		return $this;
	}

	public function getParentId()
	{
		return $this->parentId;
	}

	public function setModuleId($moduleId)
	{
		if (is_null($moduleId))
		{
			throw new Exception("ID modułu nie może być NULL");
		}

		$this->moduleId = $moduleId;
		return $this;
	}

	public function getModuleId()
	{
		return $this->moduleId;
	}

	public function setConnectorId($connectorId)
	{
		if (is_null($connectorId))
		{
			throw new Exception("ID łącznika nie może być NULL");
		}

		$this->connectorId = $connectorId;
		return $this;
	}

	public function getConnectorId()
	{
		return $this->connectorId;
	}

	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	public function getSubject()
	{
		return $this->subject;
	}

	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Ustawia sciezke identyfikujaca dany dokument.
	 * Metoda dokonuje konwersji sciezki, zastepujac m.in. spacje, znakami _
	 * zgodnie, z ustawieniami projektu. Konektory dziedziczace po tej klasie
	 * moga nadpisac sposob przypisywania sciezki do dokumentu
	 * @param $path	string Sciezka do dokumentu - np. Lorem ipsum
	 */
	public function setPath($path)
	{
		if (null !== $path)
		{
			$this->path = static::encodePath($path);
		}

		return $this;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getLocation()
	{
		return $this->location;
	}

	public function setContentType($contentType)
	{
		$this->contentType = $contentType;
		return $this;
	}

	public function getContentType()
	{
		return $this->contentType;
	}

	public function setIsPublished($flag)
	{
		$this->isPublished = (bool) $flag;
		return $this;
	}

	public function isPublished()
	{
		if ($this->isPublished === null)
		{
			$this->isPublished = Config::getItem('page.publish') == 'true' ? true : false;
		}

		return $this->isPublished;
	}

	public function setPublishDate($publishDate)
	{
		if ($timestamp = strtotime($publishDate))
		{
			if ($timestamp > time())
			{
				$this->setIsPublished(false);
			}

			$this->publishDate = $publishDate;
		}
	}

	public function getPublishDate()
	{
		return $this->publishDate;
	}

	public function setUnpublishDate($unpublishDate)
	{
		if ($timestamp = strtotime($unpublishDate))
		{
			if ($timestamp < time())
			{
				$this->setIsPublished(false);
			}

			$this->unpublishDate = $unpublishDate;
		}
	}

	public function getUnpublishDate()
	{
		return $this->unpublishDate;
	}

	public function setIsCached($flag)
	{
		$this->isCached = (bool) $flag;
		return $this;
	}

	public function isCached()
	{
		if ($this->isCached === null)
		{
			$this->isCached = Config::getItem('page.cache') == 'true' ? true : false;
		}

		return $this->isCached;
	}

	public function setRichTextId($richTextId)
	{
		$this->richTextId = $richTextId;
		return $this;
	}

	public function getRichTextId()
	{
		if ($this->richTextId === null)
		{
			$this->richTextId = (int) Config::getItem('page.richtext');
		}

		return $this->richTextId;
	}

	public function setIsDeleted($flag)
	{
		$this->isDeleted = (bool) $flag;
		return $this;
	}

	public function isDeleted()
	{
		return $this->isDeleted;
	}

	public function getChildren()
	{
		return $this->children;
	}

	public function getDepth()
	{
		return $this->depth;
	}

	public function getTextId()
	{
		return $this->textId;
	}

	public function setLog($log)
	{
		$this->log = $log;
		return $this;
	}

	public function getLog()
	{
		return $this->log;
	}

	public function setIp($ip)
	{
		$this->ip = $ip;
		return $this;
	}

	public function getIp()
	{
		return $this->ip;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	protected function initializeParsers()
	{
		$parser = &$this->getLibrary('parser');

		if ($this->getParsers())
		{
			$query = $this->db->select('parser_name')->in('parser_id', $this->getParsers())->order('parser_order')->get('parser');

			foreach ($query as $row)
			{
				$className = 'Parser_' . $row['parser_name'];
				$parser->addParser(new $className);
			}
		}

		$this->parser->setOption('tex.url', 'http://4programmers.net/cgi-bin/mimetex2.cgi');
	}

	public function getContent($parseContent = true)
	{
		if (!$parseContent)
		{
			return $this->content;
		}
		elseif ($this->getCacheTime())
		{
			return $this->getCache();
		}
		elseif ($this->content)
		{
			Trigger::call('application.onBeforeParseContent', array('content' => &$this->content));
			$this->initializeParsers();

			$parser = &$this->parser;

			if (isset($parser->wiki))
			{
				$parser->setOption('wiki.highlightBrokenLinks', true);

				$ids = array($this->getId());
				$revisions = array($this->getTextId());

				$page = &$this->getModel('page');
				$template_arr = array();

				/**
				 * Nalezy pobrac szablony, ktore zostaly wykorzystane w
				 * danym artykule. Pobranie rowniez rewizji szablonow
				 */
				$query = $page->template->fetch($this->getTextId());
				while ($row = $query->fetchAssoc())
				{
					$ids[] = $row['page_id'];
					$revisions[] = $row['text_id'];
					$template_arr[$row['location_text']] = $row['text_content'];
				}
				if ($template_arr)
				{
					$parser->setOption('wiki.template', $template_arr);
				}

				$attachment_arr = array();

				/**
				 * Pobranie zalacznikow do tekstow okreslonych z tablicy $revisions
				 */
				$query = $page->attachment->fetch('text_id IN(' . implode(',', $revisions) . ')');
				while ($row = $query->fetchAssoc())
				{
					$attachment_arr[Text::toLower($row['attachment_name'])] = $row;
				}
				if ($attachment_arr)
				{
					$parser->setOption('wiki.attachment', $attachment_arr);
				}

				$accessor = new Accessor_Model;
				$query = $accessor->fetch($ids);

				$accessor_arr = array();

				while ($row = $query->fetchAssoc())
				{
					$accessor_arr[mb_strtolower($row['location_text'])] = array(

							'class'			=>		'accessor',
							'title'			=>		$row['page_title'] ? $row['page_title'] : $row['page_subject']

					);
				}

				$parser->setOption('wiki.accessor', $accessor_arr);
			}
			$parser->parse($this->content);
			Trigger::call('application.onAfterParseContent', array('content' => &$this->content));

			if ($this->isCached())
			{
				Trigger::call('application.onBeforePageCache');

				try
				{
					$this->db->insert('page_cache', array(
						'cache_page'		=> $this->getId(),
						'cache_time'		=> time(),
						'cache_content'		=> $this->content
						)
					);
				}
				catch (Exception $e)
				{
					Log::add($e->getMessage(), E_ERROR);
				}
			}
		}
		return $this->content;
	}

	public function getTime()
	{
		return $this->time;
	}

	public function getEditTime()
	{
		return $this->editTime;
	}

	protected function setCache($cacheContent)
	{
		$this->cache = $cacheContent;
	}

	public function getCache()
	{
		return $this->cache;
	}

	public function getCacheTime()
	{
		return $this->cacheTime;
	}

	public function setTemplate($template)
	{
		$this->template = $template;
		return $this;
	}

	public function getTemplate()
	{
		return $this->template;
	}

	public function setMetaTitle($metaTitle)
	{
		$this->metaTitle = $metaTitle;
		return $this;
	}

	public function getMetaTitle()
	{
		return $this->metaTitle;
	}

	public function setMetaDescription($metaDescription)
	{
		$this->metaDescription = $metaDescription;
		return $this;
	}

	public function getMetaDescription()
	{
		return $this->metaDescription;
	}

	public function setMetaKeywords($metaKeywords)
	{
		$this->metaKeywords = $metaKeywords;
		return $this;
	}

	public function getMetaKeywords()
	{
		return $this->metaKeywords;
	}

	public function setTags($tags)
	{
		$this->tags = $tags;
		return $this;
	}

	/**
	 * Zwraca tagi przypisane do danej strony w formie tablicy PHP
	 * @return 	array|bool	Zwraca tablice PHP lub FALSE jezeli zadne tagi nie sa przypisane do dokumentu
	 */
	public function getTags()
	{
		if ($this->tags === false)
		{
			$tags = $this->getModel('tag')->getTags($this->getId());

			if ($tags)
			{
				$this->tags = $tags[$this->getId()];
			}
			else
			{
				$this->tags = true;
			}
		}
		return !is_bool($this->tags) ? $this->tags : false;
	}

	public function save()
	{
		UserErrorException::__(Trigger::call('application.onPageSubmit', array(&$this)));
		$page = &$this->getModel('page');

		if ($this->hasChanged($this->content))
		{
			$data = array(
				'text_content'	=> (string) $this->content,
				'text_log'		=> (string) $this->log,
				'text_time'		=> time(),
				'text_user'		=> User::$id,
				'text_ip'		=> $this->ip
			);
			$this->textId = $page->text->insert($data);

			/**
			 * Inicjalizacja klasy Parsera. Nalezy wydzielic z tekstu tresci pomiedzy znacznikami
			 * <plain> oraz <code> aby pobrac liste odnosnikow w tekscie oraz liste uzytych szablonow
			 */
			$parser = &Load::loadClass('parser');
			$parser->setContent($this->content);
			$plain_arr = $parser->extract('plain|code');

			$accessor = &$this->load->model('accessor');
			// pobranie listy odnosnikow w tekscie
			$accessor_arr = $accessor->fetchAccessors($parser->getContent());

			// pobranie listy szablonow w tekscie
			$template_arr = $page->template->fetchTemplates($parser->getContent());

			if ($template_arr)
			{
				$page->template->insert($this->getTextId(), $template_arr);
			}

			UserErrorException::__(Trigger::call('application.onTextSubmitComplete', $this->pageId, $this->textId, $this->content, $this->log));
		}

		$data = array(
			'page_subject'			=> (string) $this->getSubject(),
			'page_title'			=> $this->getTitle(),
			'page_publish'			=> (bool) $this->isPublished(),
			'page_cache'			=> (bool) $this->isCached(),
			'page_content'			=> (int) $this->getContentType(),
			'page_richtext'			=> (int) $this->getRichTextId(),
			'page_published'		=> $this->getPublishDate(), // moze byc null
			'page_unpublished'		=> $this->getUnpublishDate(), // moze byc null
			'page_template'			=> (string) $this->getTemplate(),
			'page_connector'		=> (int) $this->getConnectorId(),
			'page_path'				=> (string) $this->getPath(),
			'page_edit_time'		=> time()
		);

		if (!$this->getId())
		{
			$data += array(
				'page_module'		=> (int) $this->getModuleId(),
				'page_parent'		=> $this->getParentId(),
				'page_time'			=> time()
			);

			$this->db->insert('page', $data);
			$this->pageId = $this->db->nextId();
		}
		else
		{
			$this->db->update('page', $data, "page_id = {$this->pageId}");
			$clearCache = true;
		}

		$page->group->setGroups($this->getId(), $this->getGroups());
		$page->version->insert($this->getId(), $this->getTextId());

		if (isset($accessor_arr))
		{
			// dodanie informacji o odnosnikac w tekscie
			$accessor->insert($this->getId(), $accessor_arr);
		}

		$this->location = $this->db->select('location_text')->from('location')->where('location_page = ?', $this->pageId)->fetchField('location_text');

		$meta = &$this->load->model('meta');
		$meta->setMeta($this->getId(), $this->getMetaTitle(), $this->getMetaKeywords(), $this->getMetaDescription());

		if ($this->getTags() !== false)
		{
			$tag = &$this->getModel('tag');
			$tag->insert($this->getId(), $this->getTags());
		}

		$attachments = array();

		/*
		 * Tutaj wywolanie POLA attachments - NIE metody getAttachments()
		 */
		foreach ($this->attachments as $attachment)
		{
			if ($attachment instanceof Attachment)
			{
				$attachments[] = $attachment->getId();
			}
			else
			{
				$attachments[] = (int)$attachment;
			}
		}

		if ($attachments)
		{
			$page->attachment->insert($this->getTextId(), $attachments);
		}

		$page->parser->insert($this->getId(), $this->getParsers());

		if (isset($clearCache))
		{
			// czyscimy cache gdyz niezaleznie czy nowa rewizja zostala zapisana, zmiany konfiguracji moga miec wplyw na wyswietlanie dokumentu
			$this->db->query('CALL CLEAR_CACHE(' . $this->pageId . ')');
		}
		Log::add($this->getSubject(), E_PAGE_SUBMIT, $this->pageId);

		UserErrorException::__(Trigger::call('application.onPageSubmitComplete', array(&$this)));
	}

	public function delete()
	{
		Trigger::call('application.onPageDelete', $this->getId());

		$page = &$this->getModel('page');
		$page->delete($this->getId());

		Trigger::call('application.onPageDeleteComplete', $this->getId());
	}

	/**
	 * ID strony macierzystej, do ktorej zostanie przeniesiona dana strona
	 */
	public function move($parentId)
	{
		Trigger::call('application.onPageMove', $this->getId(), $parentId);

		$page = &$this->getModel('page');
		$page->move($this->getId(), $parentId);

		Trigger::call('application.onPageMoveComplete', $this->getId(), $parentId);
	}

	private function hasChanged(&$content)
	{
		if (!$content && !$this->getTextId() && !sizeof($this->attachments))
		{
			return false;
		}
		$result = true;

		if ($this->getTextId())
		{
			$query = $this->db->select('MD5(text_content) AS hash')->from('page_text')->where("text_id = ?", $this->getTextId())->get();
			if (!count($query))
			{
				$result = true;
			}
			else
			{
				$result = !(md5($content) == $query->fetchField('hash'));
			}
		}
		else
		{
			$result = true;
		}

		return $result;
	}

	protected function createFieldset($name, $label)
	{
		$this->fieldsets[$name] = new Form_Fieldset;
		$this->fieldsets[$name]->setMethod(Forms::POST);
		$this->fieldsets[$name]->setLabel($label)->setTemplate('userForm');
		$this->fieldsets[$name]->removeDecorators();

		return $this->fieldsets[$name];
	}

	protected function removeFieldset($name)
	{
		unset($this->fieldsets[$name]);
		return $this;
	}

	protected function setDefaults()
	{
		if ($this->data)
		{
			foreach ($this->getFieldsets() as $name => $element)
			{
				$this->fieldsets[$name]->setDefaults($this->data);
			}
		}

		return $this;
	}

	public function getFieldset($name)
	{
		return isset($this->fieldsets[$name]) ? $this->fieldsets[$name] : false;
	}

	public function getFieldsets()
	{
		return $this->fieldsets;
	}

	public function isValid()
	{
		$isValid = true;

		foreach ($this->getFieldsets() as $fieldset)
		{
			$fieldset->setUserData();

			if (!$fieldset->isValid())
			{
				$isValid = false;
			}
		}

		return $isValid;
	}

	protected function getValues()
	{
		$values = array();

		foreach ($this->getFieldsets() as $fieldset)
		{
			$values = array_merge($values, $fieldset->getValues());
		}

		return $values;
	}

	public function renderForm()
	{
		$this->renderMode = true;
	}

	public function isRenderMode()
	{
		return $this->renderMode;
	}

	public function onBeforeSave()
	{
		if ($this->isRenderMode())
		{
			$values = $this->getValues();
			// modulu nie mozna zmienic jezeli strona juz istnieje...
			$moduleId = $this->getModuleId() ? $this->getModuleId() : $this->get->moduleId;

			// dokument macierzysty (ID)
			$this->setParentId((int) @$values['page_parent']);

			// tytul strony
			$this->setSubject(@$values['page_subject']);

			// drugi tytul
			$this->setTitle(@$values['page_title']);

			$this->setPath(@$values['page_path']);
			$this->setModuleId((int) $moduleId);
			$this->setConnectorId((int) @$values['page_connector']);
			$this->setContent(@$values['text_content']);
			$this->setContentType(@$values['page_content']);
			$this->setLog(@$values['log']);
			$this->setIp($this->input->getIp());
			$this->setRichTextId(@$values['page_richtext']);
			$this->setIsPublished(@$values['page_publish']);
			$this->setIsCached(@$values['page_cache']);
			$this->setPublishDate(@$values['page_published']);
			$this->setUnpublishDate(@$values['page_unpublished']);
			$this->setTemplate(@$values['page_template']);
			$this->setMetaTitle(@$values['meta_title']);
			$this->setMetaKeywords(@$values['meta_keywords']);
			$this->setMetaDescription(@$values['meta_description']);

			$this->setTags(@$values['tags']);
		}
	}

	public function onAfterSave()
	{
		if ($this->isRenderMode())
		{
			if ($this->post->style)
			{
				@file_put_contents('store/css/page-' . $this->getId() . '.css', $this->post->style, LOCK_EX);
			}
			else
			{
				@unlink('store/css/page-' . $this->getId() . '.css');
			}
		}
	}

	public function &getDocument()
	{
		$document = new Search_Document;
		$document->addField('id', $this->getId());
		$document->addField('subject', $this->getSubject());
		$document->addField('location', $this->getLocation());

		if ($this->getMetaKeywords())
		{
			$document->addField('keywords', $this->getMetaKeywords());
		}
		if ($this->getTitle())
		{
			$document->addField('title', $this->getTitle());
		}
		if ($this->getMetaTitle())
		{
			$document->addField('title', $this->getMetaTitle());
		}
		if ($this->getMetaDescription())
		{
			$document->addField('description', $this->getMetaDescription());
		}
		if ($this->getTags())
		{
			$document->addField('tag', implode(' ', $this->getTags()));
		}
		$document->addField('module', $this->getModuleId());
		$document->addField('connector', $this->getConnectorId());
		$document->addField('timestamp', $this->getEditTime());

		foreach ($this->getGroups() as $groupId)
		{
			$document->addField('group', $groupId);
		}
		$document->addField('body', strip_tags($this->getContent(false)));

		return $document;
	}

	public static function encodePath($value)
	{
		$encoder = new Path;
		return $encoder->encode($value);
	}
}
?>