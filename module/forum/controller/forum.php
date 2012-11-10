<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

set_time_limit(0);

class Forum_Controller extends Page_Controller
{
	function __start()
	{
		parent::__start();

		switch ($this->get->mode)
		{
			case 'attachment':

				$post = &$this->getModel('post');
				echo $post->attachment->recive();

				exit;

			case 'paste':

				if (!empty($_POST['data']))
				{
					$post = &$this->getModel('post');
					echo $post->attachment->paste($_POST['data']);
				}

				exit;

			case 'submit':

				$this->__submit();

				exit;
		}

		if ($this->get->export == 'atom')
		{
			echo $this->getAtom();

			exit;
		}

		if ($this->input->isAjax())
		{
			if (isset($this->get->mode) || isset($this->post->mode))
			{
				$mode = isset($this->get->mode) ? $this->get->mode : $this->post->mode;

				if (method_exists($this, '__' . $mode))
				{
					$this->{'__' . $mode}();
				}

				exit;
			}
		}
	}

	private function &transform(&$content)
	{
		$this->parser->parse($content);
		return $content;
	}

	private function __finduser()
	{
		if (Config::getItem('user.name'))
		{
			if (!preg_match(Config::getItem('user.name'), $this->get['q']))
			{
				exit;
			}

			$userName = $this->get['q'];
		}
		else
		{
			$userName = $this->get->q;
		}

		$user = &$this->getModel('user');
		$result = $user->select('user_name, user_photo')->where('user_name LIKE "' . $userName . '%"')->order('user_lastvisit DESC')->limit(5)->fetchAll();
		$html = '';

		foreach ($result as $row)
		{
			if (!$row['user_photo'])
			{
				$row['user_photo'] = Url::__('template/img/avatar.jpg');
			}
			else
			{
				$row['user_photo'] = Url::__('store/_a/' . $row['user_photo']);
			}

			$thumbnail = Html::tag('img', false, array('src' => $row['user_photo'], 'width' => 18, 'height' => 18));
			$html .= Html::tag('li', true, array(), $thumbnail . preg_replace("~^($userName)~i", '<b>\\1</b>', $row['user_name']));
		}

		echo $html;
		exit;
	}

	/**
	 * @todo DRY (podobna metoda jest w kontrolerze topic.php)
	 */
	private function __preview()
	{
		$forum = &$this->getModel('forum');
		$id = $this->page->getForumId();

		$content = $this->post->value('content');

		$enableSmilies = $this->post->enableSmilies && User::data('allow_smilies');
		$enableHtml = $this->post->enableHtml && $forum->getAuth('f_html', $id);

		Forum::loadParsers($enableHtml, $enableSmilies, $this->post->attachment);

		$post = &$this->getModel('post');
		// pobranie informacji o cytowanych postach i przekazanie do parsera
		$this->parser->setOption('quote.postId', $post->getQuotedPost($content));

		echo $this->transform($content);
		exit;
	}

	private function __markread()
	{
		if (isset($this->get->forumId))
		{
			$forum = &$this->getModel('forum');
			$forumId = (int) $this->get->forumId;

			$pageId = $forum->select('forum_page')->where('forum_id = ?', $forumId)->fetchField('forum_page');

			$query = $forum->select('forum_id')
						   ->innerJoin('path', "parent_id = $pageId")
						   ->where('forum_page = child_id')
						   ->get();

			foreach ($query->fetchCol() as $forumId)
			{
				$forum->markRead($forumId);
			}
		}
		elseif (isset($this->get->topicId))
		{
			$topicId = (int) $this->get->topicId;
			$topic = &$this->getModel('topic');

			$forumId = (int) $topic->select('topic_forum')->where('topic_id = ?', $topicId)->fetchField('topic_forum');
			$topic->markRead($topicId, $forumId, time());
		}
		else
		{
			$forum = &$this->getModel('forum');
			$query = $forum->select('forum_id')->get();

			foreach ($query->fetchCol() as $forumId)
			{
				$forum->markRead($forumId);
			}
		}
	}

	private function __saveusertags()
	{
		$forum = &$this->getModel('forum');
		$forum->setting->setUserTags($this->post->tags);
		$forum->setting->save();

		$tag = &$this->getModel('tag');
		echo json_encode($tag->getWeight(explode(',', $forum->setting->getUserTags())));
	}

