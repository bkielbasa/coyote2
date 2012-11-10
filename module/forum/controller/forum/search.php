<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Search_Controller extends Page_Controller
{
	private $params = array();
	private $fields = array();
	private $filters = array();

	function main()
	{
		$this->query = trim($this->get['q']);
		$userName = $userId = null;

		$this->note = $this->error = '';

		if ($this->input->isAjax())
		{
			if (isset($this->get->mode))
			{
				$this->__finduser();
			}
			else
			{
				$this->__suggestions($this->query);
			}
			exit;
		}

		$this->qaa = $this->qf = $this->qa = $this->qn = '';

		if (!$this->query)
		{
			/*
			 * Wszystkie slowa...
			 */
			if (isset($this->get->qaa))
			{
				$this->qaa = preg_split('/\s+|"\b/', $this->get['qaa']);
			}
			/*
			 * Frazy. Cale wyrazenie tego pola, musi zostac zawarte w znaki cudzlowia (")
			 */
			if (isset($this->get->qf))
			{
				// jezeli user wpisal recznie cudzyslowia - usuwamy
				$this->qf = str_replace('"', '', trim($this->get->qf));

				if ($this->qf)
				{
					$this->qf = '"' . $this->qf . '"';
				}
			}
			/*
			 * Dowolne z slow. Nalezy rozdzielic wszystkie slowa i wstawic pomiedzy nimi slowo kluczowe "OR"
			 */
			if (isset($this->get->qa))
			{
				$words = array();
				foreach (preg_split('/\s+|\b/', $this->get['qa']) as $word)
				{
					if ($word != 'or' && $word != 'OR' && trim($word) != '')
					{
						$words[] = $word;
					}
				}

				if ($words)
				{
					$this->qa = $words; // przypisanie tablicy
				}
			}
			if (isset($this->get->qn))
			{
				if (trim($this->get->qn))
				{
					$words = array();
					foreach (preg_split('/\s+|\b/', $this->get['qn']) as $word)
					{
						if (trim($word))
						{
							$word = str_replace(array('-', '+'), '', $word);
							$words[] = $word;
						}
					}

					if ($words)
					{
						$this->qn = $words; // przypisanie tablicy
					}
				}
			}
		}

		$query = array();

		// tablica slow ktore moga wystapic w poscie (OR)
		if ($this->qa)
		{
			$value = implode(' OR ', $this->qa);

			if ($this->qaa || $this->qf || $this->qn)
			{
				$value = '(' . $value . ')';
			}

			if ($this->qn)
			{
				$value = '+' . $value;
			}

			$query[] = $value;
			$this->qa = implode(' ', $this->qa);
		}

		// zadne z tych slow
		if ($this->qn)
		{
			foreach ($this->qn as $k => $v)
			{
				$query[] = '-' . $v;
			}

			$this->qn = implode(' ', $this->qn);
		}

		// fraza
		if ($this->qf)
		{
			if ($this->qn)
			{
				$query[] = '+' . $this->qf;
			}
			else
			{
				$query[] = $this->qf;
			}
		}

		if ($this->qaa)
		{
			$this->qaa = trim(implode(' ', $this->qaa));
			$query[] = $this->qaa;

		}

		if ($query)
		{
			$this->query = implode(' ', $query);
		}

		if (Text::length($this->query) > 100)
		{
			$this->query = Text::limit($this->query, 100);
			$this->error = 'Maksymalna długość zapytania do wyszukiwarki może wynosić 100 znaków';
		}

		// domyslne ustawienia "waznosci" wyszukiwania w poszczegolnych polach
		$this->defaultBoost = array('subject' => '10.0', 'text' => '5.0', 'tag' => '1.0', 'comment' => '0.8');
		// sortowanie
		$this->sortList = array('score' => 'Trafność wyników', 'date' => 'Data napisania');
		$this->orderList = array('desc' => 'Malejąco', 'asc' => 'Rosnąco');

		if ($this->query || !empty($this->get['user']) || !empty($this->get['tag']) || (!empty($this->get['ip']) && Auth::get('a_')))
		{
			$forum = &$this->getModel('forum');

			if (!$perPage = (int) $forum->setting->getPostsPerPage())
			{
				$perPage = 20;
			}

			/**
			 * Walidacja liczby: ilosc postow na strone
			 */
			$perPage = max(10, min($perPage, 50));
			$userName = null;
			$userId = null;
			$userIp = $this->get->ip;
			$topicId = (int) $this->get->t;

			if (preg_match('~user:([0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ.=:|#_ ()[\]^-]+)$~i', $this->query, $match))
			{
				$userName = $match[1];
			}
			else if (isset($this->get->user))
			{
				$userName = $this->get['user'];
			}
			$userName = htmlspecialchars(trim($userName));

			$user = &$this->getModel('user');

			Load::loadFile('lib/validate.class.php', false);
			$validate = new Validate_User(false, false, false);

			if ($validate->isValid($userName))
			{
				$userId = $user->getByName($userName)->fetchField('user_id');
			}
			else
			{
				$this->note = 'Użytkownik <strong>' . $userName . '</strong> nie istnieje lub został usunięty. Poszukiwane są posty użytkownika niezarejestrowanego';
			}

			if (isset($this->get->boost))
			{
				foreach ($this->get->boost as $column => $value)
				{
					$value = (float) $value;

					if ($value >= 0.5 && $value <= 10.0)
					{
						$this->defaultBoost[$column] = number_format($value, 2);
					}
				}
			}

			if (!isset($this->get->in))
			{
				$this->addField('subject', $this->defaultBoost['subject']);
				$this->addField('text', $this->defaultBoost['text']);
				$this->addField('comment_text', $this->defaultBoost['comment']);
			}
			else
			{
				if (in_array('subject', $this->get->in))
				{
					$this->addField('subject', $this->defaultBoost['subject']);
					$this->addField('location', '1.5');
				}

				if (in_array('text', $this->get->in))
				{
					$this->addField('text', $this->defaultBoost['text']);
				}

				if (in_array('tag', $this->get->in))
				{
					$this->addField('tag', $this->defaultBoost['tag']);
				}

				if (in_array('comment', $this->get->in))
				{
					$this->addField('comment_text', $this->defaultBoost['comment']);
				}
			}

			$this->addParam('defType', 'edismax');
			$this->addParam('spellcheck.dictionary', 'default');
			$this->addParam('tie', '0.1');
//			$this->addParam('ps', 4);

			$qf = $pf = '';
			foreach ($this->getFields() as $field => $boosts)
			{
				$qf .= $field . ' ';
				$pf .= $field . ($boosts != 1.0 ? "^$boosts " : ' ');
			}

			/*
			 * Query fields. Lista pol po ktorych odbedzie sie wyszukiwanie
			 */
			$this->addParam('qf', rtrim($pf));
//			$this->addParam('pf', rtrim($pf));
			$this->addParam('qt', 'standard'); // typ wyszukiwania

			$this->addParam('hl', 'true');
			$this->addParam('hl.fl', 'subject,text,comment_text');
			$this->addParam('hl.snippets', 2);
			$this->addParam('hl.fragsize', 400);
			$this->addParam('hl.simple.pre', '<strong>');
			$this->addParam('hl.simple.post', '</strong>');

			$forums = $this->get->f;

			if ($forums)
			{
				if (is_array($forums))
				{
					foreach ($forums as $forumId)
					{
						$this->addFilter('forum_id', (int) $forumId);
					}
				}
				else
				{
					$this->addFilter('forum_id', (int) $forums);
				}
			}

			$tag = trim($this->get->tag);

			if ($tag)
			{
				$this->addFilter('tag', $tag);
			}

			if ($topicId)
			{
				$this->addFilter('topic_id', $topicId);
			}

			if ($userId)
			{
				$this->addFilter('user_id', $userId);
			}
			elseif ($userName)
			{
				$this->addFilter('username', $userName);
			}

			if (!$topicId && !$userId && !$userName)
			{
				$this->addParam('group', 'true');
				$this->addParam('group.field', 'topic_id');
				$this->addParam('group.main', 'false');
				$this->addParam('group.ngroups', 'true');
			}

			if (isset($this->get->firstPost))
			{
				$this->addFilter('first_post', true);
			}

			if ($userIp && Auth::get('a_'))
			{
				$this->addFilter('ip', $userIp);
			}

			$user = &$this->getModel('user');

			$group = array();
			foreach ($user->getGroups() as $groupId)
			{
				$group[] = 'group:' . $groupId;
			}

			if ($group)
			{
				$this->addParam('fq', implode(' OR ', $group));
			}

			if (isset($this->get->sort))
			{
				$sort = $this->get->sort == 'score' ? 'score' : 'timestamp';
				$order = $this->get->order == 'desc' ? 'desc' : 'asc';

				$this->addParam('sort', $sort . ' ' . $order);
			}
			$this->addParam('fl', '*,score');
//			$this->addParam('sort', 'vote desc');
//			$this->addParam('bf', 'sqrt(vote)^10');
//			$this->addParam('bf', 'sub(timestamp,' . time() . ')^2');

			foreach ($this->getFilters() as $name => $values)
			{
				if (count($values) <= 1)
				{
					$this->addParam('fq', $name . ':' . $values[0]);
				}
				else
				{
					$filter = array();

					foreach ($values as $value)
					{
						$filter[] = "$name:$value";
					}

					$this->addParam('fq', implode(' OR ', $filter));
				}
			}

			$solr = new Solr(Config::getItem('forum.solr.host'), Config::getItem('forum.solr.port'), Config::getItem('forum.solr.path'));
			$solr->enableSuggestion(true);

			$this->hits = array();

			if (!$this->query)
			{
				$this->query = '*:*';
			}
			else
			{
//				$this->query = '{!boost b=log(vote)}' . $this->query;
			}

			$hits = $solr->search($this->query, (int) $this->get['start'], $perPage, $this->getParams());

			if ($hits->getSuggestion())
			{
				parse_str($this->input->server('QUERY_STRING'), $queryString);

				$queryString['q'] = $hits->getSuggestion();
				$this->suggestion = Html::a(url($this->page->getLocation()) . '?' . http_build_query($queryString, '', '&amp;'), $hits->getSuggestion());
			}

			foreach ($hits as $hit)
			{
				$subject = isset($hit->highlight['subject']) ? $hit->highlight['subject'][0] : $hit->subject;

				if (isset($hit->highlight['text']))
				{
					$text = strip_tags(implode(' ... ', $hit->highlight['text']), '<strong>');
				}
				else
				{
					$text = Text::limit($hit->getField('text', 1), 400);
				}

				$breadcrumb = array();
				$segments = url('@forum') . '/';

				$parts = explode('/', $hit->location);
				array_shift($parts);
				array_pop($parts);

				foreach ($parts as $part)
				{
					$segments .= $part . '/';
					$breadcrumb[] = Html::a(url(trim($segments, '/')), Text::humanize($part));
				}

				array_unshift($breadcrumb, Html::a(url('@forum'), 'Forum'));

				$this->hits[$hit->id] = array(
					'url'				=> url($hit->location) . '?p=' . $hit->id . '#id' . $hit->id,
					'location'			=> isset($hit->highlight['location']) ? $hit->highlight['location'][0] : $hit->location,
					'page_subject'		=> $subject,
					'post_text'			=> $text,
					'post_topic'        => $hit->topic_id,
					'breadcrumb'        => implode(' » ', $breadcrumb),
					'score'             => $hit->score
				);
			}

			$userIds = array();
			$this->onlineUsers = $this->comments = array();

			if ($this->hits)
			{
				$post = &$this->getModel('post');
				$query = $post->select('post_id, post_username, post_vote, post_time, post_user, user_id, user_allow_count, user_lastvisit, user_post, user_name, user_regdate, topic_views, topic_replies, topic_solved')->innerJoin('topic', 'topic_id = post_topic')->leftJoin('user', 'user_id = post_user')->in('post_id', array_keys($this->hits))->get();
				$userIds = array();

				foreach ($query as $row)
				{
					$this->hits[$row['post_id']] = array_merge($this->hits[$row['post_id']], $row);

					if ($row['user_id'] > User::ANONYMOUS)
					{
						$userIds[] = $row['user_id'];
					}
				}

				$splitKeywords = preg_split("/[\s,\+\.]+/", $this->query);

				foreach ($post->comment->getComments(array_keys($this->hits)) as $row)
				{
					if ($row['comment_text'] != ($highlight = $this->highlight($splitKeywords, $row['comment_text'])))
					{
						$row['comment_text'] = $highlight;
						$this->comments[$row['comment_post']][] = $row;
					}
				}
			}

			if ($userIds)
			{
				$this->onlineUsers = $this->db->select('session_user_id, session_stop')->from('session')->in('session_user_id', array_unique($userIds))->fetchPairs();
			}

			$this->pagination = new Pagination('', $hits->getFoundRows(), $perPage, (int) $this->get['start']);
		}

		$view = parent::main();

		$forum = &$this->getModel('forum');
		$depth = $forum->getPageDepth($this->page->getParentId());

		$this->htmlForumList = array(' ' => '-----');
		$this->categoryList = array();

		foreach ($forum->getHtmlList() as $url => $value)
		{
			$this->htmlForumList[url($url)] = $value;
		}

		$query = $forum->getList()->get();

		foreach ($query as $row)
		{
			$this->categoryList[$row['forum_id']] = str_repeat('&nbsp;', 5 * ($row['page_depth'] - $depth - 1)) . $row['page_subject'];
		}

		if ($this->query)
		{
			$this->query = htmlspecialchars($this->query);
			$this->declination = Declination::__($this->pagination->getTotalItems(), array('post', 'posty', 'postów'));

			if ($this->query == '*:*')
			{
				if ($this->get->tag)
				{
					$title = 'Post zawierający tag "' . $this->get->tag . '"';
				}
				else
				{
					$title = 'Posty użytkownika "' . $userName . '"';
				}
			}
			else
			{
				$title = $this->pagination->getTotalItems() . ' ' . $this->declination . ' dla "' . $this->query . '" ' . ($userId != null ? ' (posty użytkownika "' . $userName . '")' : '');
			}

			Breadcrumb::add(url($this->page->getLocation()) . '?q=' . $this->query, $title);
			$this->output->setTitle($title . ' :: ' . Config::getItem('site.title'));

			//echo Html::a($solr->getSolrUrl());
		}

		$this->boostList = array('10.0' => 'Najważniejsze', '5.0' => 'Ważne', '1.0' => 'Normalne', '0.8' => 'Najmniej ważne');

		return $view;
	}

	private function addParam($name, $value)
	{
		if (!isset($this->params[$name]))
		{
			$this->params[$name] = array();
		}

		$this->params[$name][] = $value;
	}

	private function getParams()
	{
		return $this->params;
	}

	private function addField($field, $boost = 1.0)
	{
		$this->fields[$field] = $boost;
	}

	public function getFields()
	{
		return $this->fields;
	}

	private function addFilter($filter, $value)
	{
		$value = preg_quote($value, '\\');

		if (!isset($this->filters[$filter]))
		{
			$this->filters[$filter] = array();
		}

		$this->filters[$filter][] = $value;
		return $this;
	}

	public function getFilters()
	{
		return $this->filters;
	}

	private function __suggestions($keywords)
	{
		if (!$keywords)
		{
			return;
		}

		$result = array();
		$queryString = 'suggestion:"' . $keywords . '"';

		try
		{
			$user = &$this->getModel('user');

			$group = array();
			foreach ($user->getGroups() as $groupId)
			{
				$group[] = 'group:' . $groupId;
			}

			$solr = new Solr(Config::getItem('forum.solr.host'), Config::getItem('forum.solr.port'), Config::getItem('forum.solr.path'));
			$hits = $solr->search($queryString, 0, 20, array('fl' => 'subject', 'fq' => implode(' OR ', $group) ));

			foreach ($hits as $hit)
			{
				$result[] = $hit->subject;
			}
		}
		catch (Exception $e)
		{
			echo 'Brak połączenia z serwerem wyszukiwarki';
			exit;
		}

		$html = '';
		$result = array_slice(array_unique($result), 0, 10);

		foreach ($result as $value)
		{
			$html .= Html::tag('li', true, array(), str_ireplace($keywords, '<b>' . $keywords . '</b>', $value));
		}

		echo $html;
		exit;
	}

	private function highlight($keywords, $content)
	{
		foreach ($keywords as $keyword)
		{
			if (preg_match('#\S+#u', $keyword))
			{
				$keyword = '\b' . preg_quote($keyword) . '\b';
				$content = preg_replace(sprintf('~(?!<.*?)(%s)(?![^<>]*?>)~i', $keyword), '<strong>$1</strong>', $content);
			}
		}

		return $content;
	}

	/**
	 * @todo Taki sam kod jest w pliku forum.php. Potrzebna refaktoryzacja
	 */
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
}
?>