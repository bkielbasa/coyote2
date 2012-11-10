<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Topic_Controller extends Page_Controller
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

			case 'download':

				$this->__download($this->get->id);

				exit;

			case 'submit':

				$this->__submit();

				exit;

			case 'edit':

				$this->__edit();

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

		$post = &$this->getModel('post');
		$pairs = $post->select('post_id, post_user')->where('post_user > ' . User::ANONYMOUS)->where('post_topic = ?', $this->page->getTopicId())->fetchPairs();

		$postsId = array_keys($pairs);
		$postUsers = array_values($pairs);

		$postUsers = array_merge($postUsers, $post->comment->select('comment_user')->where('comment_post IN(' . implode(',', $postsId) . ')')->fetchCol());

		$user = &$this->getModel('user');
		$result = $user->select('user_name, user_photo')->where('user_name LIKE "' . $userName . '%"');

		if ($postUsers)
		{
			$result = $result->order('user_id IN(' . implode(',', array_unique($postUsers)) . ') DESC');
		}

		$result = $result->order('user_lastvisit DESC')->limit(5)->fetchAll();
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

	private function __merge()
	{
		$postId = (int) $this->post->postId;
		$post = &$this->getModel('post');

		try
		{
			if (User::$id == User::ANONYMOUS)
			{
				throw new Exception('Brak dostępu dla niezalogowanych użytkowników');
			}

			$forum = &$this->getModel('forum');

			if (!$forum->getAuth('f_lock', $this->page->getForumId()))
			{
				throw new Exception('No access');
			}

			$result = $post->getPreviousPost($postId);
			if (!$result)
			{
				throw new Exception('Brak postu do połączenia');
			}
			$this->validateHash();

			$this->db->begin();

			$post->merge($postId, $result['post_id']);
			$this->db->commit();
			Log::add("Połączenie postu #$postId z $result[post_id]", Topic::I_MERGE, $this->page->getId());

			/*
			 * Usuniecie informacji o liczbie tematow w danych kategoriach forum (z cache)
			 */
			$this->cache->remove('sql_' . $this->module->getId('forum') . '*', Cache::PATTERN);
		}
		catch (Exception $e)
		{
			$this->db->rollback();

			$this->output->setStatusCode(500);
			echo $e->getMessage();
		}
	}

	private function __highlight()
	{
		// ladowanie zewnetrznej biblioteki - geshi
		Load::loadFile('lib/geshi/geshi.php');

		/* tworzenie klasy kolorowania skladnii */
		$geshi = new GeSHi('', '');
		/* tekst bedzie zawarty w	znaczniku <pre>	*/
		$geshi->set_header_type(GESHI_HEADER_PRE);

		/* ustaw kod zrodlowy	*/
		$geshi->set_source(htmlspecialchars_decode($this->post['content']));
		/* nadaj jezyk kolorowania skladnii */
		$geshi->set_language($this->post->language);

		echo $geshi->parse_code();
		exit;
	}

	private function __subject()
	{
		try
		{
			if (User::$id == User::ANONYMOUS)
			{
				throw new Exception('Brak dostępu dla niezalogowanych użytkowników');
			}

			$forum = &$this->getModel('forum');

			if (!$forum->getAuth('f_edit', $this->page->getForumId()))
			{
				throw new Exception('No access');
			}

			$this->validateHash();
			$subject = trim(htmlspecialchars($this->post['subject']));

			Load::loadFile('lib/validate.class.php', false);
			$validate = new Validate_String(false, 2, 100);

			if (!$validate->isValid($subject))
			{
				throw new Exception('Tytuł wątki musi mieścić się w granicach 2-100 znaków');
			}

			if ($subject != $this->page->getSubject())
			{
				$oldSubject = $this->page->getSubject();

				$this->page->setSubject($subject);
				$this->page->setPath($this->page->getId() . '-' . $subject);
				$this->page->save();

				$result = &$this->page->getTopicData();

				$post = &$this->getModel('post');
				$userId = $post->select('post_user')->where('post_id = ' . $result['topic_first_post_id'])->fetchField('post_user');

				if ($userId > User::ANONYMOUS && $userId != User::$id)
				{
					$notification = new Notify_Topic_Subject(array(

						'recipients'		=> $userId,

						'url'				=> url($this->page->getLocation()),
						'subject'			=> $this->page->getSubject(),
						'newSubject'		=> $subject
						)
					);

					$notify = new Notify($notification);
					$notify->trigger('application.onTopicSubjectEditComplete');
				}

				Log::add('Zmiana tytułu wątku z "' . $oldSubject . '" na "' . $subject . '"', Topic::I_SUBJECT, $this->page->getId());
				$post->solr->indexByTopic($this->page->getTopicId());
			}
		}
		catch (Exception $e)
		{
			$this->output->setStatusCode(500);
			echo $e->getMessage();
		}

		exit;
	}

	private function __status()
	{
		try
		{
			if (User::$id == User::ANONYMOUS)
			{
				throw new Exception('Brak dostępu dla niezalogowanych użytkowników');
			}

			$forum = &$this->getModel('forum');

			if (!$forum->getAuth('f_edit', $this->page->getForumId()))
			{
				throw new Exception('No access');
			}

			$this->validateHash();

			if ($this->post->status == 'announcement' && $forum->getAuth('f_announcement'))
			{
				$this->page->setIsAnnouncement(!$this->page->isAnnouncement());
				$this->page->save();

				Log::add('Poprzedni status: ' . (!$this->page->isAnnouncement() ? 'Ogłoszenie' : 'Normalny'), Topic::I_ANNOUNCEMENT, $this->page->getId());
			}
			elseif ($this->post->status == 'sticky' && $forum->getAuth('f_sticky'))
			{
				$this->page->setIsSticky(!$this->page->isSticky());
				$this->page->save();

				Log::add('Poprzednio: ' . (!$this->page->isSticky() ? 'Przyklejony' : 'Odklejony'), Topic::I_STICKY, $this->page->getId());
			}
		}
		catch (Exception $e)
		{
			$this->output->setStatusCode(500);
			echo $e->getMessage();
		}

		exit;
	}

	private function __vote()
	{
		$postId = (int) $this->post->postId;
		$post = &$this->getModel('post');

		try
		{
			if (User::$id == User::ANONYMOUS)
			{
				throw new Exception('Musisz się zalogować, aby oddać głos');
			}

			list($userId, $postIp) = $this->db->select('post_user, post_ip')->where("post_id = $postId")->from('post')->fetchArray();
			if (User::$id == $userId || ($postIp == User::$ip && current(explode('.', User::$ip)) != '10'))
			{
				throw new Exception('Nie możesz oddać głosu na swój post');
			}

			$vote = $this->post->value;
			if ($vote != Post_Vote_Model::UP && $vote != Post_Vote_Model::DOWN)
			{
				throw new Exception('Nieprawidłowa wartość');
			}

			$this->validateHash();
			echo $post->vote->update($postId, $vote);
			/*
			 * Usuniecie informacji o liczbie tematow w danych kategoriach forum (z cache)
			 */
			// po co to tutaj?
			//$this->cache->remove('sql_' . $this->module->getId('forum') . '*', Cache::PATTERN);
		}
		catch (Exception $e)
		{
			$this->output->setStatusCode(500);
			echo $e->getMessage();
		}
	}

	private function __solved()
	{
		$postId = (int) $this->post->postId;
		$post = &$this->getModel('post');

		try
		{
			if (User::$id == User::ANONYMOUS)
			{
				throw new Exception('Musisz się zalogować, aby oddać głos');
			}
			$result = $this->page->getTopicData();

			$forum = &$this->getModel('forum');
			$isEditable = $forum->getAuth('f_edit', $this->page->getForumId());

			$userId = $this->db->select('post_user')->where('post_id = ' . $result['topic_first_post_id'])->from('post')->fetchField('post_user');
			if ((User::$id != $userId) && !$isEditable)
			{
				throw new Exception('Możesz zaakceptować odpowiedź jedynie w swoim wątku');
			}

			$userId = $this->db->select('post_user')->where("post_id = $postId")->from('post')->fetchField('post_user');
//			if (User::$id == $userId)
//			{
//				throw new Exception('Nie możesz zaakceptować swojej odpowiedzi');
//			}
			$solved = null;

			if ($result['topic_solved'])
			{
				if ($postId == $result['topic_solved'])
				{
					$solved = null;
				}
				else
				{
					$solved = $postId;
				}
			}
			else
			{
				$solved = $postId;
				$url = $this->page->getLocation() . "?p=$postId#id$postId";

				$notificator = new Notify(new Notify_Topic_Solved(array('subject' => $this->page->getSubject(), 'recipients' => $userId, 'url' => $url)));
				$notificator->trigger('application.onTopicSolvedComplete');

				Trigger::call('application.onTopicSolvedComplete', $postId, $userId);
			}

			$topic = &$this->getModel('topic');
			$topic->update(array('topic_solved' => $solved), 'topic_id = ' . $this->page->getTopicId());

			$post->accept->delete('accept_topic = ' . $this->page->getTopicId());

			// zapisanie informacji o zaakceptowanym poscie, wraz z data
			if ($solved)
			{
				$post->accept->insert(array('accept_post' => $postId, 'accept_topic' => $this->page->getTopicId(), 'accept_user' => User::$id, 'accept_time' => time(), 'accept_ip' => User::$ip));
			}
		}
		catch (Exception $e)
		{
			$this->output->setStatusCode(500);
			echo $e->getMessage();
		}
	}

	private function __subscribe()
	{
		try
		{
			if (User::$id == User::ANONYMOUS)
			{
				throw new Exception('No access for anonymous users');
			}
			$this->validateHash();

			$post = &$this->getModel('post');
			$post->subscribe->toggle((int) $this->post->id, User::$id);
		}
		catch (Exception $e)
		{
			$this->output->setStatusCode(500);
			echo $e->getMessage();
		}
	}

	private function __comment()
	{
		try
		{
			if (User::$id == User::ANONYMOUS)
			{
				throw new Exception('No access for anonymous users');
			}

			$post = &$this->getModel('post');

			$forum = &$this->getModel('forum');
			$result = $forum->select('forum_lock')->where('forum_id = ?', $this->page->getForumId())->fetchAssoc();

			$isWriteable = (!$this->page->isLocked() && !$result['forum_lock'] && $forum->getAuth('f_write', $this->page->getForumId()));
			$isEditable = $forum->getAuth('f_edit', $this->page->getForumId());

			if (!$isWriteable && !$isEditable)
			{
				throw new Exception('Brak uprawnień do zarządzania komentarzami');
			}

			/*
			 * Jezeli w naglowku POST znajduje sie parametr "id", oznacza, ze zadanie
			 * zaklada usuniecie komentarza
			 *
			 * Jezeli w naglowku GET znajduje sie parametr "id", oznacza to, ze mamy do czynienia
			 * z edycja komentarza. Nalezy sprawdzic, czy uzytkownik ma do tego prawa
			 */
			if (isset($this->post->id) || isset($this->get->id))
			{
				$commentId = isset($this->post->id) ? (int) $this->post->id : (int) $this->get->id;

				$query = $this->db->select('comment_user, comment_post, comment_time, forum_id, user_name')
								  ->from('post_comment, post, forum')
								  ->innerJoin('user', 'user_id = comment_user')
								  ->where("comment_id = $commentId")
								  ->where('post_id = comment_post')
								  ->where('forum_id = post_forum');

				list($commentUser, $postId, $time, $forumId, $userName) = $query->fetchArray();

				if (!$forumId)
				{
					exit;
				}

				// w zaleznosci czy usuwamy komentarz, czy edytujemy - sprawdzamy
				// odpowiednie prawa
				$auth = isset($this->get->id) ? 'f_edit' : 'f_delete';

				if (User::$id != $commentUser
					&& !$forum->getAuth($auth, $forumId))
				{
					exit;
				}

				if (isset($this->post->id))
				{
					$this->db->delete('post_comment', "comment_id = $commentId");

					$post->solr->delete($postId);
					exit;
				}
				elseif (!isset($this->post->postId))
				{
					echo htmlspecialchars_decode($this->db->select('comment_text')->from('post_comment')->where("comment_id = $commentId")->fetchField('comment_text'));
					exit;
				}
			}

			$postId = (int) $this->post->postId;
			if (!$postId)
			{
				throw new Exception('No post ID');
			}

			$comment = trim($this->post['comment']);
			if (!$comment)
			{
				throw new Exception('Comment is empty');
			}

			Load::loadFile('lib/validate.class.php');
			$validator = new Validate_String(false, 1, 580);
			if (!$validator->isValid($comment))
			{
				throw new Exception('Długość komentarza musi mieścić się w zakresie 0-580 znaków');
			}

			$comment = htmlspecialchars($comment);
			$this->validateHash();

			$commentHtml = $comment;
			// parsowanie komentarza i zwrocenie ID uzytkownikow, ktorych loginy znajduja sie w parsowanym komentarzu
			$recipients = Forum::getLogins($commentHtml);

			// jezeli zmienna $commentId nie istnieje, dodajemy nowy komentarz, zamiast jego edycji
			if (!isset($commentId))
			{
				$userName = User::data('name');
				$commentUser = User::$id;

				$post->comment->insert(array(
					'comment_post'			=> $postId,
					'comment_time'			=> time(),
					'comment_text'			=> $comment,
					'comment_user'			=> User::$id
					)
				);
				$commentId = $this->db->nextId();

				Trigger::call('application.onPostCommentSubmit', $commentId, $postId, $comment);
				$postSubsribers = $this->getPostSubscribers($postId); // pobranie listy uzytkownikow, ktorzy dostana powiadomienie

				$notify = new Notify;
				$notify->addNotify(

					// dodanie nowego powiadomienia do kolejki: powiadomienie o nowym komentarzu
					new Notify_Post_Comment(array(

							'recipients'				=> $postSubsribers,
							'comment'					=> $comment,
							'subject'					=> $this->page->getSubject(),
							'url'						=> $this->page->getLocation() . '?p=' . $postId . '#comment-' . $commentId
						)
					)
				);

				// z listy z ID userow, usuwamy ID uzytkownika, ktory wlasnie dodal komentarz
				$index = array_search(User::$id, $recipients);
				if ($index !== false)
				{
					unset($recipients[$index]);
				}

				$recipients = array_diff($recipients, $postSubsribers);

				$notify->addNotify(

					// dodanie nowego powiadomienia do kolejki: powiadomienie o uzyciu @nick w komentarzu
					new Notify_Post_Comment_Login(array(

							'recipients'				=> $recipients,
							'comment'					=> $comment,
							'subject'					=> $this->page->getSubject(),
							'url'						=> $this->page->getLocation() . '?p=' . $postId . '#comment-' . $commentId
						)
					)
				);

				$notify->trigger('application.onPostCommentSubmitComplete');
				Trigger::call('application.onPostCommentSubmitComplete', $commentId, $postId, $comment);

				$time = time();
			}
			else
			{
				$post->comment->update(array('comment_text'	=> $comment), 'comment_id = ' . $commentId);
			}

			echo json_encode(array(
				'time'					=> User::formatDate($time, false, true),
				'date'					=> User::formatDate($time, false, false),
				'timestamp'				=> $time,
				'text'					=> $commentHtml,
				'user'					=> Html::a(url('@profile?id=' . $commentUser), $userName, array('class' => 'user-name', 'data-photo' => User::data('photo') ? Url::__('store/_a/' . User::data('photo')) : Url::__('template/img/avatar.jpg'), 'data-pm-url' => Url::__('@user?controller=Pm&action=Submit&user=' . User::$id), 'data-find-url' => Url::__(Path::connector('forum')) . '?view=user&user=' . User::$id . '#user')),
				'id'					=> $commentId,
				'isSubscribe'			=> $post->subscribe->isSubscribe($postId, User::$id)
				)
			);

			flush();
			$post->solr->index($postId);

		}
		catch (SQLQueryException $e)
		{
			Log::add('Błąd SQL: ' . $e->getMessage(), E_ERROR, $this->page->getId());

			$this->output->setStatusCode(500);
			echo 'Nie można dodać komentarza. Być może post został usunięty';
		}
		catch (Exception $e)
		{
			Log::add('Błąd podczas dodawania komentarza: ' . $e->getMessage(), E_ERROR, $this->page->getId());

			$this->output->setStatusCode(500);
			echo $e->getMessage();
		}
	}

	private function __watch()
	{
		if (User::$id == User::ANONYMOUS)
		{
			exit;
		}

		$watch = &$this->getModel('watch');
		echo (int) $watch->watch($this->page->getId(), $this->module->getId('forum'));
	}

	private function __lock()
	{
		try
		{
			if (User::$id == User::ANONYMOUS)
			{
				throw new Exception('No access for anonymous users');
			}
			$topic = &$this->getModel('topic');

			$topicId = (int) $this->post->topicId;
			$forum = &$this->getModel('forum');

			$result = $topic->select('topic_forum, topic_lock, topic_page')->where("topic_id = $topicId")->fetchAssoc();
			if (!$result)
			{
				throw new Exception('Invalid topic ID');
			}

			if (!$forum->getAuth('f_lock', $result['topic_forum']))
			{
				throw new Exception('No access');
			}

			$this->validateHash();
			$topic->update(array('topic_lock' => !$result['topic_lock']), 'topic_id = ' . $topicId);

			if (!$result['topic_lock'])
			{
				Log::add(null, Topic::I_LOCK, $result['topic_page']);
				echo json_encode(array('result' => true));
			}
			else
			{
				Log::add(null, Topic::I_UNLOCK, $result['topic_page']);
				echo json_encode(array('result' => false));
			}
		}
		catch (Exception $e)
		{
			echo json_encode(array('error' => $e->getMessage()));
		}
	}

	private function __move()
	{
		try
		{
			/**
			 * Anonim nie moze przeniesc zadnego tematu
			 */
			if (User::$id == User::ANONYMOUS)
			{
				throw new Exception('Błąd: brak dostępu');
			}
			$topicId = (int) $this->post->topicId;

			$topic = &$this->getModel('topic');
			$result = $this->page->getTopicData();

			if (!$result)
			{
				throw new Exception('Błąd: Brak tematu');
			}
			$fromId = $result['topic_forum'];
			$forum = &$this->getModel('forum');

			if (!$forum->getAuth('f_move', $fromId))
			{
				throw new Exception('Brak uprawnień do wykonania tej operacji');
			}

			$path = htmlspecialchars($this->post->path);
			if (!$path)
			{
				throw new Exception('Błąd: brak parametru "path"');
			}

			$this->validateHash();
			$query = $forum->select('forum_id, forum_page')->innerJoin('location', 'location_page = forum_page')
															->where('location_text = ?', $path);
			list($toId, $parentId) = $query->fetchArray();

			if (!$toId)
			{
				throw new Exception('Błąd: nieprawidłowa ścieżka forum');
			}
			if ($fromId == $toId)
			{
				throw new Exception('Temat już znajduje się w tej kategorii');
			}

			if ($this->post->reason)
			{
				$reason = $forum->reason->find($this->post->reason)->fetchAssoc();
			}
			$postUser = $this->db->select('post_user')->where("post_id = $result[topic_first_post_id]")->get('post')->fetchField('post_user');

			$this->db->begin();

			set_time_limit(0);
			$this->page->move($parentId);

			Log::add('Przeniesienie wątku do działu "' . $path . '".' . (isset($reason) ? ' Powód: ' . $reason['reason_name'] : ''), Topic::I_MOVE, $this->page->getId());
			$this->db->commit();

			if ($postUser > User::ANONYMOUS && $postUser != User::$id)
			{
				$forum = end(explode('/', $path));

				$notify = new Notify(

					new Notify_Topic_Move(array(

						'recipients'			=> $postUser,
						'url'					=> $this->page->getLocation(),
						'subject'				=> $this->page->getSubject(),
						'forum'					=> $forum,
						'reasonName'			=> @$reason['reason_name'],
						'reasonText'			=> @$reason['reason_content']
						)
					)
				);
				$notify->trigger('application.onTopicMoveComplete');
			}

			/*
			 * Usuniecie informacji o liczbie tematow w danych kategoriach forum (z cache)
			 */
			$this->cache->remove('sql_' . $this->module->getId('forum') . '*', Cache::PATTERN);
		}
		catch (Exception $e)
		{
			$this->db->rollback();
			Log::add($e->getMessage(), E_ERROR);

			$this->output->setStatusCode(500);
			echo $e->getMessage();

			exit;
		}

		$this->output->setContentType('text/plain');
		echo url($this->page->getLocation());
	}

	private function __delete()
	{
		/**
		 * Anonim nie moze usunac zadnego postu
		 */
		if (User::$id == User::ANONYMOUS)
		{
			exit;
		}
		try
		{
			$topicId = (int) $this->post->topicId;
			$postId = (int) $this->post->postId;

			$topic = &$this->getModel('topic');
			$result = $this->page->getTopicData();

			$query = $this->db->select('post_user')->where("post_id = $postId")->get('post');
			if (!count($query))
			{
				exit;
			}

			$forum = &$this->getModel('forum');
			$result = array_merge($result, $query->fetchAssoc());

			if (User::$id != $result['post_user']
				&& !$forum->getAuth('f_delete', $result['topic_forum']))
			{
				exit;
			}

			if (!$forum->getAuth('f_delete', $result['topic_forum'])
				&& $result['topic_replies'] > 0 && $result['topic_first_post_id'] == $postId)
			{
				exit;
			}

			/*
			 * Sprawdzenie, czy watek jest zablokowany. Jezeli tak - nie mozemy usuwac postow
			 * chyba ze mamy odpowiednie uprawnienia
			 *
			 * @todo Sprawdzac, czy cale forum nie jest zablokowane
			 */
			if ($this->page->isLocked()
				&& !$forum->getAuth('f_delete', $result['topic_forum']))
			{
				exit;
			}

			if ($this->post->reason)
			{
				$reason = $forum->reason->find($this->post->reason)->fetchAssoc();
			}
			set_time_limit(0);
			$this->validateHash();

			$notifyData = array(
				'recipients'			=> $result['post_user'],
				'subject'				=> $this->page->getSubject(),
				'reasonName'			=> @$reason['reason_name'],
				'reasonText'			=> @$reason['reason_content']
			);

			$post = &$this->getModel('post');

			if ($result['topic_first_post_id'] == $postId)
			{
				$this->db->begin();
				$this->page->delete();

				Log::add('Usunięcie tematu "' . $this->page->getSubject() . '"' . (isset($reason) ? ' Powód: ' . $reason['reason_name'] : ''), Topic::I_DELETE, $this->page->getId());
				$this->db->commit();

				if ($result['post_user'] > User::ANONYMOUS && $result['post_user'] != User::$id)
				{
					$notify = new Notify(new Notify_Topic_Delete($notifyData));
					$notify->trigger('application.onTopicDeleteComplete');
				}

				$query = $this->db->select('location_text')
							  ->from('forum, location')
							  ->where('forum_id = ?', $result['topic_forum'])
							  ->where('location_page = forum_page')
							  ->get();
				$path = $query->fetchField('location_text');

				$post->solr->deleteByTopic($result['topic_id']);
			}
			else
			{
				Trigger::call('application.onPostDelete', $postId);

				$this->db->begin();
				$post->delete($postId);

				Log::add("Usunięcie postu #$postId." . (isset($reason) ? ' Powód: ' . $reason['reason_name'] : ''), Topic::I_DELETE, $this->page->getId());
				$this->db->commit();

				if ($result['post_user'] > User::ANONYMOUS && $result['post_user'] != User::$id)
				{
					$notify = new Notify(new Notify_Post_Delete($notifyData));
					$notify->trigger('application.onPostDeleteComplete');
				}

				Trigger::call('application.onPostDeleteComplete', $postId);
				$path = $this->page->getLocation();

				// nadpisanie zmiennej $postId - pobieramy ID postu do ktorego nastapi przekierowanie
				$postId = $post->select('post_id')->where('post_topic = ' . $topicId)->where('post_id < ' . $postId)->order('post_id DESC')->limit(1)->fetchField('post_id');
				$path .= '?p=' . $postId . '#id' . $postId;
			}
			/*
			 * Usuniecie informacji o liczbie tematow w danych kategoriach forum (z cache)
			 */
			$this->cache->remove('sql_' . $this->module->getId('forum') . '*', Cache::PATTERN);
		}
		catch (Exception $e)
		{
			$this->db->rollback();

			$this->output->setStatusCode(500);
			echo $e->getMessage();

			exit;
		}

		echo url($path);
	}

	private function __preview()
	{
		$forum = &$this->getModel('forum');
		$content = $this->post->value('content');

		$enableSmilies = $this->post->enableSmilies && User::data('allow_smilies');
		$enableHtml = $this->post->enableHtml && $forum->getAuth('f_html', $this->page->getForumId());

		Forum::loadParsers($enableHtml, $enableSmilies, $this->post->attachment);

		$post = &$this->getModel('post');
		// pobranie informacji o cytowanych postach i przekazanie do parsera
		$this->parser->setOption('quote.postId', $post->getQuotedPost($content));

		echo $this->transform($content);
		exit;
	}

	private function __submit()
	{
		$topicId = (int) $this->page->getTopicId();
		$topic = &$this->getModel('topic');

		try
		{
			$result = $this->page->getTopicData();

			$forum = &$this->getModel('forum');
			$result = array_merge($result, $forum->find($this->page->getForumId())->fetchAssoc());

			$isWriteable = (!$result['forum_lock'] && $forum->getAuth('f_write', $result['forum_id']));

			if (!$isWriteable)
			{
				throw new Exception('Brak uprawnień do pisania w tej kategorii!');
			}

			/*
			 * Sprawdzenie, czy watek jest zablokowany. Jezeli tak - tylko moderatorzy
			 * moga w nim odpowiadac
			 */
			if ($this->page->isLocked() && !$forum->getAuth('f_edit', $result['forum_id']))
			{
				throw new Exception('Temat jest zablokowany!');
			}

			$form = new Form_Submit(url($this->page->getLocation()) . '?' . $this->input->server('QUERY_STRING'), Forms::POST);
			$form->id = 'reply-form';

			$form->setEnableAntiSpam(User::$id == User::ANONYMOUS);

			$form->setEnableSticky(false);
			$form->setEnableAnnouncement(false);
			$form->setEnableAnonymous(User::$id == User::ANONYMOUS);
			$form->setEnableTags(false);
			$form->setEnableSubject(false);
			$form->setEnableSmilies((bool) User::data('allow_smilies')); // checkbox: zaznaczony lub nie w zaleznosci od ustawien w profilu
			$form->addHtml(new View('_partialAttachment'));
			$form->addHtml(new View('_partialPreview'));

			if ($forum->getAuth('f_html', $result['forum_id']))
			{
				$form->setEnableHtml(true);
			}

			$session = &Load::loadClass('session');
			if (!isset($session->forumHash))
			{
				$session->forumHash = md5(uniqid(rand(), true));
			}
			$form->setHash($session->forumHash);

			$watch = &$this->getModel('watch');

			if (User::$id == User::ANONYMOUS)
			{
				$form->setEnableWatch(false);
			}
			else
			{
				$isWatched = $watch->isWatched($result['topic_page'], $this->module->getId('forum'));

				/*
				 * Jezeli uzytkownik ma ustawiona opcje w profilu:
				 * "automatycznie obseruj tematy, w ktorych biore udzial"
				 * nalezy sprawdzic, czy user mimo, ze bral juz udzial w temacie --
				 * oznaczyl temat jako NIEobserwowany.
				 */
				if (!$isWatched && User::data('allow_notify'))
				{
					$post = &$this->getModel('post');
					$hasUserPost = $post->hasUserPost($topicId);

					if (!$hasUserPost)
					{
						$isWatched = true;
					}
				}
				$form->setIsWatch($isWatched);
				unset($isWatched);
			}

			if ($this->get->postId)
			{
				$query = $this->db->select('text_content AS post_text')
								  ->from('post')
								  ->innerJoin('post_text', 'text_id = post_text')
								  ->where('post_id = ?', $this->get->postId)
								  ->where('post_topic = ?', $topicId); // <-- ten warunek jest tutaj dla bezpieczenstwa (nie usuwac!)

				$postText = $query->fetchField('post_text');
				$postText = "<quote=\"" . $this->get->postId . "\">$postText</quote>";

				$form->id = 'quote-' . $this->get->postId;

				$form->renderForm();
				$form->setDefault('content', $postText);
			}

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
				$this->validateHash(); // tymczasowe wylaczenie (nieprawidlowe dzialanie dla niektorych userow)

				$post = &$this->getModel('post');
				if ($post->isFlood())
				{
					throw new Exception('Musisz odczekać 10 sekund przed napisaniem kolejnego posta!');
				}
				$this->db->begin();

				$postId = $post->submit($result['topic_forum'], $topicId, $content, $form->getValue('username'), $form->getValue('enableSmilies'), $form->getValue('enableHtml') && $form->getEnableHtml());

				if (User::$id > User::ANONYMOUS)
				{
					$isWatch = $watch->isWatched($result['topic_page'], $this->module->getId('forum'));
					$flag = (isset($this->post->watch) && !$isWatch) || (!isset($this->post->watch) && $isWatch);

					if ($flag)
					{
						$watch->watch($result['topic_page'], $this->module->getId('forum'));
					}
				}

				if ($this->post->attachment)
				{
					$attachmentDelete = $attachmentInsert = array();

					foreach ($this->post->attachment as $key => $value)
					{
						if ($value == 'delete')
						{
							$attachmentDelete[] = $key;
						}
						else
						{
							$attachmentInsert[$key] = $value;
						}
					}

					if ($attachmentInsert)
					{
						$post->attachment->insert($postId, $attachmentInsert);
					}
					if ($attachmentDelete)
					{
						$post->attachment->delete($postId, $attachmentDelete);
					}
				}
				Log::add("Post #$postId" . (User::$id == User::ANONYMOUS ? ' (' . $form->getValue('username') . ')' : ''), Topic::I_REPLY, $this->page->getId());
				$this->db->commit();
				/*
				 * Chociaz de facto nie uaktualniamy tutaj rekordu w tabeli page,
				 * uruchamiamy trigger, poniewaz zawartosc strony sie zmienila (zostal dodany nowy post)
				 */
				UserErrorException::__(Trigger::call('application.onPageSubmitComplete', array(&$this->page)));

				/*
				 * Usuniecie informacji o liczbie tematow w danych kategoriach forum (z cache)
				 */
				$this->cache->remove('sql_' . $this->module->getId('forum') . '*', Cache::PATTERN);

				$notifyData = array(

					'postId'			=> $postId,
					'subject'			=> $this->page->getSubject(),
					'content'			=> $content,
					'userName'			=> $form->getValue('username'),
					'enableSmilies'		=> $form->getValue('enableSmilies'),
					'url'               => $this->page->getLocation() . '?p=' . $postId . '#id' . $postId,
					'recipients'        => $watch->getUsers($result['topic_page'], $this->module->getId('forum')),
					'isAttachments'     => isset($attachmentInsert)
				);

				$notify = new Notify;
				$notify->addNotify(new Notify_Post_Submit($notifyData));

				// parsowanie komentarza i zwrocenie ID uzytkownikow, ktorych loginy znajduja sie w parsowanym komentarzu
				$recipients = Forum::getLogins($content);

				// z listy z ID userow, usuwamy ID uzytkownika, ktory wlasnie dodal komentarz
				$index = array_search(User::$id, $recipients);
				if ($index !== false)
				{
					unset($recipients[$index]);
				}

				$notifyData['recipients'] = array_diff($recipients, $notifyData['recipients']);
				$notify->addNotify(new Notify_Post_Login($notifyData));

				/*
				 * Wyslanie powiadomien do userow
				 */
				$notify->trigger();

				$url = url($this->page->getLocation() . '?p=' . $postId) . '#id' . $postId;
				if (!$this->input->isAjax())
				{
					$this->redirect($url);
				}
				else
				{
					$this->output->setContentType('text/plain');
					echo url($url);
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
			$this->db->rollback();
			Log::add('Błąd SQL: ' . $e->getMessage(), E_ERROR, $this->page->getId());

			$this->setError('Nie można dodać postu. Być może wątek został usunięty');

			exit;
		}
		catch (Exception $e)
		{
			$this->db->rollback();
			Log::add('Błąd podczas pisania odpowiedzi: ' . $e->getMessage(), E_ERROR, $this->page->getId());

			$this->setError($e->getMessage());

			exit;
		}

		foreach ($this->getParents() as $row)
		{
			Breadcrumb::add(url($row['location_text']), $row['page_subject']);
		}
		Breadcrumb::add(url($this->page->getLocation()), $this->page->getSubject());
		Breadcrumb::add(url($this->page->getLocation()) . '?mode=submit', 'Odpowiedź');

		$view = new View('topicSubmit', array(
			'form'			=> $form,
			'hash'			=> $session->forumHash
			)
		);

		if ($this->page->getMetaTitle())
		{
			$this->output->setTitle(sprintf('Odpowiadanie w temacie %s :: %s', $this->page->getMetaTitle(), Config::getItem('site.title')));
		}
		else
		{
			$this->output->setTitle(sprintf('Odpowiadanie w temacie %s :: %s', ($this->page->getTitle() ? $this->page->getTitle() : $this->page->getSubject()), Config::getItem('site.title')));
		}

		echo $view;
	}

	private function __fastedit()
	{
		try
		{
			if (User::$id == User::ANONYMOUS)
			{
				throw new Exception('Błąd: Tylko zalogowani użytkownicy mogą edytować posty');
			}

			$postId = (int) $this->get->postId;
			$post = &$this->getModel('post');

			/*
			 * Dodatkowy warunek post_topic ma upewnic sie ze post nalezy do tego tematu
			 */
			$result = $post->getPost($postId, $this->page->getTopicId())->fetchAssoc();

			if (!$result)
			{
				throw new Exception('Błąd: Post o podanym ID nie istnieje');
			}

			$forum = &$this->getModel('forum');
			$result = array_merge($result, $forum->find($result['post_forum'])->fetchAssoc());

			$isWriteable = (!$result['forum_lock'] && !$this->page->isLocked() && $forum->getAuth('f_write', $this->page->getForumId()));
			$isEditable = $forum->getAuth('f_edit', $result['forum_id']);

			if (!$isWriteable && !$isEditable)
			{
				throw new Exception('Błąd: Brak uprawnień do edycji postu');
			}

			if (User::$id != $result['post_user']
				&& !$isEditable)
			{
				throw new Exception('Błąd: Brak uprawnień do edycji postu');
			}

			if (!$isEditable
				&& ($this->page->getReplies() > 0 && $this->page->getFirstPostId() == $postId))
			{
				throw new Exception('Błąd: Nie możesz edytować postu, ponieważ ten wątek posiada już odpowiedzi');
			}

			if ($this->input->isPost())
			{
				Load::loadFile('lib/validate.class.php');
				Load::loadHelper('array');

				$content = trim($this->post->value('content'));

				$validator = new Validate_NotEmpty;
				if ($validator->isValid($content))
				{
					if (md5($content) != md5($result['post_text']))
					{
						$post->update($postId, $content, $result['post_username'], $result['post_enable_smilies'], $result['post_enable_html'], ++$result['post_edit_count']);

						/*
						 * Tablica zawiera ID userow, ktorzy dostana powiadomienie o tym, ze ten post zostal zmodyfikowany
						 */
						$recipients = $this->getPostSubscribers($postId);
						if ($result['post_user'] > User::ANONYMOUS && $result['post_user'] != User::$id)
						{
							$recipients[] = $result['post_user'];
						}

						$recipients = array_unique($recipients); // usuwamy duplikaty (na wypadek, gdyby autor postu rowniez obserwowal ow post - nie powinien dostac 2 powiadomien)

						if ($recipients)
						{
							$notification = new Notify_Post_Edit(array(

									'recipients'		=> $recipients,

									'postId'			=> $postId,
									'url'				=> url($this->page->getLocation()) . '?p=' . $postId . '#id' . $postId,
									'subject'			=> $this->page->getSubject(),
									'newSubject'		=> $this->page->getSubject(),
									'oldContent'		=> $result['post_text'],
									'newContent'		=> $content,
									'enableDiff'		=> true
								)
							);
						}
					}

					if ($result['post_user'] > User::ANONYMOUS && User::data('allow_sig'))
					{
						$user = &$this->getModel('user');
						$sig = $user->select('user_sig')->where('user_id = ?', $result['post_user'])->fetchField('user_sig');

						// znak nowej linii jest tutaj umyslnie. chodzi o parsowanie tabeli
						// ktora znajduje sie na koncu postu (pomiedzy stopka z tabelka nie ma znaku nowej linii)
						$content .= $sig ? (" \n<hr />" . $sig) : '';
					}

					$attachments = $post->attachment->getAttachments($postId);
					$this->attachment = array();

					if (isset($attachments[$postId]))
					{
						foreach ($attachments[$postId] as $row)
						{
							$this->attachment[$row['attachment_file']] = $row['attachment_name'];
						}
					}

					Forum::loadParsers($result['post_enable_html'], $result['post_enable_smilies']);

					$this->parser->setOption('footnotes.prefix', $result['post_id']);
					// pobranie informacji o cytowanych postach i przekazanie do parsera
					$this->parser->setOption('quote.postId', $post->getQuotedPost($content));

					echo json_encode(array('content' => $this->transform($content)));
					flush();

					if (isset($notification))
					{
						$notify = new Notify($notification);
						$notify->trigger('application.onPostEditComplete');
					}
				}
				else
				{
					echo json_encode(array(
						'error'			=> element($validator->getErrors(), 0)
						)
					);
				}

				exit;
			}

			echo $result['post_text'];
		}
		catch (SQLQueryException $e)
		{
			Log::add('Błąd SQL: ' . $e->getMessage(), E_ERROR, $this->page->getId());

			$this->output->setStatusCode(500);
			echo 'Nie można edytować postu. Być może został usunięty. Jeżeli błąd będzie się powtarzał, skontaktuj się z administratorem';
		}
		catch (Exception $e)
		{
			Log::add('Błąd podczas edycji postu: ' . $e->getMessage(), E_ERROR, $this->page->getId());

			$this->output->setStatusCode(500);
			echo $e->getMessage();
		}

		exit;
	}

	private function __edit()
	{
		try
		{
			if (User::$id == User::ANONYMOUS)
			{
				throw new Exception('Błąd: Tylko zalogowani użytkownicy mogą edytować posty');
			}
			$postId = (int) $this->get->postId;
			$post = &$this->getModel('post');

			/*
			 * Dodatkowy warunek post_topic ma upewnic sie ze post nalezy do tego tematu
			 */
			$result = $post->getPost($postId, $this->page->getTopicId())->fetchAssoc();

			if (!$result)
			{
				throw new Exception('Błąd: Post o podanym ID nie istnieje');
			}
			$forum = &$this->getModel('forum');
			$result = array_merge($result, $forum->find($result['post_forum'])->fetchAssoc());

			$topic = &$this->getModel('topic');

			$query = $topic->select()->innerJoin('page', 'page_id = topic_page')->where('topic_id = ?', $result['post_topic']);
			$result = array_merge($result, $query->fetchAssoc());

			$isWriteable = (!$result['forum_lock'] && !$result['topic_lock'] && $forum->getAuth('f_write', $result['forum_id']));
			$isEditable = $forum->getAuth('f_edit', $result['forum_id']);

			if (!$isWriteable && !$isEditable)
			{
				throw new Exception('Błąd: Brak uprawnień do edycji postu');
			}

			if (User::$id != $result['post_user']
				&& !$isEditable)
			{
				throw new Exception('Błąd: Brak uprawnień do edycji postu');
			}

			if (!$isEditable
				&& ($result['topic_replies'] > 0 && $result['topic_first_post_id'] == $postId))
			{
				throw new Exception('Błąd: Nie możesz edytować postu, ponieważ ten wątek posiada już odpowiedzi');
			}

			$form = new Form_Submit(url($this->page->getLocation()) . '?' . $this->input->server('QUERY_STRING'), Forms::POST);
			$form->id = "edit-$postId";

			$form->setEnableAntispam(false);

			$isTopicFirstPost = $result['post_id'] == $result['topic_first_post_id'];

			$attachments = $post->attachment->getAttachments($postId);
			$form->addHtml(new View('_partialAttachment', array('attachment' => @$attachments[$postId])));

			if (!$isTopicFirstPost)
			{
				$form->setEnableSticky(false);
				$form->setEnableAnnouncement(false);
				$form->setEnableTags(false);
				$form->setEnableSubject(false);
			}
			else
			{
				$form->setEnableTags($forum->getAuth('f_tag', $result['topic_forum']));
				$form->setEnableSubject(true);
				$form->setEnableSticky($forum->getAuth('f_sticky', $result['post_forum']));
				$form->setEnableAnnouncement($forum->getAuth('f_announcement', $result['post_forum']));

				if ($this->module->isPluginEnabled('poll'))
				{
					$poll = new Form_Poll;

					if ($this->page->getPollId())
					{
						$poll->setEnableDelete(true);
						$poll->renderForm();

						$result = array_merge($result, $this->getModel('poll')->get($this->page->getPollId()));

						$poll->setDefaults(array(
							'title'		=> $result['poll_title'],
							'items'		=> implode("\n", $result['items']),
							'max'		=> $result['poll_max_item'],
							'length'	=> $result['poll_length'] / Time::DAY
							)
						);
					}
					else
					{
						$poll->renderForm();
					}

					$form->addHtml(new View('_partialPollForm', array('form' => $poll)));
				}
			}

			$form->addHtml(new View('_partialPreview'));
			$form->setEnableAnonymous($result['post_user'] == User::ANONYMOUS);
			$form->setEnableSmilies((bool) $result['post_enable_smilies']);
			$form->setEnableHtml((bool) $forum->getAuth('f_html', $result['forum_id']));

			$watch = &$this->getModel('watch');

			if (User::$id == User::ANONYMOUS)
			{
				$form->setEnableWatch(false);
			}
			else
			{
				$form->setIsWatch($watch->isWatched($result['topic_page']));
			}

			$form->setIsAnnouncement((bool) $result['topic_announcement']);
			$form->setIsSticky((bool) $result['topic_sticky']);
			$form->setIsHtml((bool) $result['post_enable_html']);

			$session = &Load::loadClass('session');
			if (!isset($session->forumHash))
			{
				$session->forumHash = md5(uniqid(rand(), true));
			}

			$form->setHash($session->forumHash);

			$form->renderForm();
			$form->setDefaults(array(
				'content'				=> htmlspecialchars($result['post_text']),
				'subject'				=> $result['page_subject'],
				'username'				=> $result['post_username']
				)
			);

			if ($isTopicFirstPost)
			{
				$tag = &$this->getModel('tag');
				$form->setDefault('tag', implode(', ', (array) array_pop($tag->getTags($this->page->getId()))));
			}

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
				$this->validateHash();
				$post->update($postId, $content, (string) $form->getValue('username'), (bool) $form->getValue('enableSmilies'), (bool) $form->getValue('enableHtml') && $form->getEnableHtml(), ++$result['post_edit_count'], md5($content) != md5($result['post_text']));

				/*
				 * Tablica zawiera ID userow, ktorzy dostana powiadomienie o tym, ze ten post zostal zmodyfikowany
				 */
				$recipients = $this->getPostSubscribers($postId);
				if ($result['post_user'] > User::ANONYMOUS && $result['post_user'] != User::$id)
				{
					$recipients[] = $result['post_user'];
				}

				$recipients = array_unique($recipients); // usuwamy duplikaty (na wypadek, gdyby autor postu rowniez obserwowal ow post - nie powinien dostac 2 powiadomien)

				if ($recipients)
				{
					$notification = new Notify_Post_Edit(array(

							'recipients'		=> $recipients,

							'postId'			=> $postId,
							'url'				=> url($this->page->getLocation()) . '?p=' . $postId . '#id' . $postId,
							'subject'			=> $this->page->getSubject(),
							'newSubject'		=> $form->getValue('subject'),
							'oldContent'		=> $result['post_text'],
							'newContent'		=> $content,
							'enableDiff'		=> true
						)
					);
				}

				if ($isTopicFirstPost)
				{
					$pollId = $this->page->getPollId();

					if (isset($poll))
					{
						$poll->isValid();

						if (isset($this->post->delete))
						{
							$this->getModel('poll')->delete('poll_id = ' . $this->page->getPollId());
							$pollId = 0;
						}
						elseif ($poll->getValue('items'))
						{
							$items = explode("\n", $poll->getValue('items'));

							$pollId = $this->getModel('poll')->submit($this->page->getPollId(),
																	 (string) $poll->getValue('title'),
																	 (int) time(),
																	 (int) $poll->getValue('length'),
																	 (int) $poll->getValue('max'),
																	 true,
																	 $items
																	 );
						}
					}

					$topic->update(array('topic_announcement' => (bool) $form->getValue('announcement'), 'topic_sticky' => (bool) $form->getValue('sticky'), 'topic_poll' => $pollId), 'topic_id = ' . $result['post_topic']);

					$tag = &$this->getModel('tag');
					$tag->insert($this->page->getId(), $form->getValue('tag'));

					if ($this->page->getSubject() != $form->getValue('subject'))
					{
						$this->page->setSubject($form->getValue('subject'));
						$this->page->setPath($this->page->getId() . '-' . $form->getValue('subject'));
						$this->page->save();

						$post->solr->indexByTopic($this->page->getTopicId());
					}
				}

				if (User::$id > User::ANONYMOUS)
				{
					$isWatch = $watch->isWatched($result['topic_page'], $this->module->getId('forum'));
					$flag = (isset($this->post->watch) && !$isWatch) || (!isset($this->post->watch) && $isWatch);

					if ($flag)
					{
						$watch->watch($result['topic_page'], $this->module->getId('forum'));
					}
				}
				if ($this->post->attachment)
				{
					$attachmentDelete = $attachmentInsert = array();

					foreach ($this->post->attachment as $key => $value)
					{
						if ($value == 'delete')
						{
							$attachmentDelete[] = $key;
						}
						else
						{
							$attachmentInsert[$key] = $value;
						}
					}

					if ($attachmentInsert)
					{
						$post->attachment->insert($postId, $attachmentInsert);
					}
					if ($attachmentDelete)
					{
						$post->attachment->delete($postId, $attachmentDelete);
					}
				}

				Log::add("Edycja postu #{$postId}", Topic::I_EDIT, $this->page->getId());

				if (isset($notification))
				{
					$notify = new Notify($notification);
					$notify->trigger('application.onPostEditComplete');
				}

				/*
				 * Chociaz de facto nie uaktualniamy tutaj rekordu w tabeli page,
				 * uruchamiamy trigger, poniewaz zawartosc strony sie zmienila (post zostal zmieniony)
				 */
				UserErrorException::__(Trigger::call('application.onPageSubmitComplete', array(&$this->page)));

				/*
				 * Usuniecie informacji o liczbie tematow w danych kategoriach forum (z cache)
				 */
				$this->cache->remove('sql_' . $this->module->getId('forum') . '*', Cache::PATTERN);
				$url = url($this->page->getLocation() . '?p=' . $postId) . '#id' . $postId;

				if (!$this->input->isAjax())
				{
					$this->redirect($url);
				}
				else
				{
					$this->output->setContentType('text/plain');
					echo $url;
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
			$this->setError('Nie można edytować postu. Być może został usunięty');

			exit;
		}
		catch (Exception $e)
		{
			Log::add('Błąd podczas edycji postu: ' . $e->getMessage(), E_ERROR, $this->page->getId());
			$this->setError($e->getMessage());

			exit;
		}

		foreach ($this->getParents() as $row)
		{
			Breadcrumb::add(url($row['location_text']), $row['page_subject']);
		}
		Breadcrumb::add(url($this->page->getLocation()), $this->page->getSubject());
		Breadcrumb::add(url($this->page->getLocation()) . '?mode=edit&postId=' . $postId, 'Edycja');

		$view = new View('topicEdit', array(
			'form'			=> $form,
			'hash'			=> $session->forumHash
			)
		);

		if ($this->page->getMetaTitle())
		{
			$this->output->setTitle(sprintf('Edycja postu w temacie %s :: %s', $this->page->getMetaTitle(), Config::getItem('site.title')));
		}
		else
		{
			$this->output->setTitle(sprintf('Edycja postu w temacie %s :: %s', ($this->page->getTitle() ? $this->page->getTitle() : $this->page->getSubject()), Config::getItem('site.title')));
		}

		echo $view;
	}

	private function getAtom()
	{
		if (!$this->page->isAllowed())
		{
			exit;
		}
		$topic = &$this->getModel('topic');
		$result = array();

		$result = $this->page->getTopicData();
		$id = &$result['topic_id'];

		$post = &$this->getModel('post');

		$atom = new Feed_Atom;
		$atom->setTitle($this->page->getTitle() ? $this->page->getTitle() : $this->page->getSubject());
		$atom->setLink(url($this->page->getLocation()));
		$atom->setId(Feed_Atom::getUuid('urn:uuid:', $this->page->getId()));

		Forum::loadParsers();
		$this->parser->setOption('wiki.attachmentDir', 'store/forum/');
		$this->parser->setOption('wiki.attachmentUrl', $this->page->getLocation() . '?mode=download&id=');

		$query = $post->fetch("post_topic = $id", 'post_id ASC')->get();
		$postList = array();

		foreach ($query as $row)
		{
			$postList[$row['post_id']] = $row;
		}
		$attachments = $post->attachment->getAttachments(array_keys($postList));

		foreach ($postList as $postId => $row)
		{
			$this->parser->setOption('smilies.disable', !$row['post_enable_smilies'] || !User::data('allow_smilies'));

			if ($row['user_sig'])
			{
				$row['post_text'] .= "\n\n----\n" . $row['user_sig'];
			}
			$wikiAttachment = array();

			if (isset($attachments[$postId]))
			{
				foreach ($attachments[$postId] as $item)
				{
					$wikiAttachment[Text::toLower($item['attachment_name'])] = $item;
				}
			}
			$this->parser->setOption('wiki.attachment', $wikiAttachment);
			$row['post_text'] = $this->transform($row['post_text']);

			$element = $atom->createElement();

			if ($row['post_id'] == $result['topic_first_post_id'])
			{
				$element->setTitle('Treść pytania: ' . $this->page->getSubject());
			}
			else
			{
				$element->setTitle('RE: ' . $this->page->getSubject());
			}
			$element->setLink(url($this->page->getLocation()) . "?p=$row[post_id]#$postId");
			$element->setId(Feed_Atom::getUuid('urn:uuid:', $postId));
			$element->setUpdated(max($row['post_time'], $row['post_edit_time']));
			$element->setContent($row['post_text']);
			$element->setAuthor($row['post_user'] > User::ANONYMOUS ? $row['user_name'] : $row['post_username']);
		}

		$this->output->setHttpHeader('Last-Modified', date(DATE_RFC1123, $atom->getUpdated()));
		return $atom;
	}

	public function view()
	{
		$topic = &$this->getModel('topic');
		$result = array();

		/**
		 * @todo Usunac metode getTopicData() z lacznika. Mozemy poslugiwac sie
		 * metodami z lacznika w celu odczytania informacji o temacie
		 */
		$result = $this->page->getTopicData();
		$id = (int) ($id = &$result['topic_id']);

		$post = &$this->getModel('post');

		if ($this->get->view == 'unread')
		{
			$forum = &$this->getModel('forum');

			/*
			 * Pobranie daty ostatniego "czytania" tematu (moze byc NULL)
			 */
			$topicMarkTime = $topic->isUnread($id);
			/*
			 * Pobranie daty ostatniego przeczytania forum (moze byc NULL)
			 */
			$forumMarkTime = $forum->isUnread($result['topic_forum']);

			/*
			 * Jezeli data przeczytania tematu LUB data przeczytania forum (calego)
			 * jest mniejsza niz data napisania ostatniego postu...
			 */
			if ($topicMarkTime < $result['topic_last_post_time']
				&& $forumMarkTime < $result['topic_last_post_time'])

			{
				$markTime = max($topicMarkTime, $forumMarkTime);

				$query = $post->select('post_id')->where("post_topic = $id AND post_time > $markTime")->limit(1);
				$postId = $query->fetchField('post_id');

				if ($postId != $result['topic_first_post_id'])
				{
					$this->redirect($this->page->getLocation() . "?p=$postId#id{$postId}");
				}
			}
		}
		elseif ($this->get->view == 'last')
		{
			$this->redirect($this->page->getLocation() . "?p=$result[topic_last_post_id]#id$result[topic_last_post_id]");
		}

		$start = max(0, (int) $this->get['start']);
		$forum = &$this->getModel('forum');

		if (isset($this->get->page))
		{
			$forum->setting->setPostsPerPage((int) $this->get->page);
			$forum->setting->save();

			$this->perPage = (int) $this->get->page;
		}
		else
		{
			if (!$this->perPage = (int) $forum->setting->getPostsPerPage())
			{
				$this->perPage = 10;
			}
		};
		/**
		 * Walidacja liczby: ilosc postow na strone
		 */
		$this->perPage = max(10, min($this->perPage, 50));
		if (!isset($postId))
		{
			$postId = (int) $this->get->p;
		}

		if ($postId && !isset($this->get->start))
		{
			$start = $post->getPage($result['topic_id'], $postId, $this->perPage);
			$this->postId = $postId;
		}

		$this->postList = array();

		if (isset($this->get->sort))
		{
			$forum->setting->setPostSort($this->get->sort);
			$forum->setting->save();

			$this->sort = $this->get->sort;
		}
		else
		{
			if (!$this->sort = $forum->setting->getPostSort())
			{
				$this->sort = 'oldest';
			}
		}

		$result = array_merge($result, $forum->find($result['topic_forum'])->fetchAssoc());

		$this->isWriteable = (!$result['topic_lock'] && !$result['forum_lock'] && $forum->getAuth('f_write', $result['forum_id']));
		$this->isEditable = $forum->getAuth('f_edit', $result['forum_id']);
		$this->isRemovable = $forum->getAuth('f_delete', $result['forum_id']);
		$this->isLockable = $forum->getAuth('f_lock', $result['forum_id']);
		$this->isMoveable = $forum->getAuth('f_move', $result['forum_id']);
		$this->isMergeable = $forum->getAuth('f_merge', $result['forum_id']);
		$this->isVoteable = $forum->getAuth('f_vote', $result['forum_id']);
		$this->isStickable = $forum->getAuth('f_sticky', $result['forum_id']);
		$this->isAnnounceable = $forum->getAuth('f_announcement', $result['forum_id']);

		if (!$this->isVoteable)
		{
			$this->sort = 'oldest';
		}

		$postList = $post->getTopicPosts($result['topic_id'], $result['topic_first_post_id'], ($this->sort == 'oldest' ? 'post_id' : ($result['topic_solved'] ? "post.post_id = $result[topic_solved] DESC, post_vote DESC" : 'post_vote DESC')), $start, $this->perPage);

		$this->pagination = new Pagination($this->page->getLocation(), $result['topic_replies'], $this->perPage, $start);
		$this->pagination->setEnableDefaultQueryString(false);

		$postTime = array();
		$postIds = array();
		$userIds = array();
		/*
		 * Tablica zawierajaca ID cytowanych postow
		 */
		$quoteIds = array();

		foreach ($postList as $row)
		{
			$postIds[] = $row['post_id'];

			if ($row['post_user'] > User::ANONYMOUS)
			{
				$userIds[] = $row['post_user'];
			}

			// sprawdzenie, czy w poscie znajduje sie cytat z innego postu
			if (preg_match_all("#<quote=\"(\d+)\"\>#is", $row['post_text'], $matches))
			{
				for ($i = 0; $i < count($matches[0]); $i++)
				{
					$quoteIds[] = (int) $matches[1][$i];
				}
			}
		}

		// pobranie zalacznikow do wszystkich postow w watku wyswietlanych na danej stronie
		$attachments = $post->attachment->getAttachments($postIds);
		unset($postIds);

		$this->onlineUsers = array();

		if ($userIds)
		{
			$this->onlineUsers = $this->db->select('session_user_id, session_stop')->from('session')->in('session_user_id', array_unique($userIds))->fetchPairs();
		}

		Forum::loadParsers(false, true);
		$this->parser->setOption('wiki.attachmentDir', 'store/forum/');
		$this->parser->setOption('wiki.attachmentUrl', $this->page->getLocation() . '?mode=download&id=');

		if ($quoteIds)
		{
			// pobranie informacji o cytowanych postach i przekazanie do parsera
			$this->parser->setOption('quote.postId', $post->getQuotedPost($quoteIds));
		}

		foreach ($postList as $row)
		{
			if ($row['group_name'] == 'ADMIN')
			{
				$row['group_name'] = 'Administratorzy';
			}

			$this->parser->setOption('smilies.disable', !$row['post_enable_smilies'] || !User::data('allow_smilies'));
			$this->parser->setOption('html.enable', (bool) $row['post_enable_html']);
			$this->parser->setOption('footnotes.prefix', $row['post_id']);
			$this->parser->setOption('url.disable', $row['post_user'] > User::ANONYMOUS && $row['user_post'] < 10 && (User::$id == User::ANONYMOUS || User::$id == $row['post_user']));

			$wikiAttachment = array();

			if (isset($attachments[$row['post_id']]))
			{
				$li = '';

				foreach ($attachments[$row['post_id']] as $item)
				{
					$wikiAttachment[Text::toLower($item['attachment_name'])] = $item;
					$li .= Html::tag('li', true, array(), Html::a(url($this->page->getLocation() . '?mode=download&id=' . $item['attachment_id']), $item['attachment_name']) . ' (' . Text::formatSize($item['attachment_size']) . ') - ' . Html::tag('em', true, array(), 'ściągnięć: ' . $item['attachment_count']));
				}

				$row['post_text'] .= Html::tag('ul', true, array('class' => 'attachment'), $li);
			}
			$this->parser->setOption('wiki.attachment', $wikiAttachment);

			if ($row['user_sig'] && User::data('allow_sig'))
			{
				// dodalem znak nowej linii (\n). czemu nie bylo go tutaj wczesniej?
				// jest to potrzebne do prawidlowego parsowania tabeli, jezeli pomiedzy tabela a stopka nie ma znaku nowej linii
				$row['post_text'] .= " \n<hr />" . $row['user_sig']; // spacja tutaj jest dzialaniem umyslnym
			}

			$row['post_text'] = $this->transform($row['post_text']);
			$this->postList[$row['post_id']] = $row;

			$postTime[] = $row['post_time'];
		}

		/**
		 * Data i czas napisania ostatniego postu w tym temacie
		 * Jezeli temat jest stronnicowany, i mamy np. 3 strony
		 * ta wartosc bedzie zawierala timestamp ostatniego postu na danej stronie
		 */
		$postTime = max($postTime);

		$this->markTime = $topic->markRead($id, $result['topic_forum'], $postTime);

		/**
		 * Tablica z komentarzami do postow
		 */
		$this->comments = array();
		$this->parser->removeParsers(); // usuniecie parserow przypisanych wczesniej, przy parsowaniu postow

		/**
		 * Obiekt klasy Parser_Login (parsujemy komentarze)
		 */
		$parser = new Parser_Login;
		$config = new Parser_Config;

		foreach ($post->comment->getComments(array_keys($this->postList)) as $row)
		{
			if (!$row['user_photo'])
			{
				$row['user_photo'] = Url::__('template/img/avatar.jpg');
			}
			else
			{
				$row['user_photo'] = Url::__('store/_a/' . $row['user_photo']);
			}
			$row['comment_text'] = Text::transformEmail(Text::transformUrl($row['comment_text'], 70));
			// parsowanie komentarza
			$parser->parse($row['comment_text'], $config);

			$this->comments[$row['comment_post']][] = $row;
		}

		$this->topicFirstPost = array_shift($this->postList);

		if ($this->input->isAjax())
		{
			$this->itemsList = $this->postList;
			$view = new View('_partialPostList');
		}
		else
		{
			$this->usersOnline = array();
			$this->anonymousUsersOnline = 0;

			Topic::retriveOnlineUsers($this->usersOnline, $this->anonymousUsersOnline);

			$view = parent::main();

			if (!$this->page->getMetaDescription())
			{
				$this->output->setMeta('description', Text::limit(Text::plain($this->topicFirstPost['post_text']), 100));
			}
			$this->forumList = $forum->getHtmlList();

			$this->pageList = array();
			for ($i = 10; $i <= 50; $i += 10)
			{
				$this->pageList[$i] = $i;
			}

			$tag = &$this->getModel('tag');
			$this->tags = $tag->getPageTags($this->page->getId());

			$watch = &$this->getModel('watch');
			$this->isWatched = $watch->isWatched($this->page->getId(), $this->module->getId('forum'));

			$this->hasUserPost = true;

			/*
			 * Jezeli uzytkownik ma ustawiona opcje w profilu:
			 * "automatycznie obseruj tematy, w ktorych biore udzial"
			 * nalezy sprawdzic, czy user mimo, ze bral juz udzial w temacie --
			 * oznaczyl temat jako NIEobserwowany.
			 */
			if (!$this->isWatched && User::data('allow_notify'))
			{
				$post = &$this->getModel('post');
				$this->hasUserPost = $post->hasUserPost($id);
			}

			if (Config::getItem('databases.default.event-scheduler') == '1')
			{
				$topic->view->update($id);
			}
			else
			{
				$topic->update(array('topic_views' => ++$result['topic_views']), 'topic_id = ' . $id);
			}
			/**
			 * Pobieramy sciezke do glownej strony forum. W tym celu pobieramy tablice
			 * stron - rodzicow w stosunku do aktualnego tematu. Takie dzialanie bedzie prawidlowe
			 * zakladajac, ze glowna strona forum znajduje sie w "glownej galezi". Tzn. ze strona
			 * glowna forum nie jest dzieckiem
			 */
			$parents = $this->getParents();
			$this->forumUrl = @$parents[0]['location_text'];

			/*
			 * Sciezka do kategorii forum
			 */
			$this->categoryUrl = @$parents[sizeof($parents) -1]['location_text'];
			$log = &$this->getModel('log');

			if ($result['topic_lock'])
			{
				$this->lockInfo = $log->getLastLogMessage($this->page->getId(), Topic::I_LOCK);
			}

			if ($result['topic_moved_id'])
			{
				if ($moveInfo = $log->getLastLogMessage($this->page->getId(), Topic::I_MOVE))
				{
					$this->moveInfo = $moveInfo;

					// amatorski typ wyciagniecia z komunikatu, jedynie powodu przeniesienia watku
					if (strpos($this->moveInfo['log_message'], 'Powód') !== false)
					{
						$this->moveInfo['log_message'] = substr($this->moveInfo['log_message'], strpos($this->moveInfo['log_message'], 'Powód') + 8);
					}
					else
					{
						$this->moveInfo['log_message'] = '';
					}

					$this->moveInfo = array_merge($this->moveInfo, $forum->getForum($result['topic_moved_id']));
				}
			}

			if ($this->isRemovable || $this->isMoveable)
			{
				$this->reasonList = $forum->reason->getReasons();
			}

			if ($this->isEditable)
			{
				$report = &$this->getModel('report');
				$query = $report->select()->where('report_page = ' . $this->page->getId() . ' AND report_close = 0')->get();

				$this->reportList = array();
				foreach ($query as $row)
				{
					if ($row['report_section'])
					{
						$this->reportList[$row['report_section']][] = $row;
					}
				}

				$log = &$this->getModel('log');
				$this->log = $log->filter(null, null, null, null, null, $this->page->getId(), 'log_id');

				$this->logTypes = array(

					E_PAGE_SUBMIT						=> 'Utworzenie/edycja strony',
					E_PAGE_DELETE						=> 'Usunięcie strony',
					E_PAGE_MOVE							=> 'Przeniesienie strony',
					E_PAGE_COPY							=> 'Skopiowanie strony',
					E_PAGE_RESTORE						=> 'Przywrócenie usuniętej strony',

					Topic::I_SUBMIT						=> 'Utworzenie nowego wątku',
					Topic::I_LOCK						=> 'Zablokowanie wątku',
					Topic::I_UNLOCK						=> 'Odblokowanie wątku',
					Topic::I_MOVE						=> 'Przeniesienie wątku',
					Topic::I_EDIT						=> 'Edycja postu',
					Topic::I_DELETE						=> 'Usunięcie postu',
					Topic::I_REPLY						=> 'Napisano odpowiedź w temacie',
					Topic::I_MERGE						=> 'Połączenie postów',
					Topic::I_SUBJECT					=> 'Zmiana tytułu wątku'
				);

				$this->marking = array_merge($forum->marking->getReadForums($result['topic_forum'], $postTime), $topic->marking->getReadTopics($id));
			}

			if ($this->module->isPluginEnabled('poll') && $this->page->getPollId())
			{
				$this->poll = new Poll;
				$this->poll->setStylesheet(false);
				$this->poll->setItem($this->page->getPollId());
			}

			$session = &Load::loadClass('session');
			if (!isset($session->forumHash))
			{
				$session->forumHash = md5(uniqid(rand(), true));
			}

			$this->hash = $session->forumHash;

			/*
			 * Jezeli uzytkownik ma jakies nieprzeczytane powiadomienia, to sprawdzamy,
			 * czy dany URL nie pasuje przypadkiem do powiadomienia ktore jest nieprzeczytane
			 */
			if (User::data('notify_unread'))
			{
				$notify = &$this->getModel('notify');

				if ($postId)
				{
					$notify->header->setReadByUrl(url($this->page->getLocation()) . "?p=$postId#*");
				}
			}
		}
		$view->assign($result);

		return $view;
	}

	private function __download($attachmentId)
	{
		$attachmentId = (int) $attachmentId;

		$post = &$this->getModel('post');
		if (!$result = $post->attachment->find($attachmentId)->fetchAssoc())
		{
			throw new UserErrorException('Załącznik o tym ID nie istnieje lub został usunięty!');
		}

		$query = $post->select('post_id')->where('post_topic = ' . $this->page->getTopicId())->where('post_id = ' . $result['attachment_post'])->get();
		if (!count($query))
		{
			throw new UserErrorException('Załącznik o tym ID nie istnieje lub został usunięty!');
		}

		$post->attachment->update(array('attachment_count' => ++$result['attachment_count']), "attachment_id = $attachmentId");

		set_time_limit(0);

		$this->output->setHttpHeader('Content-Type', $result['attachment_mime']);
		$this->output->setHttpHeader('Content-Disposition', (!$result['attachment_width'] ? 'attachment' : 'inline') . '; filename="' . $result['attachment_name'] . '"');

		$this->output->setHttpHeader('Content-Transfer-Encoding', 'binary');
		$this->output->setHttpHeader('Content-Length', $result['attachment_size']);
		$this->output->setHttpHeader('Cache-control', 'private');
		$this->output->setHttpHeader('Pragma', 'private');
		$this->output->setHttpHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
		flush();

		$chunk = 1024 * 500;

		if ($file = fopen('store/forum/' . $result['attachment_file'], 'r'))
		{
			while (!feof($file)
				&& !connection_aborted())
			{
				echo fread($file, $chunk);
			}

			fclose($file);
		}
		else
		{
			die('Error while opening file');
		}
	}

	private function validateHash()
	{
		$session = &Load::loadClass('session');

		Load::loadFile('lib/validate.class.php');
		$validator = new Validate_NotEmpty;

		if (!$validator->isValid($this->post->hash))
		{
			throw new Exception('Brak klucza');
		}
		if ($this->post->hash != $session->forumHash)
		{
			throw new Exception('Błędny klucz. Sprawdź ustawienia cookies');
		}
	}

	/**
	 * Metoda wyswietla komunikat bledu. W zaleznosci, czy zadanie jest AJAXowe czy nie,
	 * ustawiany jest odpowiedni naglowek Content-type
	 * @param string	$error Komunikat bledu
	 */
	private function setError($error)
	{
		if ($this->input->isAjax())
		{
			$this->output->setStatusCode(500);
			$this->output->setContentType('text/plain');

			echo $error;
		}
		else
		{
			throw new UserErrorException($error);
		}
	}

	/**
	 * Metoda zwraca ID uzytkownikow, ktorzy obserwuja dany post
	 *
	 * @param $postId
	 * @return array
	 */
	private function getPostSubscribers($postId)
	{
		$post = &$this->getModel('post');
		$recipients = array();

		foreach ($post->subscribe->getSubscribers($postId) as $userId)
		{
			if ($userId != User::$id)
			{
				$recipients[] = $userId;
			}
		}

		if ($recipients)
		{
			$recipients = array_intersect($recipients, (array) $this->db->select('g.user_id')->from('page_group p')->innerJoin('auth_group g', 'g.group_id = p.group_id AND g.user_id IN(' . implode(',', $recipients) . ')')->where('p.page_id = ' . $this->page->getId())->get()->fetchCol());
		}

		return $recipients;
	}
}
?>