	private function __submit()
	{
		$id = $this->page->getForumId();
		$forum = &$this->getModel('forum');

		$isWriteable = (!$this->page->isLocked() && $forum->getAuth('f_write', $id));

		if (!$isWriteable)
		{
			throw new Error(401, 'Brak uprawnienień do pisania w tej kategorii forum');
		}

		$form = new Form_Submit(url($this->page->getLocation()) . '?' . $this->input->server('QUERY_STRING'), Forms::POST);
		$form->id = 'submit-form';

		$form->setEnableAntiSpam(User::$id == User::ANONYMOUS);

		$form->setEnableSticky($forum->getAuth('f_sticky', $id));
		$form->setEnableAnnouncement($forum->getAuth('f_announcement', $id));
		$form->setEnableAnonymous(User::$id == User::ANONYMOUS);
		$form->setEnableTags($forum->getAuth('f_tag', $id));
		$form->setEnableSmilies((bool) User::data('allow_smilies'));

		if ($forum->getAuth('f_html', $id))
		{
			$form->setEnableHtml(true);
		}

		if (User::$id == User::ANONYMOUS)
		{
			$form->setEnableWatch(false);
		}
		else
		{
			if (User::data('allow_notify'))
			{
				$form->setIsWatch(true);
			}
		}

		$form->addHtml(new View('_partialAttachment'));
		if ($this->module->isPluginEnabled('poll'))
		{
			$poll = new Form_Poll;
			$poll->renderForm();

			$form->addHtml(new View('_partialPollForm', array('form' => $poll)));
		}

		$form->addHtml(new View('_partialPreview'));

		$session = &Load::loadClass('session');
		if (!isset($session->forumHash))
		{
			$session->forumHash = md5(uniqid(rand(), true));
		}

		$form->setHash($session->forumHash);

		try
		{
			/*
			 * Domyslnym dzialaniem frameworka Coyote jest filtrowanie wartosci
			 * z tablicy _POST pod wzgledem niebezpiecznych znacznikow/atrybutow html.
			 * Ponizsza instrukcja omija to zabezpieczenie pobierajac tresc postu
			 * w czystej postaci. Filtracja znacznikow odbedzie sie podczas wyswietlania
			 * strony
			 */
			$content = trim($this->post->value('content'));

			if ($form->isValid())
			{
				Load::loadFile('lib/validate.class.php');
				$validate = new Validate_NotEmpty;

				if (!$validate->isValid($this->post->hash))
				{
					throw new Exception('Brak klucza');
				}

				if ($this->post->hash != $session->forumHash)
				{
					throw new Exception('Błędny klucz. Sprawdź ustawienia cookies');
				}
				$post = &$this->getModel('post');

				if ($post->isFlood())
				{
					throw new Exception('Musisz odczekać 10 sekund przed napisaniem kolejnego posta!');
				}
				$topic = &$this->getModel('topic');
				$page = new Page(new Connector_Topic);

				if (isset($poll))
				{
					$poll->isValid();
					$poll->setUserValues();

					if ($poll->getValue('items'))
					{
						$items = explode("\n", $poll->getValue('items'));

						$pollId = $this->getModel('poll')->submit(0,
																(string) $poll->getValue('title'),
																(int) time(),
																(int) $poll->getValue('length'),
																(int) $poll->getValue('max'),
																true,
																$items
															    );

						if ($pollId)
						{
							$page->setPollId($pollId);
						}
					}
				}

				$page->setForumId($id);
				$page->setSubject($form->getValue('subject'));
				$page->setUserName($form->getValue('username'));
				$page->setTopicContent($content);
				$page->setIsAnnouncement($form->getValue('announcement'));
				$page->setIsSticky($form->getValue('sticky'));
				$page->setEnableSmilies($form->getValue('enableSmilies'));
				$page->setEnableHtml($form->getValue('enableHtml') && $form->getEnableHtml());

				$this->load->model('page');
				$page->setGroups((array) $this->model->page->group->getGroups($page->getParentId()));

				UserErrorException::__(Trigger::call('application.onTopicSubmit', array(&$page)));

				/*
				 * Zapis danych. W metodzie save() zapis odbywa sie w
				 * transakcji.
				 */
				if (!$page->save())
				{
					throw new Exception('Temat nie został opublikowany. Wystąpił błąd podczas zapisu danych');
				}

				if ($this->post->watch)
				{
					$watch = &$this->getModel('watch');
					$watch->watch($page->getId(), $this->module->getId('forum'));
				}

				if ($forum->getAuth('f_tag', $id))
				{
					$tag = &$this->getModel('tag');
					$tag->insert($page->getId(), $this->post['tag']);
				}

				if ($this->post->attachment)
				{
					$post = &$this->getModel('post');
					$post->attachment->insert($page->getPostId(), $this->post->attachment);
				}

				Log::add($form->getValue('subject'), Topic::I_SUBMIT, $page->getId());
				UserErrorException::__(Trigger::call('application.onTopicSubmitComplete', array(&$page)));

				/*
				 * Usuniecie informacji o liczbie tematow w danych kategoriach forum (z cache)
				 */
				$this->cache->remove('sql_' . $this->module->getId('forum') . '*', Cache::PATTERN);

				// parsowanie komentarza i zwrocenie ID uzytkownikow, ktorych loginy znajduja sie w parsowanym komentarzu
				$recipients = Forum::getLogins($content);

				// z listy z ID userow, usuwamy ID uzytkownika, ktory wlasnie dodal komentarz
				$index = array_search(User::$id, $recipients);
				if ($index !== false)
				{
					unset($recipients[$index]);
				}

				$notify = new Notify(new Notify_Post_Login(array(

					'postId'			=> $page->getPostId(),
					'subject'			=> $this->page->getSubject(),
					'content'			=> $content,
					'userName'			=> $form->getValue('username'),
					'enableSmilies'		=> $form->getValue('enableSmilies'),
					'url'               => $page->getLocation() . '?p=' . $page->getPostId() . '#id' . $page->getPostId(),
					'recipients'        => $recipients,
					'isAttachments'     => isset($this->post->attachment)
				)));

				/*
				 * Wyslanie powiadomien do userow
				 */
				$notify->trigger();

				if (!$this->input->isAjax())
				{
					$this->redirect($page->getLocation());
				}
				else
				{
					$this->output->setContentType('text/plain');
					echo url($page->getLocation());
				}

				exit;
			}
			elseif ($form->hasErrors())
			{
				if ($this->input->isAjax())
				{
					$errors = array();
					foreach ($form->getErrors() as $field => $array)
					{
						$errors[$field] = $array[0];
					}

					$this->output->setContentType('application/json');
					echo json_encode(array(
						'errors' => $errors
						)
					);

					exit;
				}
			}
		}
		catch (SQLQueryException $e)
		{
			Log::add('Błąd SQL: ' . $e->getMessage(), E_ERROR, $this->page->getId());

			$this->output->setStatusCode(500);
			$this->output->setContentType('text/plain');

			echo 'Nie można dodać nowego wątku. Wystąpił bład SQL. Skontaktuj się z administratorem';

			exit;
		}
		catch (Exception $e)
		{
			Log::add('Błąd podczas dodawania nowego wątku: ' . $e->getMessage(), E_ERROR, $this->page->getId());

			if ($this->input->isAjax())
			{
				$this->output->setStatusCode(500);
				$this->output->setContentType('text/plain');

				echo $e->getMessage();
			}
			else
			{
				throw new UserErrorException($e->getMessage());
			}

			exit;
		}

		foreach ($this->getParents() as $row)
		{
			Breadcrumb::add(url($row['location_text']), $row['page_subject']);
		}
		Breadcrumb::add(url($this->page->getLocation()), $this->page->getSubject());
		Breadcrumb::add(url($this->page->getLocation()) . '?mode=submit&forumId=' . $id, 'Nowy temat');

		$view = new View('forumSubmit', array(
			'form'			=> $form,
			'hash'			=> $session->forumHash
			)
		);

		if ($this->page->getMetaTitle())
		{
			$this->output->setTitle(sprintf('Nowy temat na forum %s :: %s', $this->page->getMetaTitle(), Config::getItem('site.title')));
		}
		else
		{
			$this->output->setTitle(sprintf('Nowy temat na forum %s :: %s', ($this->page->getTitle() ? $this->page->getTitle() : $this->page->getSubject()), Config::getItem('site.title')));
		}

		echo $view;
	}

