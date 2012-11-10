<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Report extends Adm
{
	function main()
	{
		$this->reportTime = array(
			0			=> 'Wszystkie wpisy',
			360			=> 'Ostatnia godzina',
			8640		=> 'Ostatnie 24 godz.',
			60480		=> 'Ostatni tydzień',
			120960		=> 'Ostatnie 2 tygodnie',
			241920		=> 'Ostatnie 4 tygodnie',
			1451520		=> 'Ostatnie 6 miesięcy',
			2903040		=> 'Ostatni rok'
		);

		$report = &$this->getModel('report');
		$module = &$this->getModel('module');

		$query = $module->select('module_id, module_text')->get();

		$this->modules = array(0 => 'Wszystkie moduły');
		$this->modules += $query->fetchPairs();

		Sort::setDefaultSort('report_id', Sort::DESC);

		$query = $this->db->select('SQL_CALC_FOUND_ROWS *')->from('report')
						  ->innerJoin('page', 'page_id = report_page')
						  ->innerJoin('module', 'module_id = page_module')
						  ->innerJoin('user', 'user_id = report_user')
						  ->innerJoin('location', 'location_page = page_id');

		if ($this->get->id)
		{
			$query->where('report_id', $this->get->id);
		}
		else
		{
			if ($this->get->module)
			{
				$query->where('page_module = ?', $this->get->module);
			}
			if ($this->get->time)
			{
				$query->where('report_time > ?', time() - $this->get->time);
			}
			if ($this->get->user)
			{
				$query->where('report_user IN(SELECT user_id FROM user WHERE user_name LIKE ?)', str_replace('*', '%', $this->get->user));
			}
			if ($this->get->email)
			{
				$query->where('report_email = ? OR user_email = ?', $this->get->email, $this->get->email);
			}
			if ($this->get->page)
			{
				$query->where('report_page = ?', $this->get->page);
			}
			if ($this->get->ip)
			{
				$query->like('report_ip', str_replace('*', '%', $this->get->ip));
			}
		}
		$query->order(Sort::getSortAsSQL());
		$query->limit((int) $this->get['start'], 20);

		$this->report = $query->get()->fetchAll();
		$sql = 'SELECT FOUND_ROWS() AS totalItems';
		$totalItems = $this->db->query($sql)->fetchField('totalItems');

		$this->pagination = new Pagination('', $totalItems, 20, (int)$this->get['start']);

		return true;
	}

	public function submit($id = 0)
	{
		$id = (int)$id;
		$report = &$this->getModel('report');

		$result = array();
		if (!$result = $report->find($id)->fetchAssoc())
		{
			throw new AcpErrorException('Raport o podanym ID nie istnieje!');
		}

		if ($this->input->isPost())
		{
			if ($result['report_close'])
			{
				throw new AcpErrorException('Raport jest zamknięty');
			}

			if ($this->post->content)
			{
				$email = &$this->getModel('email');
				$email->setValue($result);
				$email->setValue('content', $this->post->content);

				if ($result['report_user'] > User::ANONYMOUS)
				{
					$email->sendToUser('reportClose', $result['report_user']);
				}
				else
				{
					$email->send('reportClose', $result['report_email']);
				}
			}

			$report->update(array('report_close' => 1), 'report_id = ' . $id);
			$this->session->message = 'Raport został zamknięty';

			Log::add(null, E_REPORT_CLOSE, $result['report_page']);

			$this->redirect('adm/Report');
		}

		$user = &$this->getModel('user');
		$result = array_merge($result, $user->find($result['report_user'])->fetchAssoc());

		$query = $this->db->select()->where('page_id = ' . $result['report_page'])->leftJoin('location', 'location_page = page_id')->get('page');
		$result = array_merge($result, $query->fetchAssoc());

		if ($result['report_close'])
		{
			$log = &$this->getModel('log');

			$query = $log->select('log.*, user_name AS report_user')->leftJoin('user', 'user_id = log_user')
						 ->where('log_type = ' . E_REPORT_CLOSE . ' AND log_page = ' . $result['report_page'])
						 ->order('log_id DESc')
						 ->limit(0, 1)
						 ->get();

			if (count($query))
			{
				$result = array_merge($result, $query->fetchAssoc());
			}
		}

		if ($result['report_anchor'])
		{
			$result['location_text'] .= '?' . $result['report_anchor'];
		}

		return View::getView('adm/reportSubmit', $result);
	}
}
?>