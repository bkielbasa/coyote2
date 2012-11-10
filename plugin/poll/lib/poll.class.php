<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Poll extends Plugin
{
	private $stylesheet = 'plugin/poll/template/css/poll.css';

	public function setStylesheet($stylesheet)
	{
		$this->stylesheet = $stylesheet;
	}

	public function getStylesheet()
	{
		return $this->stylesheet;
	}

	public function display()
	{
		if (!$itemId = $this->getItem())
		{
			return;
		}

		$poll = &$this->getModel('poll');
		if (!$result = $poll->find($itemId)->fetchAssoc())
		{
			return;
		}
		/*
		 * Jezeli ankieta jest wylaczona - nie jest nawet wyswietlana
		 */
		if (!$result['poll_enable'])
		{
			return;
		}

		$view = new View('_partialPoll');
		/*
		 * Obliczanie daty wygasniecia ankiety.
		 * Jezeli poll_length ma wartosc 0 - ankieta nie wygasa nigdy
		 */
		$view->hasExpired = $result['poll_length'] > 0 ? (time() > $result['poll_start'] + $result['poll_length']) : false;

		if ($this->input->isPost())
		{
			if (!$view->hasExpired)
			{
				if ($this->post->pollItem)
				{
					if (($error = $this->isValid($result)) !== true)
					{
						$view->error = $error;
					}
					else
					{
						$result['poll_votes'] += count($this->post->pollItem);
					}
				}
			}
		}

		$view->assign($result);
		$view->componentName = $result['poll_max_item'] > 1 ? 'checkbox' : 'radio';

		$query = $poll->item->fetch("item_poll = $itemId");
		foreach ($query as $row)
		{
			$row['percentage'] = $result['poll_votes'] ? round(100 * $row['item_total'] / $result['poll_votes']) : 0;

			$view->append('items', $row);
		}

		/*
		 * Zmienna okresla, czy na dana ankiete oddano juz glos (True), czy tez nie (False)
		 */
		$view->hasVoted = false;
		$cookies = unserialize($this->input->cookie('poll'));

		if (isset($cookies[$result['poll_id']]))
		{
			$view->hasVoted = true;
		}

		if (!$view->hasVoted)
		{
			$view->hasVoted = $poll->vote->hasVoted($result['poll_id'], User::$id, User::$ip);
		}

		if ($this->getStylesheet() !== false)
		{
			$view->stylesheet = $this->getStylesheet();
		}

		echo $view;
	}

	private function isValid(&$data)
	{
		if (sizeof($this->input->post['pollItem']) > $data['poll_max_item'])
		{
			return 'Liczba możliwych opcji do oddania: ' . $data['poll_max_item'];
		}

		$cookies = unserialize($this->input->cookie('poll'));

		if (isset($cookies[$data['poll_id']]))
		{
			return 'Oddałeś już głos w tej ankiecie';
		}
		$poll = &$this->load->model('poll');

		if ($poll->vote->hasVoted($data['poll_id'], User::$id, User::$ip))
		{
			return 'Oddałeś już głos w tej ankiecie';
		}

		$poll->vote->setVote($data['poll_id'], User::$id, $this->input->post->pollItem);

		$cookies[$data['poll_id']] = true;
		$this->getContext()->output->setCookie('poll', serialize($cookies), time() + Time::MONTH);

		return true;
	}
}
?>