	/**
	 * Zapisuje ustawienia wyswietlania sekcji forum (okresla, czy ma byc ona widoczna czy tez nie)
	 */
	private function __section()
	{
		$forum = &$this->getModel('forum');

		if ((int) $this->post->id)
		{
			$forum->setting->setForumVisibility((int) $this->post->id, !$forum->setting->getForumVisibility((int) $this->post->id));
			$forum->setting->save();
		}
	}

	function main()
	{
		$forum = &$this->getModel('forum');

		/*
		 * Wsteczna kompatybilnosc..
		 */
		if (isset($this->get->f))
		{
			if (intval($forumId = $this->get->f))
			{
				$result = $forum->select('location_text')
								->innerJoin('location', 'location_page = forum_page')
								->where('forum_id = ?', $forumId)
								->fetchAssoc();

				if ($result)
				{
					$this->output->setStatusCode(301);
					$this->redirect($result['location_text']);
				}
			}
		}

		if (isset($this->get->view))
		{
			if (!$this->isValidMode($this->get->view))
			{
				$this->viewMode = 'all';
			}
			else
			{
				$forum->setting->setForumViewMode($this->get->view);
				$forum->setting->save();

				$this->viewMode = $this->get->view;
			}
		}
		else
		{
			if (!$this->viewMode = $forum->setting->getForumViewMode())
			{
				$this->viewMode = 'category';
			}
		}

		if ($this->viewMode == 'category')
		{
			$this->forum = $forum->fetch($this->page->getId());
		}
		else
		{
			$this->loadTopicList(0);

			$this->forumList = array(' ' => '-----');
			$this->forumList = array_merge($this->forumList, $forum->getHtmlList());
		}

		if ($this->input->isAjax())
		{
			if (!$this->page->isAllowed())
			{
				exit;
			}

			if ($this->viewMode == 'category')
			{
				$view = new View('_partialCategory');
			}
			else
			{
				$view = new View('_partialTopicList');
			}

			return $view;

		}
		else
		{
			$this->usersOnline = array();
			$this->anonymousUsersOnline = 0;

			Topic::retriveOnlineUsers($this->usersOnline, $this->anonymousUsersOnline);

			$this->userTags = array();
			$this->tags = '';

			if ($forum->setting->getUserTags())
			{
				$tag = &$this->getModel('tag');
				/**
				 * Poniewaz tagi sa zapisywane w cookies, przed wyswietleniem nalezy dokonac ponownej filtracji
				 */
				$this->userTags = array_unique(explode(' ', $tag->filter($forum->setting->getUserTags())));
				$this->tags = $tag->getWeight($this->userTags);
			}

			return parent::main();
		}
	}

