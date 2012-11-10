<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Topic extends Connector_Document implements Connector_Interface
{
	/**
	 * Tablica zawiera wartosci rekordu z tabeli topic
	 */
	private $topicData = array();
	/**
	 * ID forum
	 */
	private $forumId;
	/**
	 * Tresc tematu (czyli de facto - pierwszego postu w temacie)
	 */
	private $topicContent;
	/**
	 * Nazwa uzytkownika anonimowego
	 */
	private $userName;
	/**
	 * Okresla, czy temat jest przyklejony
	 */
	private $isSticky = false;
	/**
	 * Okresla, czy temat jest jednoczesnie ogloszeniem
	 */
	private $isAnnouncement = false;
	/**
	 * Okresla, czy temat jest zablokowany
	 */
	private $isLocked = false;
	/**
	 * Okresla, czy w temacie wlaczone sa emoticony
	 */
	private $enableSmilies = false;
	/**
	 * Okresla, czy w poscie wlaczony jest HTML
	 */
	private $enableHtml = false;
	/**
	 * ID tematu
	 */
	private $topicId;
	/**
	 * ID pierwszego postu w temacie
	 */
	private $postId;
	/**
	 * ID ankiety przypisanej do tematu
	 */
	private $pollId;
	/**
	 * Ilosc odpowiedzi w watku
	 */
	private $replies;
	/**
	 * ID pierwszego postu w watku
	 */
	private $topicFirstPostId;
	/**
	 * ID ostatniego postu w watku
	 */
	private $topicLastPostId;
	/**
	 * Czas (timestamp) napisania ostatniego postu w watku
	 */
	private $topicLastPostTime;

	function __construct($data = array())
	{
		parent::__construct($data);

		if ($this->getId())
		{
			$topic = &$this->getModel('topic');
			$query = $topic->getByPage($this->getId());

			if (!count($query))
			{
				throw new Exception('Strona #' . $this->getId() . ' nie jest przydzielona do żadnego tematu');
			}
			$this->topicData = $result = $query->fetchAssoc();

			$this->topicId = $result['topic_id'];
			$this->forumId = $result['topic_forum'];
			$this->isSticky = $result['topic_sticky'];
			$this->isAnnouncement = $result['topic_announcement'];
			$this->isLocked = $result['topic_lock'];
			$this->pollId = $result['topic_poll'];
			$this->replies = $result['topic_replies'];
			$this->topicFirstPostId = $result['topic_first_post_id'];
			$this->topicLastPostId = $result['topic_last_post_id'];
			$this->topicLastPostTime = $result['topic_last_post_time'];
			/**
			 * @todo Odczyt posta przypisanego do tego tematu...
			 */
		}
	}

	public function getTopicData()
	{
		return $this->topicData;
	}

	public function renderForm()
	{
		parent::renderForm();

		$fieldset = &$this->getFieldset('content');

		$fieldset->getElement('page_subject')
				 ->setLabel('Nazwa tematu')
				 ->setDescription('Właściwa nazwa tematu');

		$fieldset->getElement('page_title')
				 ->setLabel('Rozszerzona nazwa tematu');

		$fieldset->getElement('page_path')
				 ->setLabel('Ścieżka do tematu')
				 ->setDescription('Ścieżka po której będzie identyfikowany dany temat');

		$fieldset->removeElement('page_parent');

		$forum = &$this->getModel('forum');
		$query = $forum->getList(false);

		$forumList = array();
		foreach ($query->get() as $row)
		{
			$forumList[$row['forum_id']] = str_repeat('&nbsp;', $row['page_depth'] * 4) . $row['page_subject'];
		}

		if ($this->getId())
		{
			$fieldset->createElement('hidden', 'forum_id')->setValue($this->getForumId());
		}
		else
		{
			$element = $fieldset->createElement('select', 'forum_id')
								 ->setLabel('Forum macierzyste')
								 ->setMultiOptions($forumList)
								 ->addFilter('int')
								 ->setDescription('Wybierz forum macierzyste, do którego ma być przypisany ten temat')
								 ->setRequired(true);

		}

		$fieldset->getElement('text_content')
				 ->setAttribute('style', 'width: 56%')
				 ->setAttribute('rows', 10)
				 ->setLabel('Zawartość strony');

		$fieldset->createElement('textarea', 'topic_content')
				 ->setLabel('Treść tematu')
				 ->setAttribute('cols', 55)
				 ->setAttribute('rows', 20)
				 ->setAttribute('style', 'width: 56%')
				 ->setOrder(6);

		$fieldset->getElement('style')
				 ->setAttribute('style', 'width: 56%')
				 ->setAttribute('rows', 10);

		$this->getFieldset('setting')->getElement('page_template')->setValue('topicView.php');

		$this->setDefaults();

		/*if ($this->getId())
		{
			preg_match('#(\d+),(.*)#', $this->getPath(), $match);
			$fieldset->getElement('page_path')->setValue(@$match[2]);

			$this->topicId = $match[1];
		}*/
	}

	public function setForumId($forumId)
	{
		$this->forumId = (int) $forumId;

		$forum = &$this->getModel('forum');
		$this->setParentId($forum->find($forumId)->fetchField('forum_page'));

		return $this;
	}

	public function getForumId()
	{
		return $this->forumId;
	}

	public function getTopicId()
	{
		return $this->topicId;
	}

	public function getPostId()
	{
		return $this->postId;
	}

	public function setUserName($userName)
	{
		$this->userName = $userName;
		return $this;
	}

	public function getUserName()
	{
		return $this->userName;
	}

	public function setTopicContent($topicContent)
	{
		$this->topicContent = $topicContent;
		return $this;
	}

	public function getTopicContent()
	{
		return $this->topicContent;
	}

	public function setIsAnnouncement($flag)
	{
		$this->isAnnouncement = (bool) $flag;
		return $this;
	}

	public function isAnnouncement()
	{
		return $this->isAnnouncement;
	}

	public function setIsSticky($flag)
	{
		$this->isSticky = (bool) $flag;
		return $this;
	}

	public function isSticky()
	{
		return $this->isSticky;
	}

	public function setIsLocked($flag)
	{
		$this->isLocked = (bool) $flag;
		return $this;
	}

	public function isLocked()
	{
		return $this->isLocked;
	}

	public function setEnableSmilies($flag)
	{
		$this->enableSmilies = (bool) $flag;
		return $this;
	}

	public function isSmiliesEnabled()
	{
		return $this->enableSmilies;
	}

	public function setEnableHtml($flag)
	{
		$this->enableHtml = (bool) $flag;
		return $this;
	}

	public function isHtmlEnabled()
	{
		return $this->enableHtml;
	}

	public function setPollId($pollId)
	{
		$this->pollId = $pollId;
		return $this;
	}

	public function getPollId()
	{
		return $this->pollId;
	}

	public function getReplies()
	{
		return $this->replies;
	}

	public function getFirstPostId()
	{
		return $this->topicFirstPostId;
	}

	public function getLastPostId()
	{
		return $this->topicLastPostId;
	}

	public function getLastPostTime()
	{
		return $this->topicLastPostTime;
	}

	public function onBeforeSave()
	{
		$topic = &$this->getModel('topic');

		if ($this->isRenderMode())
		{
			$values = $this->getValues();

			if (!empty($values['forum_id']))
			{
				$this->setForumId($values['forum_id']);
			}

			$this->setSubject(@$values['page_subject']);
			$this->setTitle(@$values['page_title']);
			$this->setPath(@$values['page_path']);

			$this->setContent(@$values['text_content']);
			$this->setContentType(@$values['page_content']);
			$this->setLog(@$values['log']);

			$this->setRichTextId(@$values['page_richtext']);
			$this->setIsPublished(@$values['page_publish']);
			$this->setIsCached(@$values['page_cache']);
			$this->setPublishDate(@$values['page_published']);
			$this->setUnpublishDate(@$values['page_unpublished']);
			$this->setTemplate(@$values['page_template']);
			$this->setMetaTitle(@$values['meta_title']);
			$this->setMetaKeywords(@$values['meta_keywords']);
			$this->setMetaDescription(@$values['meta_description']);

			$this->setForumId(@$values['forum_id']);
			$this->setTopicContent(@$values['topic_content']);
		}

		if (!$this->getPath())
		{
			$this->setPath($this->getSubject());
		}
		if (!$this->getTemplate())
		{
			$this->setTemplate('topicView.php');
		}
		$this->setIsPublished(true);
		$this->setIsCached(true);

		if (!$this->getContentType())
		{
			$this->setContentType(Page::HTML);
		}

		if (!$this->getId())
		{
			if (!$this->getConnectorId())
			{
				$connectorId = $this->get->connectorId;
			}
			else
			{
				$connectorId = $this->getConnectorId();
			}

			$this->setModuleId($this->module->getId('forum'));
			$this->setConnectorId($connectorId);
			$this->setIp($this->input->getIp());

			$uniqId = $topic->getNextId();
			$this->setPath($uniqId . '-' . $this->getPath());
		}
		else
		{
			if (!$this->isRenderMode())
			{
				if (!preg_match('#(\d+)\-(.*)#', $this->getPath()))
				{
					$this->setPath($this->getId() . '-' . $this->getPath());
				}
			}
		}
	}

	public function onAfterSave()
	{
		parent::onAfterSave();

		$topic = &$this->getModel('topic');

		if (!$this->getTopicId())
		{
			$topic->insert(array(
				'topic_page'			=> $this->getId(),
				'topic_forum'			=> (int) $this->getForumId(),
				'topic_sticky'			=> (bool) $this->isSticky(),
				'topic_announcement'	=> (bool) $this->isAnnouncement(),
				'topic_poll'			=> (int) $this->getPollId(),
				'topic_lock'			=> (bool) $this->isLocked()
				)
			);
			$this->topicId = $this->db->nextId();

			$post = &$this->getModel('post');
			$this->postId = $post->submit($this->getForumId(), $this->getTopicId(), $this->getTopicContent(), $this->getUserName(), $this->isSmiliesEnabled(), $this->isHtmlEnabled());
		}
		else
		{
			$topic->update(array(
				'topic_forum'			=> (int) $this->getForumId(),
				'topic_sticky'			=> (bool) $this->isSticky(),
				'topic_announcement'	=> (bool) $this->isAnnouncement(),
				'topic_poll'			=> (int) $this->getPollId(),
				'topic_lock'			=> (bool) $this->isLocked()
				),

				'topic_id = ' . $this->getTopicId()
			);
			/**
			 * @todo
			 *
			 * Uaktualnianie danych pierwszego posta...
			 */
		}
	}

	public function delete()
	{
		$topic = &$this->getModel('topic');

		Trigger::call('application.onTopicDelete', $this->getTopicId());
		/**
		 * W modelu usuwane sa ewentualne zalaczniki przypisane do postow z danego tematu
		 */
		$topic->delete($this->getTopicId());
		Trigger::call('application.onTopicDeleteComplete', $this->getTopicId());

		parent::delete();
	}

	public function move($parentId)
	{
		$topic = &$this->getModel('topic');

		Trigger::call('application.onPageMove', $this->getId(), $parentId);
		Trigger::call('application.onTopicMove', $this->getTopicId(), $parentId);

		$forumId = $this->db->select('forum_id')->where("forum_page = $parentId")->get('forum')->fetchField('forum_id');
		$topic->move($this->getTopicId(), $this->getId(), $forumId);

		Trigger::call('application.onTopicMoveComplete', $this->getTopicId(), $parentId);
		Trigger::call('application.onPageMoveComplete', $this->getId(), $parentId);
	}

	public function &getDocument()
	{
		$document = &parent::getDocument();
		$post = &$this->getModel('post');

		if ($this->getTopicId())
		{
			$document->addField('topic_forum_i', (int) $this->getForumId());
			$document->addField('topic_replies_i', (int) $this->getReplies());
			$postIds = array(); // ID postow

			$postList = $post->fetch('post_topic = ' . $this->getTopicId())->fetchAll();
			foreach ($postList as $row)
			{
				$document->addField('post_id', (int) $row['post_id']);
				$document->addField('post_text', (string) htmlspecialchars($row['post_text']));
				$document->addField('post_ip', (int) ip2long($row['post_ip']));
				$document->addField('post_user_i', (int) $row['user_id']);
				$document->addField('post_user_t', (string) $row['post_username']);
				$document->addField('post_time_i', (int) $row['post_time']);

				$postIds[] = $row['post_id'];
			}

			$document->addField('topic_first_post_i', min($postIds));
			$document->addField('topic_last_post_i', max($postIds));
		}

		return $document;
	}

	/**
	 * Metoda nadpisuje metode encodePath() z klasy bazowej
	 * Ignoruje ustawienia projektu i usuwa np. polskie znaki ze sciezki strony
	 * @param string $value
	 * @return string
	 */
	public static function encodePath($value)
	{
		$path = new Path(false);
		$filter = &$path->getFilter();

		$chars = str_split(Config::getItem('url.remove'));
		$chars[] = '.'; // niezaleznie od ustawien globalnych, usuwamy te znaki
		$chars[] = '/';
		$chars[] = '(';
		$chars[] = ')';
		$chars[] = ':';

		$filter->addFilter('strip_tags');

		$filter->addFilter(new Filter_Replace($chars));
		// usuwamy polskie znaki niezaleznie od ustawien globalnych
		$filter->addFilter(new Filter_Diacritics);
		// zmniejszamy do malych znakow niezaleznie od ustawien globalnych
		$filter->addFilter('strtolower');

		$value = htmlspecialchars_decode($value);
		return $path->encode($value);
	}
}
?>