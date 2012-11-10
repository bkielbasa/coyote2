<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Filter_Controller extends Adm
{
	function main()
	{
		$this->pageTime = array(
			0			=> 'Wszystkie wpisy',
			Time::HOUR	=> 'Ostatnia godzina',
			Time::DAY	=> 'Ostatnie 24 godz.',
			Time::WEEK	=> 'Ostatni tydzień',
			Time::WEEK * 2	=> 'Ostatnie 2 tygodnie',
			Time::MONTH	=> 'Ostatnie 4 tygodnie',
			Time::MONTH * 6	=> 'Ostatnie 6 miesięcy',
			Time::YEAR	=> 'Ostatni rok'
		);

		Sort::setDefaultSort('page_edit_time', Sort::DESC);

		$page = &$this->getModel('page');
		$start = (int) $this->get['start'];

		$this->result = $page->filter($this->get->pageId, $this->get->time, $this->get->subject, $this->get->location, $this->get->editTime, $this->get->user, $this->get->ip, Sort::getSortAsSQL(), $start, 25)->fetchAll();
		$this->pagination = new Pagination('', $page->getFoundRows(), 25, $start);

		return true;
	}
}
?>