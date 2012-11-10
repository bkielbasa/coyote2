<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Wiki_Controller extends Page_Controller
{
	private $path;
	private $parentId;
	private $parentSubject;

	function main()
	{
		header("cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

		$this->path = func_get_args();

		/*
		 * Nie uzywamy metody getPath() z klasy Input, poniewaz ta metoda
		 * filtruje dane
		 */
		$pathInfo = trim(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : getenv('PATH_INFO'), '/');
		if (empty($pathInfo))
		{
			$pathInfo = trim(isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : getenv('PATH_INFO'), '/');
		}
		$pathInfo = explode('/', $pathInfo);
		array_shift($pathInfo).

		$this->path = $pathInfo;

		$methodName = strtolower($this->router->mode);
		return $this->$methodName();
	}

	public function view()
	{
		$this->children = array();
		if ($this->module->wiki('enableMenu', $this->page->getId()))
		{
			$this->children = $this->getChildren();
		}

		/*
		 * Wsteczna kompatybilnosc z coyote 0.9
		 */
		$lines = explode("\n", $this->page->getContent(false));

		if (preg_match('~^#REDIRECT (.+)$~', $lines[0], $match))
		{
			$this->redirect(Url::__($match[1]));
		}

		return parent::main();
	}

	public function write()
	{
		if (User::$id == User::ANONYMOUS)
		{
			$this->redirect(Path::connector('login') . '?redirect=' . url('Write/' . $this->page->getLocation()));
		}

		$path = implode('/', $this->path);
		$this->page = Page::load((string) $path);

		if (!$pageId = $this->page->getId())
		{
			throw new Error(404, "Pod ścieżką <strong>$path</strong> nie znajduje się żadna strona");
		}

		foreach ($this->getParents() as $row)
		{
			Breadcrumb::add(url($row['location_text']), $row['page_subject']);
		}
		Breadcrumb::add(url($this->page->getLocation()), $this->page->getSubject());
		Breadcrumb::add('', 'Napisz nowy artykuł');

		Load::loadFile('lib/validate.class.php');

		$this->form = new Forms('', Forms::POST);
		$element = new Form_Element_Text('path');
		$element->addValidator(new Validate_Path(null, $pageId))
				->addFilter(new Filter_Path)
				->setLabel('Tytuł artykułu')
				->setRequired(true)
				->setDescription('Wpisz tytuł artykułu jaki chciałbyś napisać. Tę wartość będziesz mógł później zmienić')
				->setAttribute('style', 'width: 350px');

		$this->form->addElement($element);
		$this->form->createElement('submit')->setValue('Dalej >>');

		if ($this->form->isValid())
		{
			$encoder = new Path;
			$this->redirect('Edit/' . $path . '/' . $encoder->encode($this->form->getValue('path')) . '?subject=' . $this->form->getUnfilteredValue('path'));
		}

		return 'Write';
	}

	public function watch()
	{
		$this->page = Page::load((string) implode('/', $this->path));

		if (!$this->page)
		{
			throw new Error(404, 'Podana strona nie istnieje lub została usunięta');
		}
		elseif (!$this->page->isPublished() || $this->page->isDeleted())
		{
			throw new Error(404, 'Podana strona została usunięta lub nie jest opublikowana!');
		}
		elseif (!$this->page->isAllowed())
		{
			throw new Error(403, 'Brak dostępu');
		}

		if (User::$id == User::ANONYMOUS)
		{
			$this->redirect(Path::connector('login') . '?redirect=' . url($this->page->getLocation()));
		}

		$watch = &$this->getModel('watch');
		if ($watch->watch($this->page->getId(), $this->module->getId('wiki')))
		{
			$this->session->message = 'Strona <strong>' . $this->page->getSubject() . '</strong> została dodana do obserwowanych';
		}
		else
		{
			$this->session->message = 'Strona <strong>' . $this->page->getSubject() . '</strong> została usunięta z listy obserwowanych';
		}

		$this->redirect($this->page->getLocation());
	}

	public function preview()
	{
		$content = $this->post->value('text_content');
		$model = &$this->getModel('parser');

		$parser = &$this->load->library('parser');

		if (!$this->post->revision)
		{
			$query = $model->select('parser_name')->where('parser_default = 1')->order('parser_order')->get();
		}
		else
		{
			$page = Page::load((string) $this->post->location);
			$parserIds = $page->getParsers();

			$query = $model->select('parser_name')->in('parser_id', $parserIds)->order('parser_order')->get();
			unset($page);
		}

		if (count($query))
		{
			foreach ($query as $row)
			{
				$className = 'Parser_' . $row['parser_name'];
				$parser->addParser(new $className);
			}
		}

		$accessor = &$this->load->model('accessor');

		$accessor_arr = array();
		$revisions = array((int)$this->post->revision);

		$page = &$this->getModel('page');

		$template = &$page->template;
		$template_arr = $template->fetchTemplates($content);

		if ($template_arr)
		{
			$quote_arr = array_map(array('Text', 'quote'), $template_arr);
			$template_arr = array();

			$query = $this->db->select('page_id, text_id, text_content, LOWER(location_text) AS location_text')->from('page_v')->where('location_text IN(' . implode(',', $quote_arr) . ')')->get();
			while ($row = $query->fetchAssoc())
			{
				$template_arr[$row['location_text']] = $row['text_content'];
				$revisions[] = $row['text_id'];

				$accessor_arr = array_merge($accessor_arr, $accessor->fetchAccessors($row['text_content']));
			}

			$parser->setOption('wiki.template', $template_arr);
		}

		$accessor_arr = array_merge($accessor_arr, $accessor->fetchAccessors($content));
		if ($accessor_arr)
		{
			$quote_arr = array_map(array('Text', 'quote'), $accessor_arr);
			$accessor_arr = array();

			$query = $this->db->select('location_page, LOWER(location_text) AS location_text')->from('location')->where('location_text IN(' . implode(',', $quote_arr) . ')')->get();
			while ($row = $query->fetchAssoc())
			{
				$accessor_arr[mb_strtolower($row['location_text'])] = array(

					'class'			=>		'accessor'
				);
			}
			if ($accessor_arr)
			{
				$parser->setOption('wiki.accessor', $accessor_arr);
			}
		}
		$attachment = &$page->attachment;
		$attachment_arr = array();

		$query = $attachment->fetch('text_id IN(' . implode(',', $revisions) . ')');
		while ($row = $query->fetchAssoc())
		{
			$attachment_arr[Text::toLower($row['attachment_name'])] = $row;
		}
		if ($this->post->attachment)
		{
			foreach ($this->load->model('attachment')->fetch('attachment.attachment_id IN(' . implode(',', $this->post->attachment) . ')')->fetch() as $row)
			{
				$attachment_arr[Text::toLower($row['attachment_name'])] = $row;
			}
		}
		if ($attachment_arr)
		{
			$parser->setOption('wiki.attachment', $attachment_arr);
		}
		$parser->setOption('wiki.highlightBrokenLinks', true);
		$parser->setOption('html.allowTags', Connector_Wiki::getAllowedTags());
		$parser->setOption('tex.url', 'http://4programmers.net/cgi-bin/mimetex2.cgi');

		echo $parser->parse($content);
		exit;
	}

	public function history()
	{
		$this->page = Page::load(implode('/', $this->path));

		if (!$this->page)
		{
			throw new Error(404, 'Dokument o takim adresie nie istnieje!');
		}
		elseif (!$this->page->isAllowed())
		{
			throw new Error(403, 'Brak dostępu');
		}
		Config::setItem('wiki.subject', $this->page->getSubject());

		$page = &$this->getModel('page');
		$this->text = $page->version->fetchVersions($this->page->getId());

		foreach ($this->getParents() as $row)
		{
			Breadcrumb::add(url($row['location_text']), $row['page_subject']);
		}
		Breadcrumb::add(url($this->page->getLocation()), $this->page->getSubject());
		Breadcrumb::add('', 'Historia i autorzy');

		return 'History';
	}

	public function diff()
	{
		$this->page = Page::load((string) implode('/', $this->path));

		if (!$this->page)
		{
			throw new Error(404, 'Podana strona nie istnieje lub została usunięta');
		}
		elseif (!$this->page->isPublished() || $this->page->isDeleted())
		{
			throw new Error(404, 'Podana strona została usunięta lub nie jest opublikowana!');
		}
		elseif (!$this->page->isAllowed())
		{
			throw new Error(403, 'Brak dostępu');
		}

		$r1 = (int) $this->get['r1'];
		$r2 = (int) $this->get['r2'];

		if (!$r1 || !$r2)
		{
			throw new UserErrorException('Błędne wywołanie programu. Brak numeru wersji do porównania');
		}
		$page = &$this->getModel('page');

		$t1 = $page->text->select('text_time, text_content')->where("text_id = $r1")->fetchAssoc();
		$t2 = $page->text->select('text_time, text_content')->where("text_id = $r2")->fetchAssoc();

		if (!$t1 || !$t2)
		{
			throw new UserErrorException('Nieprawidłowe wywołanie programu');
		}

		Load::loadFile('lib/diff/Diff.php');
		Load::loadFile('lib/diff/Diff/Renderer.php');
		Load::loadFile('lib/diff/Diff/Renderer/unified.php');
		Load::loadFile('lib/diff/Diff/Renderer/inline.php');

		$diff = new Text_Diff('auto', array(explode("\n", $t2['text_content']), explode("\n", $t1['text_content'])));

		/**
		 * @todo Napisac wlasna klase rendera
		 */
		$renderer = new Text_Diff_Renderer_inline();
		$this->diff = $renderer->render($diff);

		$this->r1Time = $t1['text_time'];
		$this->r2Time = $t2['text_time'];

		foreach ($this->getParents() as $row)
		{
			Breadcrumb::add(url($row['location_text']), $row['page_subject']);
		}
		Breadcrumb::add(url($this->page->getLocation()), $this->page->getSubject());
		Breadcrumb::add('', 'Różnica wersji');

		return 'Diff';
	}

	public function edit()
	{
		if ($this->input->isAjax())
		{
			$path = new Path;
			echo $path->encode((string) $this->get['path']);

			exit;
		}
		Page::setEnable404(false);
		Page::setEnableRedirect(false);
		Page::setOmmitDelete(false);
		Page::setOmmitUnpublished(false);

		$this->page = Page::load(implode('/', $this->path));
		if (!$this->page)
		{
			$this->page = new Page(new Connector_Wiki);
		}

		if (User::$id == User::ANONYMOUS)
		{
			$this->redirect(Path::connector('login') . '?redirect=' . url('Edit/' . $this->page->getLocation()));
		}
		if (count($this->path) > 1)
		{
			$trunk = $this->path;
			array_pop($trunk);

			$location = &$this->getModel('location');

			$page = &$this->getModel('page');
			if (!$this->parentId = $location->getPageId(implode('/', $trunk)))
			{
				throw new Error(404, 'Brak artykułu w ścieżce ' . implode('/', $trunk));
			}

			$query = $page->find($this->parentId);
			$result = $query->fetchAssoc();

			$connector = &$this->getModel('connector');
			if ($result['page_connector'] != $connector->getId('wiki'))
			{
				throw new Error(404, 'Kategoria macierzysta artykułu jest nieprawidłowa');
			}

			$this->parentSubject = $query->fetchField('page_subject');
		}

		$this->attachment = array();
		$page = &$this->getModel('page');

		if ($this->page->getId())
		{
			if ($this->page->getModuleId() !== $this->module->getId('wiki'))
			{
				throw new Error(403, 'Nie masz możliwości edycji tej strony!');
			}
			if ($this->page->isDeleted() || !$this->page->isPublished())
			{
				throw new Error(404, 'Podana strona nie istnieje lub nie została opublikowana!');
			}
			if (!$this->page->isAllowed())
			{
				throw new Error(403, 'Brak dostępu');
			}
			if (!$this->module->wiki('enableWiki', $this->page->getId()) && !Auth::get('a_'))
			{
				throw new Error(403, 'Edycja tej strony została wyłączona');
			}

			foreach ($this->getParents() as $row)
			{
				Breadcrumb::add(url($row['location_text']), $row['page_subject']);
			}

			Breadcrumb::add(url($this->page->getLocation()), $this->page->getSubject());
			Breadcrumb::add('', 'Edycja artykułu');

			$this->attachment = $this->page->getAttachments();
			Config::setItem('wiki.subject', '"' . $this->page->getSubject() . '"');
		}
		else
		{
			Breadcrumb::add('', 'Dodawanie nowego artykułu');
		}

		$this->contentForm = $this->getContentForm();
		$this->metaForm = $this->getMetaForm();

		if ($this->input->isPost())
		{
			$this->post->disableFilter();

			if ($this->post->attachment)
			{
				foreach ($this->post->attachment as $attachmentId)
				{
					$this->attachment[] = new Attachment($attachmentId);
				}
			}

			/*
			 * Pobieramy "czysty" tekst (zawartosc artykulu), z uwagi na to, ze
			 * framework automatycznie zamieni znaki < w potencjalnie niebezpiecznych
			 * znacznikach na &lt; Chcemy, aby proces parsowania znakow html
			 * odbywal sie podczas wyswietlania tekstu
			 */
			$content = $this->post->value('text_content');

			if ($this->contentForm->isValid())
			{
				$this->metaForm->setUserValues();
				/*
				 * Metoda isValid() dokonuje walidacji oraz filtrowania danych.
				 * Walidatorow brak w formie metaForm -- ale sa filtry
				 */
				$this->metaForm->isValid();

				$richtext = &$this->getModel('richtext');
				$richtextId = $richtext->getByName('WikiEditor')->fetchField('richtext_id');

				$values = $this->contentForm->getValues();

				$this->page->setContentType(Page::HTML);
				$this->page->setSubject($values['page_subject']);
				$this->page->setTitle($values['page_title']);
				$this->page->setIp($this->input->getIp());
				$this->page->setLog($values['log']);
				$this->page->setRichTextId($richtextId);
				$this->page->setContent($content);
				$this->page->setTemplate('wikiView.php');
				$this->page->setAttachments((array)$this->post->attachment);
				$this->page->setGroups(array(1, 2));

				if (!$this->page->getId())
				{
					$this->page->setPath($values['page_path']);
					$this->page->setParentId($this->parentId);
				}
				else
				{
					if (Auth::get('w_rename'))
					{
						$this->page->setPath($values['page_path']);
					}
				}
				$parser = &$this->getModel('parser');
				$query = $parser->getByName(array('html', 'wiki', 'highlight', 'br', 'url'));

				$this->page->setParsers($query->fetchCol());

				$this->page->setMetaTitle($this->metaForm->getValue('meta_title'));
				$this->page->setMetaKeywords($this->metaForm->getValue('meta_keywords'));
				$this->page->setMetaDescription($this->metaForm->getValue('meta_description'));

				if (!$this->page->save())
				{
					throw new Error(500, 'Nie można zapisać dokumentu. Poinformuj administratora');
				}

				$watch = &$this->getModel('watch');
				$notification = new Notify_Page(array(
					'subject'		=> $this->page->getSubject(),
					'url'			=> $this->page->getLocation(),
					'log'			=> @$values['log']
					)
				);

				$notification->setRecipients($watch->getUsers($this->page->getId(), $this->module->getId('wiki')));

				$notify = new Notify($notification);
				$notify->trigger('application.onPageSubmitComplete');

				$this->redirect($this->page->getLocation());
			}
		}

		return 'Edit';
	}

	private function getContentForm()
	{
		Load::loadFile('lib/validate.class.php', false);

		$form = new Forms('', Forms::POST);
		$form->addDecorator('tag', array('tag' => 'ol'))
			 ->addDecorator('fieldset');

		$element = new Form_Element_Text('page_subject');
		$element->setLabel('Tytuł artykułu')
				->setRequired(true)
				->setDescription('Właściwy tytuł artykułu')
				->setFilters(array('trim', 'htmlspecialchars'))
				->setValue($this->page->getSubject());

		if (!$this->page->getId())
		{
			$element->setValue($this->get->subject);
		}

		$form->addElement($element);

		$element = new Form_Element_Text('page_title');
		$element->setLabel('Rozszerzony tytuł')
				->setDescription('Opcjonalnie. Rozszerzony tytuł dokumentu')
				->addFilter('htmlspecialchars')
				->setValue($this->page->getTitle());

		$form->addElement($element);

		$element = new Form_Element_Text('page_path');
		$element->setLabel('Ścieżka do artykułu')
				->setDescription('Ścieżka po której identyfikowany będzie artykuł')
				->setValue($this->page->getPath());

		if (!$this->page->getId())
		{
			$element->setValue($this->path[sizeof($this->path) -1]);
		}

		if ($this->page->getId() && !Auth::get('w_rename'))
		{
			$element->disabled = 'disabled';
		}
		else
		{
			$element->addFilter(new Filter_Path)
					->addValidator(new Validate_Path($this->page->getId(), $this->parentId))
					->setRequired(true);
		}

		$form->addElement($element);

		if ($this->parentSubject)
		{
			$form->createElement('span', 'parentId', array('class' => 'parent-span'))
				 ->setLabel('Dokument macierzysty')
				 ->setValue($this->parentSubject)
				 ->addFilter('int');
		}

		$element = new Form_Element_Textarea('text_content', array('class' => 'editor', 'id' => 'text_content', 'style' => 'width: 96%', 'rows' => 25));
		$element->addDecorator('description', array('tag' => 'small'))
				->addDecorator('errors', array('tag' => 'ul'))
				->addDecorator('tag', array('tag' => 'li', 'placement' => 'WRAP'))
				->setValue(htmlspecialchars($this->page->getContent(false)));

		$form->addElement($element);

		$validate = new Validate_String(true, 1, 200);
		$validate->setTemplate(Validate_String::TOO_LONG, 'Opis zmian nie może przekraczać 200 znaków');

		$element = new Form_Element_Text('log');
		$element->setLabel('Opis zmian')
				->setDescription('Jeżeli wprowadziłeś jakieś zmiany, dobrą praktyką jest opisanie jakich zmian dokonałeś')
				->addValidator($validate)
				->addFilter('trim')
				->addFilter('htmlspecialchars')
				->setAttribute('style', 'width: 600px');

		$form->addElement($element);

		$form->createElement('submit', '')->setValue('Zapisz zmiany');

		return $form;
	}

	public function getMetaForm()
	{
		$form = new Forms('', Forms::POST);
		$form->addDecorator('tag', array('tag' => 'ol'))
			 ->addDecorator('fieldset');

		$element = new Form_Element_Text('meta_title');
		$element->setLabel('Tytuł strony')
				->setDescription('Zawartość znacznika &lt;title&gt;')
				->addFilter('trim')
				->addFilter('strip_tags')
				->addFilter('htmlspecialchars')
				->setValue($this->page->getMetaTitle())
				->setAttribute('style', 'width: 500px');

		$form->addElement($element);

		$element = new Form_Element_Textarea('meta_keywords', array('cols' => 90, 'rows' => 10));
		$element->setLabel('Słowa kluczowe')
				->setDescription('Słowa kluczowe, zawartość znacznika &lt;meta&gt;')
				->addFilter('trim')
				->addFilter('strip_tags')
				->addFilter('htmlspecialchars')
				->setValue($this->page->getMetaKeywords());

		$form->addElement($element);

		$element = new Form_Element_Textarea('meta_description', array('cols' => 90, 'rows' => 10));
		$element->setLabel('Opis strony')
				->setDescription('Opis strony. Zawartość znacznika &lt;meta&gt;')
				->addFilter('trim')
				->addFilter('strip_tags')
				->addFilter('htmlspecialchars')
				->setValue($this->page->getMetaDescription());

		$form->addElement($element);

		return $form;
	}
}
?>