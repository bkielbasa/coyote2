<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

set_time_limit(0);

class Page_Controller extends Adm
{
	function main()
	{
		$this->setup();

		$report = &$this->getModel('report');
		$this->report = $report->fetch('report_close = 0')->fetchAll();

		$query = $this->db->select('t.*, user_name, p.page_id, p.page_subject')
						  ->innerJoin('page p', 'p.page_text = text_id')
						  ->leftJoin('user', 'user_id = t.text_user')
						  ->from('page_text t')
						  ->order('t.text_id DESC')
						  ->limit(10);
		$this->revision = $query->fetchAll();

		$query = $this->db->select('page_id, page_subject, page_edit_time, location_text')
						  ->from('page')
						  ->leftJoin('location', 'location_page = page_id')
						  ->order('page_edit_time DESC')
						  ->limit(10);
		$this->update = $query->fetchAll();

		$query = $this->db->select('page_id, page_subject, page_time, location_text')
						  ->from('page')
						  ->leftJoin('location', 'location_page = page_id')
						  ->order('page_id DESC')
						  ->limit(10);
		$this->insert = $query->fetchAll();

		return true;
	}

	private function setup()
	{
		$connector = &$this->getModel('connector');
		$this->connectorList = $connector->getConnectorList();
		$this->modules = array(0 => '-- wybierz moduł --');

		$query = $this->db->select('module_id, module_text')->from('module')->in('module_id', array_keys($this->connectorList))->get();
		foreach ($query as $row)
		{
			$this->modules[$row['module_id']] = $row['module_text'];
		}

		$this->deleted = $this->db->select('COUNT(*)')->from('page')->where('page_delete = 1')->get()->fetchField('COUNT(*)');
	}

	public function submit($id = 0)
	{
		$id = (int) $id;
		$result = array();

		/**
		 * Dezktywacja filtrow. Dane otrzymane w _POST/_GET beda potencjalnie
		 * niebezpieczne, brak filtracji XSS/SQL Injection
		 */
		$this->post->disableFilter();
		$this->attachment = array();

		$this->page = null;
		$connector = &$this->getModel('connector');

		if ($id)
		{
			if (!$page = Page::load((int) $id))
			{
				throw new AcpErrorException('Strona o podanym ID nie istnieje!');
			}
			$this->page = &$page; // utworzenie referencji dla latwiejszego zapisu
			$result = &$page->getData();

			$moduleId = $page->getModuleId();
			$connectorId = $this->post->page_connector($page->getConnectorId());
			$richtextId = isset($this->post->page_richtext) ? (int)$this->post->page_richtext : $page->getRichTextId();

			$this->attachment = $page->getAttachments();
		}
		else
		{
			$moduleId = (int)$this->get['moduleId'];
			$connectorId = (int)$this->post->page_connector($this->get['connectorId']);
			$richtextId = isset($this->post->page_richtext) ? (int)$this->post->page_richtext : Config::getItem('page.richtext');

			$result += array(
				'page_connector'		=> $connectorId
			);

			$connectorClass = $connector->select('connector_class')->where("connector_id = $connectorId")->get()->fetchField('connector_class');
			$connectorClass = 'Connector_' . $connectorClass;

			$page = new Page(new $connectorClass);
			$this->page = &$page;
		}

		if (!$id && (!$moduleId || !$connectorId))
		{
			throw new AcpErrorException('Brak parametru określającego ID modułu!');
		}

		if ($this->input->isPost())
		{
			if (isset($this->post->attachment))
			{
				foreach ($this->post->attachment as $attachmentId)
				{
					$this->attachment[] = new Attachment($attachmentId);
				}
			}
		}

		/**
		 * @todo Przeniesc do helpera?
		 */
		$this->setup();

		if ($richtextId)
		{
			$richtext = &$this->getModel('richtext');
			$result = array_merge($result, $richtext->find($richtextId)->fetchAssoc());

			$this->output->addJavascript($result['richtext_path']);
		}

		$field = &$this->getModel('field');
		$component = new Component;

		$this->moduleConfig = null;
		$this->moduleConfig = &$component->displayForm($moduleId);

		if ($this->moduleConfig->getElements())
		{
			$config = $this->module->getModuleConfig($moduleId, $id);
			$this->moduleConfig->setDefaults($config);
			$this->moduleConfig->setEnableDefaultDecorators(false);
		}

		/*
		 * Generowanie formularza do edycji strony
		 */
		$page->renderForm();

		if ($this->input->isPost())
		{
			$this->moduleConfig->setUserData();

			if (isset($this->post->reloadPage))
			{
				foreach ($page->getFieldsets() as $fieldset)
				{
					$fieldset->setUserData();
				}
			}
			elseif ($page->isValid())
			{
				$page->setGroups((array) $this->post->group);
				$page->setAttachments((array) $this->post->attachment);
				$page->setParsers((array) $this->post->parser);

				if (!$page->save())
				{
					throw new AcpErrorException('Dokument nie został prawidłowo zapisany. Sprawdź logi, co było przyczyną błędu');
				}

				if (isset($this->post->children))
				{
					$this->load->model('page');

					$pageIds = $this->model->page->getChildren($page->getId())->fetchCol('page_id');

					foreach ($pageIds as $pageId)
					{
						$this->model->page->group->setGroups($pageId, (array) $this->post->group);
					}
				}

				if ($page->getId())
				{
					$watch = &$this->getModel('watch');

					$notify = new Notify(

						new Notify_Page(array(

							'recipients' 	=> $watch->getUsers($id, $this->module->getId('main')),
							'url'			=> $page->getLocation()
							)
						)
					);
					$notify->trigger('application.onPageSubmitComplete');
				}
				$id = $page->getId();

				$module = &$this->getModel('module');
				$module->config->setModuleConfig($page->getModuleId(), $id, $this->moduleConfig->getValues());

				$this->redirect("adm/Page/View/$id");
			}

			$this->parentPageSubject = $this->getPageSubject($this->post->page_parent);
		}

		$group = &$this->getModel('group');
		$query = $group->select('group_id, group_name')->get();
		$this->groupList = $query->fetchPairs();

		if ($id)
		{
			$this->parentPageSubject = $this->getPageSubject($page->getParentId());
			$this->pageGroup = $page->getGroups($id);
		}
		else
		{
			$this->pageGroup = array(1, 2);
			$this->parentPageSubject = '(brak)';
		}

		$parser = &$this->getModel('page')->parser;

		$this->parser = $parser->getParsers();
		$this->pageParser = $page->getParsers();

		// do usuniecia niebawem...
		$this->id = $id;

		return View::getView('adm/pageSubmit', $result);
	}

