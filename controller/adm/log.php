<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Log_Controller extends Adm
{
	function main()
	{
		$log = &$this->getModel('log');

		$this->logType = array(0 => 'Wszystkie zdarzenia');
		$this->logType += $log->getLogTypes();

		$this->logTime = array(
			0			=> 'Wszystkie wpisy',
			Time::HOUR	=> 'Ostatnia godzina',
			Time::DAY	=> 'Ostatnie 24 godz.',
			Time::WEEK	=> 'Ostatni tydzień',
			Time::WEEK * 2	=> 'Ostatnie 2 tygodnie',
			Time::MONTH	=> 'Ostatnie 4 tygodnie',
			Time::MONTH * 6	=> 'Ostatnie 6 miesięcy',
			Time::YEAR	=> 'Ostatni rok'
		);

		Sort::setDefaultSort('log_id', Sort::DESC);

		if ($this->input->isPost())
		{
			if ($this->post->purge)
			{
				$where = array();
				$where[] = 'log_type = "' . $this->post->purge . '"';

				if ($this->post->logTime)
				{
					$where[] = 'log_time < ' . (time() - (int) $this->post->logTime);
				}

				$log->delete(implode(' AND ', $where));
				$this->message = 'Wpisy z zaznaczonej kategorii zostały usunięte';
			}
			elseif ($delete = $this->post->delete)
			{
				$log->delete('log_id IN(' . implode(',', $delete) . ')');
				$this->message = 'Zaznaczone rekordy zostały usunięte!';
			}
		}

		$user = $this->get['user'];
		Load::loadFile('lib/validate.class.php');

		if (!is_numeric($user))
		{
			$validate = new Validate_User(false, false, false);
			if (!$validate->isValid($user))
			{
				$user = null;
				$this->session->note = 'Nazwa użytkownika jest nieprawidłowa. Została pominięta w procesie wyszukiwania';
			}
		}

		$this->log = $log->filter($this->get->id, $this->get->type, $this->get->time, $user, $this->get->ip, null, Sort::getSortAsSQL(), (int)$this->get['start'], 25);
		$this->pagination = new Pagination('', $log->getFoundRows(), 25, (int)$this->get['start']);

		$this->purge = array(0 => '--');
		$this->purge += $log->getLogTypes();

		foreach ($this->log as $index => $row)
		{
			if ($row['log_type'] == E_USER_UPDATE)
			{
				// lame regexp... zamiana ID usera na dzialajcy link w opisie zdarzenia
				$row['log_message'] = preg_replace('~(.*?)#(\d+) \((.*)\)~', '$1 #$2 (<a href="' . Url::site() . 'adm/User/Submit/$2">$3</a>)', $row['log_message']);
				$this->log[$index] = $row;
			}
		}

		load_helper('array');
		return true;
	}
}
?>