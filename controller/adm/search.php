<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

set_time_limit(0);

class Search_Controller extends Adm
{
	function main()
	{
		$search = &$this->getModel('search');

		if ($this->input->isPost())
		{
			$this->db->update('search', array('search_enable' => 0));

			foreach ($this->post['enable'] as $id => $value)
			{
				$this->db->update('search', array('search_enable' => (bool) $value, 'search_default' => 0), 'search_id = ' . $id);
			}

			if ($this->post->default)
			{
				$this->db->update('search', array('search_default' => 1), 'search_id = ' . $this->post->default);
			}
			$this->redirect('adm/Search');
		}

		$this->search = $search->fetch()->fetchAll();
		return true;
	}

	public function index()
	{
		$this->freqList = array(
			0						=> 'Indeksowanie wyłączone',
			Time::MINUTE			=> '1 minuty',
			Time::MINUTE * 5		=> '5 minut',
			Time::MINUTE * 10		=> '10 minut',
			Time::MINUTE * 30		=> '30 minut',
			Time::HOUR				=> '1 godz.',
			Time::DAY				=> '1 dnia',
		);
		$scheduler = &$this->getModel('scheduler');

		if ($this->input->isPost())
		{
			if (isset($this->post->freq))
			{
				$freq = (int) $this->post['freq'];

				if (!$scheduler->setFrequency('indexQueue', $freq))
				{
					$scheduler->insert($this->module->getId('main'), 'indexQueue', 'search', 'buildIndex', $freq);
				}
				$this->redirect('adm/Search/Index');
			}

			if (isset($this->post->mode))
			{
				$search = new Search;

				if ($this->post->mode == 1)
				{
					$search->addDocument((int) $this->post->pageId);
					$this->session->message = 'Strona została dodana do indeksu';
				}
				elseif ($this->post->mode == 0)
				{
					$search->delete((int) $this->post->pageId);
					$this->session->message = 'Strona została usunięta z indeksu';
				}
			}

			if (isset($this->post->action))
			{
				$search = new Search;

				switch ($this->post->action)
				{
					case 1:

						$pageIds = $this->db->select('page_id')->order('timestamp DESC')->group('page_id')->get('search_queue')->fetchCol();
						$search->addDocuments($pageIds);

						$this->db->delete('search_queue');

						$this->session->message = 'Strony zostały zindeksowane';
						break;

					case 2:

						$search->deleteAll();

						$this->session->message = 'Indeks został usunięty';
						break;

					case 3:

						$pageIds = $this->db->select('page_id')->from('page')->fetchCol();
						$search->addDocuments($pageIds);

						$this->session->message = 'Dokonano ponownej indeksacji';
						break;

					case 4:

						$search->optimize();
						$this->session->message = 'Zoptymalizowano indeks';
						break;
				}

				$this->redirect('adm/Search/Index');
			}
		}
		$search = &$this->getModel('search');
		$index = new Search_Queue_Model;

		$this->queue = $index->fetch(null, 'timestamp')->fetchAll();
		$this->queueCount = $index->getTotalItems();

		$this->isSearchEnabled = $search->getEnabledSearch();

		return true;
	}

	public function top10()
	{
		$this->top10 = $this->db->select()->from('search_top10')->order('top10_weight DESC')->limit(30)->fetchAll();

		return true;
	}
}
?>