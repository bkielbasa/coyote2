<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Comment_Controller extends Controller
{
	public function submit($id = 0)
	{
		if (!$this->input->isAjax())
		{
			exit;
		}
		$id = (int) $id;

		try
		{
			$moduleId = (int) $this->post->moduleId;

			// sprawdzenie, czy w danym module wlaczone sa komentarze
			if (!$this->module->getPlugin($this->module->getName($moduleId), Comment::NAME))
			{
				throw new Exception('Pisanie komentarzy na tej stronie zostało wyłączone');
			}
			$watch = &$this->load->model('watch');

			$module = $this->module->getName($moduleId);
			$pluginId = &$this->module->getPluginId('comment');

			$pageId = (int) $this->post->pageId;

			if (isset($this->get->watch))
			{
				if (User::$id > User::ANONYMOUS)
				{
					if (isset($this->post->notify) && !$watch->isWatched($pageId, $moduleId, $pluginId))
					{
						$watch->watch($pageId, $moduleId, $pluginId);
					}
					elseif (!isset($this->post->notify) && $watch->isWatched($pageId, $moduleId, $pluginId))
					{
						$watch->watch($pageId, $moduleId, $pluginId);
					}

					exit;
				}
				else
				{
					throw new Exception('Musisz być zalogowany, aby obserwować komentarze');
				}
			}

			$enableAnonymous = $this->module->$module('commentEnableAnonymous', $pageId);
			$enableHtml = $this->module->$module('commentEnableHtml', $pageId);

			if ($enableAnonymous == 2)
			{
				throw new Exception('Pisanie komentarzy zostało wyłączone!');
			}
			if (User::$id == User::ANONYMOUS)
			{
				if ($enableAnonymous == 0)
				{
					throw new Exception('Aby napisać komentarz, musisz się zalogować!');
				}
			}

			/**
			 * @todo Poprawienie zabezpieczenia -- tutaj powinno sie wykonywac zapytanie
			 * SQL sprawdzajace po IP ostatni czas napisania komentarza
			 */
			if ((time() - $this->session->flood) < 5)
			{
				throw new Exception('Proszę poczekać pare sekund przed napisaniem kolejnego komentarza');
			}

			$filter = new Filter_Input;

			$data['validator'] = array(

					'content'					=> array(
															array('notempty', 'templates' => array(

																'IS_EMPTY' => 'Treść komentarza nie może być pusta'
																)
															)
												),
					'username'					=> array(
															array('string', true, 3, 'templates' => array(

																'STRING_EMPTY'		=> 'Pole "Nazwa użytkownika" nie może być pusta',
																'TOO_SHORT'			=> 'Nazwa użytkownika musi mieć min. 3 znaki'
																)
															),
															array('match', '/^[0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ.=:|#_ ()[\]^-]*?$/', 'templates' => array(

																'NOT_MATCH' => 'Nazwa użytkownika jest nieprawidłowa'
																)
															)
												)
			);

			if (User::$id == User::ANONYMOUS)
			{
				$data['validator']['username'][0][1] = false;
			}
			$filter->setRules($data);

			if (!$filter->isValid($_POST))
			{
				Load::helper('array');
				$error = '';

				foreach ($filter->getErrors() as $errors)
				{
					$error = $errors[0];
					break;
				}

				throw new Exception($error);
			}
			unset($data);

			$comment = &$this->getModel('comment');
			$data['comment_content'] = $this->post->content;

			$username = $this->post->username;

			if (!$id)
			{
				$data += array(
					'comment_user'		=>	User::$id,
					'comment_time'		=>	time(),
					'comment_ip'		=>	$this->input->getIp(),
					'comment_page'		=>	$pageId,
					'comment_module'	=>	$moduleId
				);
				if (User::$id == User::ANONYMOUS)
				{
					$data += array('comment_username'	=>	$this->post->username);
				}
				UserErrorException::__(Trigger::call('application.onCommentSubmit', array(&$data)));

				$id = $comment->insert($data);
				UserErrorException::__(Trigger::call('application.onCommentSubmitComplete', array($id, &$data)));

				$page = &$this->load->model('page');
				$result = $page->getById($pageId)->fetchAssoc();

				$result['u_comment'] = $this->input->getReferer() . '#comment-' . $id;

				$notification = new Notify_Comment(array(

					'comment'			=> $this->post->content,
					'subject'			=> $result['page_subject'],
					'url'				=> $result['u_comment'],
					'enableAnonymous'	=> $enableAnonymous,
					'enableHtml'		=> $enableHtml,
					'userName'			=> $this->post->username
					)
				);

				// dodanie odbiorcow powiadomienia
				$notification->setRecipients($watch->getUsers($pageId, $moduleId, $pluginId));

				$notify = new Notify($notification);
				$notify->trigger('application.onCommentSubmitComplete');
			}
			else
			{
				$comment->update($data, "comment_id = $id");
			}

			$this->session->flood = time();

			$parser = &$this->load->library('parser');

			$parser->addParser(new Parser_Html);
			$parser->addParser(new Parser_Url);
			$parser->addParser(new Parser_Br);

			$enableHtml = $this->module->$module('commentEnableHtml', $pageId);

			// domyslnie - dozwolony jest pewien zestaw znacznikow
			$parser->setOption('html.allowTags', array('b', 'i', 'u', 'del', 'hr', 'sup', 'sub', 'code', 'kbd', 'tt', 'pre', 'strong', 'a'));

			// jezeli html jest wylaczony - nalezy ustawic to w konfiguracji parsera
			if ($enableHtml == 0)
			{
				$parser->setOption('html.allowTags', array(''));

			}
			else if ($enableHtml == 1)
			{
				// jezeli html jest wlaczony dla zarejestrowanych, nalezy
				// usunac liste dozwolonych znacznikow jezeli user jest anonimem
				if (User::$id == User::ANONYMOUS)
				{
					$parser->setOption('html.allowTags', array(''));
				}
			}

			$content = $this->post->content;
			$content = $parser->parse($content);

			echo new View('_partialComment', array(
				'enableDelete'			=> $this->module->$module('commentEnableDelete', $pageId),
				'row'					=> array(

						'comment_id'			=> $id,
						'comment_user'			=> User::$id,
						'comment_time'			=> time(),
						'comment_content'		=> $content,
						'comment_username'		=> $this->post->username,
						'comment_ip'			=> User::$ip,
						'user_id'				=> User::$id,
						'user_name'				=> User::data('name'),
						'user_photo'			=> User::data('photo')
				)
			));
		}
		catch (Exception $e)
		{
			$this->output->setStatusCode(500);

			echo $e->getMessage();
			exit;
		}

		exit;
	}

	public function edit($id = 0)
	{
		if (User::$id == User::ANONYMOUS)
		{
			throw new Error(500, 'Edycja komentarzy możliwa jest przez zalogowanych użytkowników!');
		}
		$id = (int) $id;
		$comment = &$this->getModel('comment');

		if (!$result = $comment->find($id)->fetchAssoc())
		{
			throw new Error(404, 'Komentarz o tym ID nie istnieje!');
		}

		if (($result['comment_user'] != User::$id) && !Auth::get('c_edit'))
		{
			throw new Error(403, 'Nie masz uprawnień do edycji tego komentarza!');
		}

		$this->filter = new Filter_Input;
		if (!$this->page = Page::load((int) $result['comment_page']))
		{
			throw new Exception('Błąd! Strona nie istnieje!');
		}

		$module = $this->module->getName($this->page->getModuleId());
		$enableAnonymous = $this->module->$module('commentEnableAnonymous', $this->page->getId());

		if ($enableAnonymous == 2)
		{
			throw new Error(403, 'Pisanie komentarzy zostało wyłączone!');
		}

		if ($this->input->isPost())
		{
			$data['validator'] = array(

				'content'					=> array(
														array('string', false, 3)
											)
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				$comment->update(array('comment_content' => $this->post->content), "comment_id = $id");

				$this->redirect($this->page->getLocation() . '#comment-' . $result['comment_id']);
			}
		}

		$parents = &$this->getModel('page')->getParents($this->page->getId())->fetchAll();

		foreach ($parents as $row)
		{
			Breadcrumb::add(url($row['location_text']), $row['page_subject']);
		}
		Breadcrumb::add(url($this->page->getLocation()), $this->page->getSubject());
		Breadcrumb::add('', 'Edycja komentarza');

		return View::getView('commentEdit', $result);
	}

	public function delete($id = 0)
	{
		if (User::$id == User::ANONYMOUS)
		{
			throw new Error(500, 'Kasowanie komentarzy możliwe jest przez zalogowanych użytkowników!');
		}
		$id = (int)$id;
		$comment = &$this->getModel('comment');

		if (!$result = $comment->find($id)->fetchAssoc())
		{
			throw new Error(404, 'Komentarz o tym ID nie istnieje!');
		}

		$module = $this->module->getName($result['comment_module']);
		$enableDelete = $this->module->$module('commentEnableDelete', $result['comment_page']);

		if ($enableDelete == 0)
		{
			throw new Error(403, 'Nie masz uprawnień do wykonania tej operacji!');
		}
		else
		{
			if (User::$id != $result['comment_user'] && !Auth::get('c_delete'))
			{
				throw new Error(403, 'Nie masz uprawnień do wykonania tej operacji!');
			}
		}

		if (Box::confirm('Usuwanie komentarza', 'Czy na pewno chcesz usunąć ten komentarz?'))
		{
			Trigger::call('application.onCommentDelete', $id);

			$comment->delete("comment_id = $id");

			Trigger::call('application.onCommentDeleteComplete', $id);
		}

		$page = Page::load((int) $result['comment_page']);
		$this->redirect($page->getLocation());
	}
}
?>