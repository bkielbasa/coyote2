<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Bookmark_Controller extends Page_Controller
{
	function main()
	{
		$bookmark = &$this->getModel('bookmark');
		$conditions = null;

		if ($this->get->host)
		{
			$conditions = 'bookmark_host = "' . $this->get->host . '"';
		}

		$this->sort = array(

			array(
				'Ostatnie',
				'Zobacz najnowsze, ostatnio polecane zakładki',
				''
			),
			array(
				'24 godz.',
				'Zobacz najpopularniejsze zakładki z ostatnich 24 godzin',
				'24'
			),
			array(
				'7 dni',
				'Zobacz najpopularniejsze zakładki w przeciągu tygodnia',
				'7'
			),
			array(
				'30 dni',
				'Zobacz najpopularniejsze zakładki w przeciągu miesiąca',
				'30'
			),
			array(
				'365 dni',
				'Zobacz najpopularniejsze zakładki w przeciągu roku',
				'365'
			)
		);
		$this->baseUrl = $this->page->getLocation();
		$having = null;

		switch ($this->get->sort)
		{
			case 24:

				$order = 'bookmark.bookmark_rank DESC';
				$having = 'rank_time > ' . (time() - 86400);
				break;

			case 7:

				$order = 'bookmark.bookmark_rank DESC';
				$having = 'rank_time > ' . (time() - 604800);
				break;

			case 30:

				$order = 'bookmark.bookmark_rank DESC';
				$having = 'rank_time > ' . (time() - 2592000);
				break;

			case 365:

				$order = 'bookmark.bookmark_rank DESC';
				$having = 'rank_time > ' . (time() - 31536000);
				break;

			default:

				$order = 'bookmark.bookmark_rank > -1 DESC, rank_time DESC';
				break;
		}
		
		$result = $bookmark->fetch($conditions, $order, (int)$this->get['start'], 20, $having)->fetch();
		$totalItems = $bookmark->getFoundRows();

		$bookmark = new Bookmark;		

		$this->bookmark = $bookmark->decorate($result);
		$this->pagination = new Pagination('', $totalItems, 20, (int)$this->get['start']);

		return parent::main();
	}

	public function view()
	{
		$bookmark = &$this->getModel('bookmark');
		$result = array();

		if (!$result = $bookmark->getByPage($this->page->getId())->fetchAssoc())
		{
			throw new Error(404);
		}

		return parent::main();
	}
	
	private function getUrlInfo($url)
	{
		Load::loadFile('lib/validate.class.php', false);
		
		$validate = new Validate_Url;
		if (!$validate->isValid($url))
		{
			return false;
		}
		
		$dom = new DOMDocument;
		@$dom->loadHtml(@file_get_contents($url));		

		$xpath = new DOMXPath($dom);
		$titleNodes = $xpath->query('/html/head/title');

		foreach ($titleNodes as $titleNode) 
		{
			$title = $titleNode->nodeValue;
			break;
		}

		$metaNodes = $xpath->query('/html/head/meta[@name]');
		foreach ($metaNodes as $metaNode) 
		{
			if (strcasecmp($metaNode->getAttribute('name'), 'description') == 0)
			{
				$description = $metaNode->getAttribute('content');
			}					
		}
		
		return array(
			'title'			=> $title,
			'description'	=> $description
		);			
	}

	public function add()
	{
		if (User::$id == User::ANONYMOUS)
		{
			$this->redirect(Path::connector('login') . '?redirect=' . $this->page->getLocation()); 
		}
		$url = $this->get['url'];
		$title = $description = '';

		if ($url)
		{
			Load::loadFile('lib/validate.class.php', false);

			$validate = new Validate_Url;
			if (!$validate->isValid($url))
			{
				throw new Error(500, 'Podany URL jest nieprawidłowy');
			}

			$bookmark = &$this->getModel('bookmark');
			$result = $bookmark->getByUrl($url)->fetchAssoc();

			if ($result)
			{
				$query = $bookmark->user->select()->where('bookmark_url = ' . $result['bookmark_id'] . ' AND bookmark_user = ' . User::$id)->get();
				if (count($query))
				{
					throw new Error(500, 'Strona o podamy URL jest już na Twojej liście zakładek');
				}
			}
		}

		Breadcrumb::add(url($this->page->getLocation()), $this->page->getTitle() ? $this->page->getTitle() : $this->page->getSubject());
		Breadcrumb::add('', 'Dodaj zakładkę');

		$this->form = new Forms;
		$this->form->setMethod(Forms::POST);

		$this->form->createElement('text', 'url', array(), array(
			'Required'			=> true,
			'Label'				=> 'Adres URL',
			'Validators'		=> array(
											array('string', false, 1, 255),
											array('url')
								)
			)
		);

		$this->form->createElement('text', 'title', array(), array(
			'Required'			=> true,
			'Label'				=> 'Tytuł zakładki',
			'Description'		=> 'Tytuł zakładki będzie widoczny dla innych użytkowników serwisu',
			'Validators'		=> array(
											array('string', false, 3, 255)
								),
			'Filters'			=> array('htmlspecialchars')
			)
		);

		$this->form->createElement('textarea', 'content', array('cols' => 90, 'rows' => 15), array(
			'Label'				=> 'Opis zakładki',
			'Description'		=> 'UWAGA! Ten opis będzie widoczny dla innych użytkowników',
			'Required'			=> true,
			'Validators'		=> array(
											array('string', false, 5)
								),
			'Filters'			=> array('htmlspecialchars'),
			)
		);

		$this->form->createElement('textarea', 'description', array('cols' => 90, 'rows' => 5), array(
			'Label'				=> 'Twój opis',
			'Description'		=> 'Twój opis zakładki, będzie widoczny jedynie dla Ciebie',
			'Filters'			=> array('htmlspecialchars')
			)
		);
		$this->form->createElement('submit', '')->setValue('Zapisz zakładkę');

		if ($this->input->isPost())
		{
			if ($this->form->isValid())
			{
				$bookmark = &$this->getModel('bookmark');
				$result = $bookmark->getByUrl($this->post->url)->fetchAssoc();

				if ($result)
				{
					$query = $bookmark->user->select()->where('bookmark_url = ' . $result['bookmark_id'] . ' AND bookmark_user = ' . User::$id)->get();
					if (count($query))
					{
						throw new Error(500, 'Strona o podanym URL jest już na Twojej liście zakładek');
					}

					$id = $result['bookmark_id'];
				}
				else
				{
					$page = new Page(new Connector_Bookmark_View);
					$page->setUrl($this->post->url);					
					$page->setSubject($this->post->title);
					$page->setParentId($this->page->getId());
					$page->setIsPublished(true);
					$page->setIsCached(true);					
					$page->setIp($this->input->getIp());
					$page->setContent($this->post->content);
					
					$this->load->model('page');
					$page->setGroups((array) $this->model->page->group->getGroups($this->page->getId()));
					
					$parser = &$this->getModel('parser');
					$query = $parser->getByName(array('html', 'wiki', 'highlight', 'br', 'url'));

					$this->page->setParsers($query->fetchCol());
				
					$page->save();
					$id = $page->getBookmarkId();
				}

				$bookmark->user->insert(array(
					'bookmark_user'			=> User::$id,
					'bookmark_description'	=> (string)$this->post->description,
					'bookmark_url'			=> $id
					)
				);
				$bookmark->rank->setRank($id, 1);				

				$this->session->message = 'Zakładka została dodana';
				$this->redirect($this->page->getLocation());

				exit;
			}
		}
		elseif ($url)
		{
			$title = $this->get->title;
			$description = $this->get->description;

			if (!$title)
			{
				$data = $this->getUrlInfo($url);
				
				$title = $data['title'];
				$description = $data['description'];								
			}
		}

		$this->form->getElement('url')->setValue($url);
		$this->form->getElement('title')->setValue($title);
		$this->form->getElement('content')->setValue($description);

		return true;
	}
	
	public function fetch()
	{
		$url = $this->get['url'];
		if ($data = $this->getUrlInfo($url))
		{
			echo json_encode($data);
		}
		
		exit;
	}
}
?>