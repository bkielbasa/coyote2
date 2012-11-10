<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class News_Controller extends Page_Controller
{
	function main()
	{
		$news = &$this->getModel('news');
		
		if ($this->input->isAjax())
		{
			if (User::$id == User::ANONYMOUS)
			{
				exit;
			}
			
			if (isset($this->post->id) && isset($this->post->value))
			{
				echo $news->vote->setVote((int) $this->post->id, (int) $this->post->value);				
			}

			exit;			
		}
		
		$this->maxLength = $this->module->news('snippetLimit', $this->page->getId());
		$this->store = $this->module->news('store', $this->page->getId());
		
		$this->mode = array(

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
				
		foreach ($this->mode as $index => $rowset)
		{
			$queryString = array();
			
			if ($rowset[2])
			{
				$queryString['mode'] = $rowset[2];
			}		
			if (isset($this->get->host))
			{
				$queryString['host'] = $this->get->host;
			}

			$rowset[3] = http_build_query($queryString, '', '&amp;');
			$this->mode[$index] = $rowset;
		}

		if ($this->get->mode)
		{
			$this->news = $news->getNews($this->get->host, $this->get->mode, (int) $this->get['start'], 20);
		}
		else 
		{
			$this->news = $news->getTopNews($this->get->host, (int) $this->get['start'], 20);
		}

		$this->pagination = new Pagination('', $news->getFoundRows($this->get->host, $this->get->mode), 20, (int) $this->get['start']);
		$this->recent = $news->getRecentNews();
		
		$this->location = $this->page->getLocation();
		
		return parent::main();
	}
	
	public function view()
	{
		$this->store = $this->module->news('store');
		$news = &$this->getModel('news');
		
		$this->recentTime = $news->vote->getRecentTime($this->page->getNewsId());
		$this->userId = $this->page->getUserId();
		
		$user = &$this->getModel('user');
		$this->userName = $user->select('user_name')->where('user_id = ?', $this->page->getUserId())->fetchField('user_name');
		
		$view = parent::main();

		if (!$this->output->getMeta('description'))
		{
			$this->output->setMeta('description', Text::limit(Text::plain($this->page->getContent(false)), 100));
		}	
		
		return $view;
	}
	
	public function submit()
	{
		if (User::$id == User::ANONYMOUS)
		{
			$this->redirect(Path::connector('login') . '?' . $this->input->server('QUERY_STRING')); 
		}
		$this->url = '';

		if (isset($this->get->url))
		{
			Load::loadFile('lib/validate.class.php', false);

			$validate = new Validate_Url;
			if ($validate->isValid($this->get['url']))
			{
				$this->url = $this->get['url'];				
			}
		}

		Breadcrumb::add(url($this->page->getLocation()), $this->page->getTitle() ? $this->page->getTitle() : $this->page->getSubject());
		Breadcrumb::add('', 'Dodaj nowy link');

		$this->form = new Forms;
		$this->form->setMethod(Forms::POST);
		$this->form->id = 'submit-form';

		$this->form->createElement('hash', 'hash-news');
		$this->form->createElement('text', 'url', array(), array(
			'Required'			=> true,
			'Label'				=> 'Adres URL',
			'Filters'			=> array('trim', 'htmlspecialchars'),
			'Validators'		=> array(
											array('string', false, 1, 255),
											array('url'),
											array('news')
								)
			)
		);

		$this->form->createElement('text', 'title', array(), array(
			'Required'			=> true,
			'Label'				=> 'Tytuł newsa',
			'Description'		=> 'Tytuł newsa będzie widoczny dla pozostałych użytkowników - będzie linkiem do podanej strony WWW',
			'Validators'		=> array(
											array('string', false, 3, 255)
								),
			'Filters'			=> array('trim', 'htmlspecialchars')
			)
		);

		$this->form->createElement('textarea', 'content', array('cols' => 90, 'rows' => 15), array(
			'Label'				=> 'Opis',
			'Description'		=> 'UWAGA! Ten opis będzie widoczny dla innych użytkowników. Postaraj się, aby był jak najbardziej szczegółowy',
			'Required'			=> true,
			'Filters'			=> array('trim'),
			)
		);

		$this->form->createElement('submit', '')->setValue('Dodaj link');
		$this->images = array();

		if ($this->input->isPost())
		{		
			if ($this->form->isValid())
			{
				$page = new Page(new Connector_News);
				$page->setUrl($this->post->url);					
				$page->setSubject($this->post->title);
				$page->setParentId($this->page->getId());			
				$page->setIp($this->input->getIp());
				$page->setContent($this->post->content);
				
				$store = $this->module->news('store');
				if (@is_writeable($store))
				{
					if ($this->post->thumbnail)
					{
						$suffix = pathinfo($this->post->thumbnail, PATHINFO_EXTENSION);
						if (in_array($suffix, array('jpg', 'jpeg', 'png', 'gif')))
						{
							$uniqid = uniqid(mt_rand(1, 1000), true) . '.' . $suffix;
							
							file_put_contents($store . $uniqid, file_get_contents($this->post->thumbnail));
							$page->setThumbnail($uniqid);
							
							$image = new Image($store . $uniqid);
							$image->thumbnail(120, 120);
							$image->save($store . '120-' . $uniqid);
							
							$image->thumbnail(50, 50);
							$image->save($store . $uniqid);
							$image->close();
						}
					}
				}
			
				
				$this->load->model('page');
				$page->setGroups((array) $this->model->page->group->getGroups($this->page->getId()));
				
				$page->save();
				$this->redirect($page->getLocation());
			}
		}

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
	
	private function getUrlInfo($url)
	{
		Load::loadFile('lib/validate.class.php', false);
		
		$validate = new Validate_Url;
		if (!$validate->isValid($url))
		{
			return false;
		}
		$title = $description = '';
		
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
		$images = array();
		$host = parse_url($url, PHP_URL_HOST);
		$scheme = parse_url($url, PHP_URL_SCHEME);
		
		$imageNodes = $xpath->query('//img[@src]');
		foreach ($imageNodes as $imageNode)
		{
			$image = $imageNode->getAttribute('src');
			
			if (!preg_match('#^[\w]+?://.*?#i', $image))
			{
				$images[] .= $scheme . '://' . $host . (substr($image, 0, 1) != '/' ? '/' . $image : $image);
			}
			else
			{
				$images[] = $image;
			}
		}

		return array(
			'title'			=> $title,
			'description'	=> $description,
			'images'		=> $images
		);			
	}
}
?>