	public function view($id = 0)
	{
		$id = (int)$id;
		$result = array();
		$page = &$this->getModel('page');

		if (!$result = $page->find($id)->fetchAssoc())
		{
			throw new AcpErrorException('Strona o podanym ID nie istnieje!');
		}

		$this->path = $this->db->select('location_text')->from('location')->where('location_page = ?', $id)->fetchField('location_text');

		$this->connectorText = $this->db->select('connector_text')->from('connector')->where('connector_id = ' . $result['page_connector'])->get()->fetchField('connector_text');
		$this->setup();

		$this->versions = $page->version->fetchVersions($id);
		$this->count = 0;

		$this->contentType = $this->db->select('content_type')->from('content')->where('content_id = ' . $result['page_content'])->fetchField('content_type');

		$report = &$this->getModel('report');
		$this->report = $report->fetch('report_page = ' . $id)->fetchAll();

		$this->hasOpenReport = (bool) count($report->select()->where('report_page = ' . $id . ' AND report_close = 0')->get());
		$this->watch = $this->db->select('uu.*, w.watch_time')->from('watch w, user uu')->where('w.page_id = ' . $id . ' AND uu.user_id = w.user_id')->fetchAll();

		$this->id = $id;

		return View::getView('adm/pageView', $result);
	}

	public function copy($id = 0)
	{
		$id = (int)$id;
		$page = &$this->getModel('page');
		$result = array();

		if (!$result = $page->find($id)->fetchAssoc())
		{
			throw new AcpErrorException('Strona o podanym ID nie istnieje!');
		}
		$this->setup();
		$this->filter = new Filter_Input;

		if ($this->input->isMethod(Input::POST))
		{
			$data['filter'] = array(

				'page_parent'			=> array('int')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				Load::loadFile('lib/validate.class.php');
				$validate = new Validate_Path(null, $this->post->page_parent);

				if (!$validate->isValid($result['page_path']))
				{
					throw new AcpErrorException('Dokument pod taką samą ścieżką istnieje już w kategorii do której chcesz skopiować tę stronę');
				}

				$pageId = $page->copy($id, (int) $this->post->page_parent);

				Log::add('Skopiowano do ID: #' . $this->post->page_parent, E_PAGE_COPY, $id);
				$this->redirect('adm/Page/View/' . $pageId);
			}
		}

		$this->id = $id;

		$this->parentPageSubject = '(brak)';
		return View::getView('adm/pageCopy', $result);
	}