	public function view()
	{
		$forum = &$this->getModel('forum');

		if ($this->page->getUrl())
		{
			$forum->update(array('forum_topics' => $this->page->getTopics() + 1), 'forum_id = ' . $this->page->getForumId());
			$this->redirect($this->page->getUrl());
		}

		$this->isWriteable = (!$this->page->isLocked() && $forum->getAuth('f_write', $this->page->getForumId()));
		$this->isVoteable = $forum->getAuth('f_vote', $this->page->getForumId());
		$this->forum = $forum->fetch($this->page->getId(), $this->page->getDepth());

		$topic = &$this->getModel('topic');

		if (isset($this->get->view))
		{
			$this->viewMode = $this->get->view;
		}
		else
		{
			if (!$this->viewMode = $forum->setting->getTopicViewMode())
			{
				$this->viewMode = 'all';
			}
		}

		// walidacja trybu wyswietlania tematow
		if (!$this->isValidMode($this->viewMode))
		{
			$this->viewMode = 'all';
		}

		/*
		 * Jezeli glosowanie jest wylaczone, nie mozemy pozwolic na wyswietlanie
		 * tylko wartosciowych postow - zmieniamy ustawienie trybu wyswietlania
		 * na 'all' (wszystkie tematy)
		 */
		if (!$this->isVoteable && $this->viewMode == 'votes')
		{
			$this->viewMode = 'all';
		}

		$this->loadTopicList($this->page->getForumId());
		$this->forumList = $forum->getHtmlList();

		$this->userTags = array();
		$this->tags = '';

		if ($this->input->isAjax())
		{
			if (!$this->page->isAllowed())
			{
				exit;
			}

			$view = new View('_partialTopicList');
		}
		else
		{
			$view = parent::main();

			if ($forum->setting->getUserTags())
			{
				$tag = &$this->getModel('tag');
				/**
				 * Poniewaz tagi sa zapisywane w cookies, przed wyswietleniem nalezy dokonac ponownej filtracji
				 */
				$this->userTags = array_unique(explode(' ', $tag->filter($forum->setting->getUserTags())));
				$this->tags = $tag->getWeight($this->userTags);
			}

			$this->pageList = array();
			for ($i = 10; $i <= 50; $i += 10)
			{
				$this->pageList[$i] = $i;
			}

			$session = &Load::loadClass('session');
			if (!isset($session->forumHash))
			{
				$session->forumHash = md5(uniqid(rand(), true));
			}
			$this->hash = $session->forumHash;

			$this->usersOnline = array();
			$this->anonymousUsersOnline = 0;

			Topic::retriveOnlineUsers($this->usersOnline, $this->anonymousUsersOnline);
		}

		return $view;
	}

