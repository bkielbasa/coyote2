<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Forum_View extends Connector_Document implements Connector_Interface
{
	protected $forumId;
	protected $topics;
	protected $posts;
	protected $description;
	protected $section;
	protected $prune = 0;
	protected $isLocked = false;
	protected $url;

	function __construct($data = array())
	{
		parent::__construct($data);

		if ($this->getId())
		{
			$forum = &$this->getModel('forum');

			$result = $forum->getByPage($this->getId())->fetchAssoc();

			$this->forumId = $result['forum_id'];
			$this->topics = $result['forum_topics'];
			$this->posts = $result['forum_posts'];

			$this->setDescription($result['forum_description']);
			$this->setSection($result['forum_section']);
			$this->setUrl($result['forum_url']);
			$this->setPrune($result['forum_prune']);
			$this->setIsLocked($result['forum_lock']);
			unset($result);
		}
	}

	public function renderForm()
	{
		parent::renderForm();

		$fieldset = &$this->getFieldset('content')->setLabel('Opcje forum');
		$fieldset->getElement('page_subject')
				 ->setLabel('Nazwa forum')
				 ->addConfig('description', 'Właściwa nazwa forum');

		$fieldset->getElement('page_title')
				 ->setLabel('Rozszerzona nazwa forum')
				 ->addConfig('description', 'Opcjonalnie. Rozszerzona nazwa forum. Może to wpłynąć korzystanie na pozycjonowanie');

		$fieldset->getElement('page_path')
				 ->setLabel('Ścieżka do forum')
				 ->addConfig('description', 'Ścieżka po której identyfikowana będzie dana kategoria forum');

		$fieldset->createElement('text', 'section')
				 ->setLabel('Nazwa sekcji')
				 ->setDescription('Kategorie forum mogą być podzielone na sekcje - np. Kategorie ogólne, Pozostałe')
				 ->setOrder(5)
				 ->setValue($this->getSection());

		$fieldset->createElement('textarea', 'description')
				 ->setAttribute('cols', 60)
				 ->setAttribute('rows', 15)
				 ->setLabel('Opis forum')
				 ->setDescription('Krotki opis forum, wyświetlany na liście for')
				 ->setOrder(6)
				 ->setValue($this->getDescription());

		$fieldset->createElement('checkbox', 'lock')
				 ->setLabel('Forum zablokowane')
				 ->setDescription('Jeżeli forum jest zablokowane nie można w nim publikować nowych postów oraz jest odznaczane specjalną ikoną')
				 ->setOrder(7)
				 ->setChecked($this->isLocked());

		$purgeSelect = array(
			0					=> 'Brak automatycznego usuwania',
			7					=> 'Usuwaj po upływie 7 dni',
			14					=> 'Usuwaj po upływie 2 tygodni',
			31					=> 'Usuwaj po upływie 31 dni',
			60					=> 'Usuwaj po upływie 2 miesiącach',
			90					=> 'Usuwaj po upływie 3 miesięcy'
		);

		$fieldset->createElement('select', 'prune')
				 ->setLabel('Kasuj tematy starsze niż')
				 ->setDescription('Tematy, w których ostatnią aktywność stwierdzono X dni temu będą usuwane.')
				 ->setMultiOptions($purgeSelect)
				 ->setOrder(8)
				 ->setValue($this->getPrune());

		$fieldset->getElement('text_content')
				 ->setLabel('Treść strony')
				 ->setAttribute('style', '')
				 ->setAttribute('cols', 60)
				 ->addConfig('description', 'Jeżeli chcesz wyświetlić na tej stronie dodatkowe informacje, to jest miejsce na ich wpisanie');

		$fieldset->getElement('style')
				 ->setAttribute('style', '')
				 ->setAttribute('cols', 60)
				 ->setAttribute('rows', 15);

		$this->getFieldset('setting')->getElement('page_template')->setValue('forumView.php');

		$this->setDefaults();
	}

	public function getForumId()
	{
		return $this->forumId;
	}

	public function getTopics()
	{
		return $this->topics;
	}

	public function getPosts()
	{
		return $this->posts;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function setSection($section)
	{
		$this->section = $section;
		return $this;
	}

	public function getSection()
	{
		return $this->section;
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

	public function setPrune($prune = false)
	{
		$this->prune = $prune;
		return $this;
	}

	public function getPrune()
	{
		return $this->prune;
	}

	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function onAfterSave()
	{
		parent::onAfterSave();

		if ($this->isRenderMode())
		{
			$values = $this->getValues();
			$this->setDescription((string) $values['description']);
			$this->setSection((string) $values['section']);

			if (intval($values['lock']))
			{
				$this->setIsLocked(true);
			}

			$this->setPrune(intval($values['prune']));
		}

		$data = array(
			'forum_description'			=> (string) $this->getDescription(),
			'forum_section'				=> (string) $this->getSection(),
			'forum_lock'				=> (int) $this->isLocked(),
			'forum_prune'				=> (int) $this->getPrune(),
			'forum_url'					=> (string) $this->getUrl()
		);

		if (!$this->getForumId())
		{
			$auth = &$this->getModel('auth');
			$authList = array();

			foreach ($auth->getOptions() as $key => $row)
			{
				if (strpos($row['option_text'], 'f_') !== false)
				{
					$authList[$key] = $row;
				}
			}
			$permission = array();

			$groups = $this->db->select('group_id')->from('`group`')->fetchCol();
			foreach ($groups as $group)
			{
				foreach ($authList as $key => $row)
				{
					$permission[$group][$key] = $row['option_default'];
				}
			}

			$data += array('forum_page'	=> (int) $this->getId(), 'forum_permission' => serialize($permission));
			$this->db->insert('forum', $data);
		}
		else
		{
			$this->db->update('forum', $data, 'forum_page = ' . $this->getId());
		}
	}

	public function delete()
	{
		$forum = &$this->getModel('forum');
		$forum->delete('forum_id = ' . $this->getForumId());

		parent::delete();
	}

	public function &getDocument()
	{
		$document = &parent::getDocument();

		if ($this->getForumId())
		{
			$document->addField('forum_text', (string) htmlspecialchars($this->getDescription()));
		}

		return $document;
	}
}
?>