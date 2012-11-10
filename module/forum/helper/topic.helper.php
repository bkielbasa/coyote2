<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Topic
{
	const I_SUBMIT				= 'Utworzenie nowego wątku';
	const I_LOCK				= 'Zablokowanie wątku';
	const I_UNLOCK				= 'Odblokowanie wątku';
	const I_MOVE				= 'Przeniesienie wątku';
	const I_EDIT				= 'Edycja postu';
	const I_DELETE				= 'Usunięcie postu';
	const I_REPLY				= 'Napisano odpowiedź w temacie';
	const I_MERGE				= 'Połączenie postów';
	const I_SUBJECT				= 'Zmiana tytułu wątku';
	const I_STICKY				= 'Przyklejenie/Odklejenie wątku';
	const I_ANNOUNCEMENT		= 'Ustawienie ogłoszenia';

	/**
	 * Metoda generujaca 'pagination' (stronnicowanie) przy tematach
	 * bazujac na ilosci postow w odpowiedzi w temacie
	 * Celowo nie wykorzystano do tego klasy Pagination z uwagi na
	 * optymalizacje procesu generowania
	 */
	public static function pagination($baseUrl, $totalItems, $itemsPerPage = 10)
	{
		$itemsPerPage = max(10, $itemsPerPage);

		$output = '';
		$totalPages = ceil($totalItems / $itemsPerPage);

		$iteration = 1;
		for ($i = 0; $i < $totalItems; $i += $itemsPerPage)
		{
			$output .= Html::a($baseUrl . '?start=' . $i, $iteration);

			if ($iteration == 1 && $totalPages > 4)
			{
				$output .= ' ... ';
				$iteration = $totalPages - 3;
				$i += ($totalPages - 4) * $itemsPerPage;
			}
			else if ($iteration < $totalPages)
			{
				$output .= ' ';
			}

			$iteration++;
		}

		return $output;
	}

	public static function retriveOnlineUsers(&$usersList, &$anonymousCounter)
	{
		if (User::$id == User::ANONYMOUS)
		{
			++$anonymousCounter;
		}
		else
		{
			$usersList[] = Html::a(url('@profile?id=' . User::$id), User::data('name'));
		}
		$robots = array();

		$path = &Core::getInstance()->page->getLocation();
		foreach (User::getUsersSession($path . '*') as $row)
		{
			if ($row['session_ip'] != User::$ip)
			{
				if ($row['session_user_id'] > User::ANONYMOUS)
				{
					$usersList[] = Html::a(url('@profile?id=' . $row['session_user_id']), $row['user_name'],
						array('title' => 'Data logowania: ' . User::date($row['session_start']))
					);
				}
				elseif ($row['session_robot'])
				{
					if (!isset($robots[$row['session_robot']]))
					{
						$robots[$row['session_robot']] = 1;
					}
					else
					{
						$robots[$row['session_robot']]++;
					}
				}
				else
				{
					++$anonymousCounter;
				}
			}
		}

		foreach ($robots as $robotName => $count)
		{
			if ($count == 1)
			{
				$usersList[] = $robotName;
			}
			else
			{
				$usersList[] = $robotName . ' (' . $count . 'x)';
			}
		}
	}

	public static function getAuthor(&$postUser, &$postUsername, &$userName)
	{
		if ($postUser == User::ANONYMOUS)
		{
			return $postUsername;
		}
		else
		{
			return Html::a(url('@profile?id=' . $postUser), $userName);
		}
	}

	private static function buildMenu($data, $mode)
	{
		$core = &Core::getInstance();
		$httpQueryArray = array();

		if (isset($core->input->get->q))
		{
			$httpQueryArray['q'] = $core->input->get['q'];
		}
		if (isset($core->input->get->tag))
		{
			$httpQueryArray['tag'] = $core->input->get['tag'];
		}
		if (isset($core->input->get->user))
		{
			$httpQueryArray['user'] = $core->input->get['user'];
		}

		$output = '';

		foreach ($data as $_mode => $rowset)
		{
			$httpQuery = '';

			if ($mode == $_mode)
			{
				$httpQueryTmp = $httpQueryArray;

				unset($httpQueryTmp['tag']);
				$httpQuery = http_build_query($httpQueryTmp);

				unset($httpQueryTmp);
			}
			else
			{
				$httpQuery = http_build_query($httpQueryArray);
			}

			$anchor = Html::a(url($core->page->getLocation()) . "?view=$_mode" . ($httpQuery ? "&amp;{$httpQuery}" : '') . "#$_mode", $rowset['title']);
			$output .= Html::tag('li', true, $_mode == $mode ? array('class' => 'focus', 'title' => $rowset['description']) : array('title' => $rowset['description']), $anchor);
		}

		return $output;
	}

	public static function buildMainMenu($mode)
	{
		$data = array(

			'category'		=> array(

					'description'					=> 'Kategorie forum',
					'title'							=> 'Kategorie'
			),
			'all'			=> array(

					'description'					=> 'Wszystkie tematy',
					'title'							=> 'Wszystkie'
			),
			'unanswered'	=> array(

					'description'					=> 'Tematy na które nikt nie udzielił odpowiedzi',
					'title'							=> 'Bez odpowiedzi'
			),
			'votes'			=> array(

					'description'					=> 'Tylko wartościowe tematy (z liczbą głosów >= 0)',
					'title'							=> 'Wartościowe'
			)
		);

		if (User::$id > User::ANONYMOUS)
		{
			$data['mine'] = array(

				'description'						=> 'Wątki mojego autorstwa lub te w których brałem udział',
				'title'								=> 'Moje'
			);
//			$data['unread'] = array(
//
//					'description'					=> 'Wyświetli nieczytane (nowe) tematy',
//					'title'							=> 'Nieczytane'
//			);
		}

		$input = &Load::loadClass('input');

		if (isset($input->get->user) && $input->get->view == 'user')
		{
			$user = new User_Model;
			$userName = $user->select('user_name')->where('user_id = ?', $input->get->user)->fetchField('user_name');

			$data['user'] = array(

				'description'						=> "Wątki autorstwa użytkownika $userName lub te w których brał udział",
				'title'								=> "Posty użytkownika $userName"
			);
		}

		return self::buildMenu($data, $mode);
	}

	public static function buildForumMenu($mode)
	{
		$data = array(

			'all'			=> array(

					'description'					=> 'Wszystkie tematy',
					'title'							=> 'Wszystkie'
			),
			'unanswered'	=> array(

					'description'					=> 'Tematy na które nikt nie udzielił odpowiedzi',
					'title'							=> 'Bez odpowiedzi'
			)
		);

		$forumId = Core::getInstance()->page->getForumId();
		$isVotable = &Core::getInstance()->load->model('forum')->getAuth('f_vote', $forumId);

		if ($isVotable)
		{
			$data['votes'] = array(

					'description'					=> 'Tylko wartościowe tematy (z liczbą głosów >= 0)',
					'title'							=> 'Wartościowe'
			);
		}

		if (User::$id > User::ANONYMOUS)
		{
			$data['mine'] = array(

				'description'						=> 'Wątki mojego autorstwa lub te, w których brałem udział',
				'title'								=> 'Moje'
			);
//			$data['unread'] = array(
//
//					'description'					=> 'Wyświetli nieczytane (nowe) tematy',
//					'title'							=> 'Nieczytane'
//			);
		}

		return self::buildMenu($data, $mode);
	}

	public static function buildTopicMenu($sort)
	{
		$output = '';

		$data = array(

			'oldest'		=> array(

					'description'					=> 'Wyświetl odpowiedzi od najstarszego do najmłodszego',
					'title'							=> 'Chronologicznie'
			),
			'votes'			=> array(

					'description'					=> 'Wyświetl odpowiedzi według liczby głosów',
					'title'							=> 'Wartościowo'
			)
		);

		$core = &Core::getInstance();
		$httpQuery = '';

		if (isset($core->input->get->start))
		{
			$httpQuery = http_build_query(array('start' => (int) $core->input->get->start));
		}

		foreach ($data as $_mode => $rowset)
		{
			$anchor = Html::a(url($core->page->getLocation()) . "?sort=$_mode" . ($httpQuery ? "&amp;{$httpQuery}" : '') . "#$_mode", $rowset['title']);
			$output .= Html::tag('li', true, $_mode == $sort ? array('class' => 'focus', 'title' => $rowset['description']) : array('title' => $rowset['description']), $anchor);
		}

		return $output;
	}
}
?>