	private function loadForumSettings($id)
	{
		$forum = &$this->getModel('forum');

		if (isset($this->get->page))
		{
			$forum->setting->setTopicsPerPage((int) $this->get->page);
			$forum->setting->save();

			$this->perPage = (int) $this->get->page;
		}
		else
		{
			if (!$this->perPage = (int) $forum->setting->getTopicsPerPage())
			{
				$this->perPage = 20;
			}
		}

		/**
		 * Walidacja liczby: ilosc postow na strone
		 */
		$this->perPage = max(10, min($this->perPage, 50));
	}

	private function getAtom()
	{
		$forum = &$this->getModel('forum');
		$result = array();

		$result = $forum->getByPage($this->page->getId())->fetchAssoc();

		if (isset($result['forum_id']))
		{
			/**
			 * Referencja do ID forum
			 */
			$id = &$result['forum_id'];

			if ($result['forum_url'])
			{
				exit;
			}
		}
		else
		{
			$id = null;
		}

		$atom = new Feed_Atom;
		$atom->setTitle($this->page->getTitle() ? $this->page->getTitle() : $this->page->getSubject());
		$atom->setLink(url($this->page->getLocation()));
		$atom->setId(Feed_Atom::getUuid('urn:uuid:', $this->page->getId()));

		$topic = &$this->getModel('topic');
		$query = $topic->getRecentlyTopics($id, 20)->get();

		Forum::loadParsers(false, false);

		foreach ($query as $row)
		{
			$element = $atom->createElement();

			$element->setTitle((isset($row['forum_subject']) ? ('[' . $row['forum_subject'] . '] ') : '') . $row['topic_subject']);
			$element->setLink(url($row['topic_path']));
			$element->setId(Feed_Atom::getUuid('urn:uuid:', $row['topic_id']));
			$element->setUpdated(max($row['post_time'], $row['post_edit_time']));
			$element->setContent($this->transform($row['post_text']));
			$element->setAuthor($row['post_user'] > User::ANONYMOUS ? $row['user_name'] : $row['post_username']);
		}

		$this->output->setHttpHeader('Last-Modified', date(DATE_RFC1123, $atom->getUpdated()));
		return $atom;
	}

	private function isValidSort($value)
	{
		return in_array($value, array('id', 'replies', 'views', 'votes', 'time', 'forum'));
	}

	private function isValidMode($value)
	{
		return in_array($value, array('all', 'category', 'unanswered', 'mine', 'user', 'votes'));
	}

