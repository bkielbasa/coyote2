<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Solr_Controller extends Page_Controller
{
	const SEARCH_DATE		=		'd';
	const SEARCH_SORT		=		's';

	const ALL				=		'a';
	const HOUR				=		'h';
	const DAY				=		'd';
	const WEEK				=		'w';

	const SCORE				=		's';
	const DATE				=		'd';

	public $options = array(

		self::SEARCH_DATE		=> array(

				self::ALL			=>	'Dowolny czas',
				self::HOUR			=>	'Ostatnia godzina',
				self::DAY			=>	'Ostatnie 24 godziny',
				self::WEEK			=>	'Ostatni tydzien'
		),
		self::SEARCH_SORT		=> array(

				self::SCORE			=>	'Według trafności',
				self::DATE			=>	'Według daty'
		)
	);

	public $searchOptions;

	function __start()
	{
		$this->searchOptions = array(
			self::SEARCH_DATE				=> self::ALL,
			self::SEARCH_SORT				=> self::SCORE
		);
		$cookie = unserialize($this->cookie->solr);

		foreach ((array) $cookie as $key => $arr)
		{
			if (isset($this->get->$key))
			{
				$this->searchOptions[$key] = $this->get->$key;
			}
			elseif (isset($cookie[$key]))
			{
				$this->searchOptions[$key] = $cookie[$key];
			}
		}

		$this->output->setCookie('solr', serialize($this->searchOptions), strtotime('+30 days'));
	}

	public function getSearchSort()
	{
		return $this->searchOptions[self::SEARCH_SORT];
	}

	public function getTimeLimit()
	{
		return $this->searchOptions[self::SEARCH_DATE];
	}

	function main()
	{
		$connector = &$this->getModel('connector');
		$this->connectorList = $connector->select('connector_id, connector_text')->fetchPairs();

		$this->solr = new Search(new Search_Solr);
		$this->solr->setQueryString($this->get['q']);
		$this->solr->setEnableHighlight(true);
		$this->solr->setHighlightFields('*');
		$this->solr->setHighlightSize(200);
		$this->solr->setHighlightSnippets(2);
		$this->solr->setHighlightTag('strong');
		$this->solr->setEnableSuggestion(true);
		$this->solr->setEnableFacet(true);

		$this->solr->addField('keyword', '3');
		$this->solr->addField('description', '2.6');
		$this->solr->addField('title', '2.5');
		$this->solr->addField('location', '2.0');
		$this->solr->addField('subject', '1.5');
		$this->solr->addField('tag', '1.0');
		//$this->solr->addField('body');
		$this->solr->addField('text', '0.8');
		// parametr ps - odleglosc slow od siebie
		// parametr pf - phrase fields

		if ($this->getSearchSort() == self::DATE)
		{
			$this->solr->setSort('timestamp desc');
		}

		switch ($this->getTimeLimit())
		{
			case self::HOUR:

				$this->solr->setTimeLimit(Time::HOUR);
				break;

			case self::DAY:

				$this->solr->setTimeLimit(Time::DAY);
				break;

			case self::WEEK:

				$this->solr->setTimeLimit(Time::WEEK);
				break;
		}
		if ($this->get->cat)
		{
			$this->solr->addFilter('connector', $this->get->cat);
		}
		$user = &$this->getModel('user');
		$this->solr->setGroups($user->getGroups());

		parse_str($this->input->server('QUERY_STRING'), $qs);
		unset($qs['start'], $qs['cat']);

		$this->baseUrl = url($this->page->getLocation()) . '?' . http_build_query($qs);
		$this->hits = array();

		if (trim($this->get->q))
		{
			$this->solr->setStartPage((int) $this->get['start']);
			$this->solr->setLimit(5);

			$hits = null;

			try
			{
				$hits = $this->solr->find();
			}
			catch (Exception $e)
			{
				throw new Error(500, 'Nieprawidłowe zapytanie lub brak połączenia z serwerem wyszukiwarki.');
			}

			if ($hits)
			{
				foreach ($hits as $hit)
				{
					$title = '';
					$body = array();

					if ($hit->title)
					{
						if (isset($hit->highlight['title']))
						{
							$title = $hit->highlight['title'][0];
						}
						else
						{
							$title = !is_array($hit->title) ? $hit->title : $hit->title[0];
						}
					}
					else
					{
						$title = isset($hit->highlight['subject']) ? $hit->highlight['subject'][0] : $hit->subject;
					}

					foreach ($hit as $key => $value)
					{
						if (isset($hit->highlight[$key]) && $this->isString($key))
						{
							$body[] = implode(' ... ', $hit->highlight[$key]);
						}
						elseif ($this->isString($key))
						{
							$body[] = substr($hit->getField($key, 1), 0, 200);
						}
					}

					$this->hits[] = array(
						'url'				=> url($hit->location),
						'location'			=> isset($hit->highlight['location']) ? $hit->highlight['location'][0] : $hit->location,
						'description'		=> $hit->description,
						'title'				=> $title,
						'timestamp'			=> $hit->timestamp,
						'body'				=> implode(' ', $body)
					);
				}
			}

			$this->output->setTitle('Wyniki wyszukiwania dla "' . htmlspecialchars($this->get['q']) . '" :: ' . Config::getItem('site.title'));
		}
		$this->pagination = new Pagination('', $this->solr->getTotalRows(), 5, $this->solr->getStartPage());

		return parent::main();
	}

	private function isString($key)
	{
		return $key == 'body' || substr($key, -2, 2) == '_t' || substr($key, -5, 5) == '_text';
	}

	public function __suggest()
	{
		$query = $this->get['q'];
		if (!$query)
		{
			return;
		}

		$html = array();
		$queryString = 'suggestion:"' . $query . '"';

		try
		{
			$user = &$this->getModel('user');

			$group = array();
			foreach ($user->getGroups() as $groupId)
			{
				$group[] = 'group:' . $groupId;
			}

			$solr = new Solr(Config::getItem('solr.host'), Config::getItem('solr.port'), Config::getItem('solr.path'));
			$hits = $solr->search($queryString, 0, 20, array('fl' => 'subject', 'fq' => implode(' OR ', $group)));

			foreach ($hits as $hit)
			{
				$html[] = Html::tag('li', true, array(), preg_replace("~^($query)~i", '<b>\\1</b>', $hit->subject));
			}
		}
		catch (Exception $e)
		{
			die('Brak połączenia z serwerem wyszukiwarki');
		}

		echo implode('', array_slice($html, 0, 10));
		exit;
	}
}
?>