	public function move($id = 0)
	{
		$id = (int)$id;

		$page = &$this->getModel('page');
		$result = array();

		if (!$result = $page->find($id)->fetchAssoc())
		{
			throw new AcpErrorException('Strona o podanym ID nie istnieje!');
		}
		$this->setup();
		$this->filter = new Filter_Input;

		if ($this->input->isMethod(Input::POST))
		{
			$data['filter'] = array(

				'page_parent'			=> array('int')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				Load::loadFile('lib/validate.class.php');
				$validate = new Validate_Path($id, $this->post->page_parent);

				if ($result['page_parent'] == $this->post->page_parent)
				{
					throw new AcpErrorException('Dokument już znajduje się pod podaną ścieżką');
				}
				if (!$validate->isValid($result['page_path']))
				{
					throw new AcpErrorException('Dokument pod taką samą ścieżką istnieje już w kategorii do której chcesz przenieść tę stronę');
				}

				unset($page);

				// obiekt klasy Page zawiera metode move() ktora dokonuje okreslonych
				// czynnosci w zaleznosci od lacznika
				$page = Page::load($id);

				try
				{
					$this->db->begin();

					$page->move($this->post->page_parent);
					$this->db->commit();

					Log::add('Nowa kategoria macierzysta: ' . $this->post->page_parent, E_PAGE_MOVE, $id);
				}
				catch (Exception $e)
				{
					$this->db->rollback();
					Log::add($e->getMessage(), E_ERROR);

					throw new AcpErrorException('Dokument nie mógł zostać przeniesiony. Powód: ' . $e->getMessage());
				}

				$this->redirect('adm/Page/View/' . $id);
			}
		}

		$this->id = $id;

		$this->parentPageSubject = '(brak)';
		return View::getView('adm/pageMove', $result);
	}

	public function delete($id = 0)
	{
		$id = (int)$id;
		$page = &$this->getModel('page');
		$result = array();

		if (!$result = $page->find($id)->fetchAssoc())
		{
			throw new AcpErrorException('Dokument o tym ID nie istnieje!');
		}
		$page->setDelete($id);
		Log::add($result['page_subject'], E_PAGE_DELETE, $id);

		$this->redirect("adm/Page/View/$id");
	}

	public function restore($id = 0)
	{
		$id = (int)$id;
		$page = &$this->getModel('page');
		$result = array();

		if (!$result = $page->find($id)->fetchAssoc())
		{
			throw new AcpErrorException('Dokument o tym ID nie istnieje!');
		}
		$page->setRestore($id);
		Log::add($result['page_subject'], E_PAGE_RESTORE, $id);

		$this->redirect("adm/Page/View/$id");
	}

	public function remove($id = 0)
	{
		$id = (int) $id;

		$page = Page::load($id);
		if ($page === false)
		{
			throw new AcpErrorException('Dokument o tym ID nie istnieje!');
		}

		$page->delete();
		Log::add($page->getSubject(), E_PAGE_DELETE, $id);

		$this->redirect('adm/Page');
	}

	public function revision($id = 0)
	{
		$page = &$this->getModel('page');
		$result = array();

		if (!$result = $page->find($id)->fetchAssoc())
		{
			throw new AcpErrorException('Dokument o tym nie istnieje!');
		}

		$query = $page->text->select('text_time, text_content')->where('text_id = ' . $this->get->r);

		if (!count($query))
		{
			throw new AcpErrorException('Rewizja o tym ID nie istnieje!');
		}
		$result = array_merge($result, $query->fetchAssoc());
		$this->setup();

		/**
		 * @todo Dodac mozliwosc parsowania archiwalnej wersji tekstu
		 */
		return View::getView('adm/pageRevision', $result);
	}