	private function loadTopicList($id)
	{
		$this->loadForumSettings($id);
		$forum = &$this->getModel('forum');

		/*
		 * Przypisanie ID forum do pola. Dzieki temu to pole bedzie dostepne
		 * w widoku
		 */
		$this->forumId = $id;

		if (!empty($this->get->sort))
		{
			$this->order = $this->get->order;

			if ($this->isValidSort($this->get->sort))
			{
				$this->sort = $this->get->sort;

				$forum->setting->setSort($id, $this->get->sort);
				$forum->setting->setOrder($id, $this->get->order);
				$forum->setting->save();
			}
			else
			{
				$this->sort = 'time';
			}
		}
		else
		{
			$sort = $forum->setting->getSort($id);

			if (!$this->isValidSort($sort))
			{
				$sort = 'time';
			}
			$this->order = $forum->setting->getOrder($id);
			$this->sort = $sort;
		}

		if ($this->order != 'DESC' && $this->order != 'ASC')
		{
			$this->order = 'DESC';
		}
		$topic = &$this->getModel('topic');

		switch ($this->sort)
		{
			case 'id':

				$sqlSort = "topic_id $this->order";
				break;

			case 'replies':

				$sqlSort = "topic_replies $this->order";
				break;

			case 'views':

				$sqlSort = "topic_views $this->order";
				break;

			case 'votes':

				$sqlSort = "topic_vote $this->order";
				break;

			case 'time':

				$sqlSort = "topic_last_post_id $this->order";
				break;

			case 'forum':

				if (!$id)
				{
					$sqlSort = "topic_forum $this->order";
				}

				break;
		}
		parse_str($this->input->server('QUERY_STRING'), $query);
		$this->sortLinks = array();

		foreach (array('id', 'replies', 'views', 'votes', 'time', 'forum') as $element)
		{
			$httpQuery = array();

			$httpQuery = $query;
			$httpQuery['sort'] = $element;
			$httpQuery['order'] = $this->sort == $element ? ($this->order == 'DESC' ? 'ASC' : 'DESC') : 'DESC';

			$this->sortLinks[$element] = '?' . http_build_query($httpQuery, '', '&amp;');
		}

		$start = max(0, (int) $this->get['start']);

		/*
		 * W zaleznosci od kontekstu, tzn. jaki tryb wyswietlania watkow wybral user (bez odpowiedzi, wszystkie, moje itp)
		 * ladowana jest odpowiedni model, ktory ma za zadanie zwrocic liste watkow w formie tablicy PHP.
		 *
		 * Tak wiec caly proces pobierania watkow, w tym zapytania SQL, sa ukryte w klasie modelu
		 */
		Load::loadFile('model/topic/abstract.model.php');
		Load::loadFile('model/topic/' . $this->viewMode . '.model.php');

		$className = 'Topic_' . $this->viewMode . '_Model';
		$context = new $className;

		// Ustawienie ID forum (watki musza przynalezec do tego ID) - moze byc wartosc 0
		$context->setForumId($id);
		// Ustawienie tagow
		$context->setTags($this->get['tag']);
		// ustawienie sortowania SQL - np. topic_last_post_id DESC
		$context->setSort($sqlSort);
		// Ustawienie punktu poczatkowego (int) potrzebnego do stronnicowania
		$context->setOffset($start);
		// Ustawienie limitu watkow na strone
		$context->setLimit($this->perPage);

		/*
		 * Jezeli w GET znajduje sie parametr "user", oznacza, on zadanie wyswietlenia
		 * watkow/postow jedynie okreslonego usera.
		 */
		if (isset($this->get->user))
		{
			$context->setUserId($this->get->user);
		}

		/*
		 * Jezeli ID forum to 0, oznacza zadanie wyswietlanie wszystkich watkow na forum.
		 * Ale moga byc wyswietlane jedynie watki, do ktorych user ma dostep. Pomijamy rowniez
		 * kolejnosc wyswietlania watkow przyklejonych
		 */
		if (!$id)
		{
			$context->setOmitSticky(true);
			$context->setOmitNotAllowed(true);
		}

		$this->topic = $context->load(); // metoda load() zwraca tablice PHP z lista watkow
		$this->pagination = new Pagination('', $context->getTotalItems(), $this->perPage, $start);

		$this->topicTags = $this->topicClass = array();

		if ($this->topic)
		{
			$pageIds = array();

			foreach ($this->topic as $row)
			{
				$pageIds[] = $row['topic_page'];
			}
			$tag = &$this->getModel('tag');

			foreach ($tag->getTags($pageIds) as $pageId => $tags)
			{
				foreach ($tags as $_tag)
				{
					if (!in_array($_tag, (array) $this->page->getTags()))
					{
						$this->topicTags[$pageId][] = $_tag;
					}
					$this->topicClass[$pageId][] = str_replace('%', '', 't-' . rawurlencode($_tag));
				}
			}
		}

		return true;
	}

	public function __find()
	{
		$queryString = trim($this->get['like']);

		if ($queryString)
		{
			$connector = &$this->getModel('connector');
			$connectorId = $connector->getId('topic');

			$search = new Search;
			$search->setQueryString($queryString);
			$search->setLimit(10);
			$search->setGroups(array(1,2));
			$search->addFilter('connector', $connectorId);
			$search->addField('subject', '5.0');
			$search->addField('location', '2.0');
			$search->addField('post_text_t');

			$hits = $search->find();
			if ($search->getTotalRows())
			{
				$result = array();

				foreach ($hits as $hit)
				{
					$result[$hit->id] = array(
						'location'		=> $hit->location,
						'subject'		=> $hit->subject,
						'description'	=> $hit->getField('post_text_t')
					);
				}
				unset($hits);

				$topic = &$this->getModel('topic');
				$query = $topic->select('topic_page, topic_vote')->in('topic_page', array_keys($result))->get();

				foreach ($query as $row)
				{
					$result[$row['topic_page']]['votes'] = $row['topic_vote'];
				}

				echo new View('_partialTopicSearch', array('result' => $result));
			}
		}

		exit;
	}
}
?>