	public function diff()
	{
		$r1 = (int) $this->get['r1'];
		$r2 = (int) $this->get['r2'];

		if (!$r1 || !$r2)
		{
			throw new AcpErrorException('Błędne wywołanie programu. Brak numeru wersji do porównania');
		}
		$page = &$this->getModel('page');

		$t1 = $page->text->select('text_time, text_content')->where("text_id = $r1")->fetchAssoc();
		$t2 = $page->text->select('text_time, text_content')->where("text_id = $r2")->fetchAssoc();

		if (!$t1 || !$t2)
		{
			throw new AcpErrorException('Nieprawidłowe wywołanie programu');
		}

		$result = $page->find($this->get->id)->fetchAssoc();
		if (!$result)
		{
			throw new AcpErrorException('Dokument o tym ID nie istnieje!');
		}
		$this->setup();

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

		return View::getView('adm/pageDiff', $result);
	}

	private function getPageSubject($pageId)
	{
		$pageId = (int)$pageId;
		if (!$pageId)
		{
			return '(brak)';
		}

		if (!$subject = $this->db->select('page_subject')->from('page')->where('page_id = ' . $pageId)->get()->fetchField('page_subject'))
		{
			$subject = Config::getItem('site.title');
		}

		return $subject;
	}

	private function buildTree($result)
	{
		$nextDepth = 0;
		$xhtml = '<ul>';

		foreach ($result as $index => $row)
		{
			$depth = $row['page_depth'];
			$hasSubmenu = (bool) ($row['location_children'] > 0);

			$nextDepth = isset($result[$index + 1]) ? $result[$index + 1]['page_depth'] : 0;

			if ($row['page_delete'])
			{
				$row['page_subject'] = '<strike>' . $row['page_subject'] . '</strike>';
			}
			if (!$row['page_publish'])
			{
				$row['page_subject'] = '<cite>' . $row['page_subject'] . '</cite>';
			}

			switch ($row['content_type'])
			{
				case 'text/plain':
					$icon = 'plainTextIcon.png';
				break;

				case 'text/css':
					$icon = 'styleIcon.png';
				break;

				default:

					$icon = 'pageIcon.png';
			}

			$link = Html::img(url("template/adm/img/$icon"), array('title' => 'Szczegóły dokumentu'));
			$link .= Html::a(url('adm/Page/Submit/' . $row['page_id']), $row['page_subject'], array('title' => 'Edytuj dokument'));
			$link .= ' <small>(' . $row['location_children'] . ')</small>';

			if ($hasSubmenu && ($nextDepth > $depth))
			{
				$xhtml .= '<li><em class="open" id="page-' . $row['page_id'] . "\"></em>\n\t" . $link . "\n\t\t<ul>";
			}
			elseif ($hasSubmenu)
			{
				$xhtml .= '<li><em class="close" id="page-' . $row['page_id'] . "\"></em>\n\t" . $link . "\n\t\t</li>";
			}
			else
			{
				$xhtml .= '<li><em id="page-' . $row['page_id'] . "\"></em>\n\t" . $link . "\n\t</li>";
			}

			if ($nextDepth < $depth)
			{
				while ($nextDepth < $depth)
				{
					$xhtml .= "\n</ul></li>";
					--$depth;
				}
			}
		}
		$xhtml .= '</ul>';

		return $xhtml;
	}

	public function __purge()
	{
		$query = $this->db->select('page_id')
					  ->from('page')->where('page_delete = 1')->order('page_matrix DESC')->get();

		if (count($query))
		{
			foreach ($query as $row)
			{
				$page = Page::load((int) $row['page_id']);
				$page->delete();

				unset($page);
			}

			Log::add(null, E_PAGE_PURGE);
		}

		exit;
	}

	public function __find()
	{
		$subject = (string)$this->get['subject'];
		$subject = str_replace('*', '%', $subject);

		$page = &$this->getModel('page');
		$result = $page->findBySubject($subject);

		echo $this->buildTree($result);
		exit;
	}

	public function __displaytree()
	{
		$parentId = (int) $this->get['parentId'];
		$pageId = (int) $this->get['pageId'];

		$page = &$this->getModel('page');

		if (!$pageId)
		{
			$result = $page->getList($parentId);
		}
		else
		{
			$result = $page->getList($parentId); //$page->findById($pageId);
		}

		echo $this->buildTree($result);
		exit;
	}

	public function __pathencode()
	{
		if (!(int) $this->get['connectorId'])
		{
			$path = new Path;
			echo $path->encode((string) $this->get['path']);
		}
		else
		{
			$query = $this->db->select('connector_class')->where('connector_id = ' . (int)$this->get['connectorId'])->get('connector');
			$className = 'Connector_' . $query->fetchField('connector_class');

			echo call_user_func_array(array($className, 'encodePath'), array($this->get['path']));
		}

		exit;
	}
}
?>