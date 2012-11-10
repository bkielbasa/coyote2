<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Profiler_Controller extends Adm
{
	function main()
	{
		$profiler = &$this->load->model('profiler');

		if ($this->input->getMethod() == Input::POST)
		{
			$profiler->sql->delete();
			$profiler->delete();
		}
		$this->load->helper('sort');
		// ustawia domslna kolejnosc sortowania
		Sort::setDefaultSort('profiler_time', Sort::DESC);

		$start = (int)$this->input->get->start;
		$stop = 10;
		
		$result = $profiler->fetch(null, Sort::getSortAsSQL(), $start, $stop)->fetch();
		$total = $this->db->query('SELECT FOUND_ROWS() AS total')->fetchField('total');

		$query_arr = array(				
				'sort'				=> $this->input->get->sort,
				'order'				=> $this->input->get->order(Sort::DESC),
		);

		$this->sql = $profiler->sql->fetch(null, 'sql_time DESC', 0, 10)->fetchAll();
		
		return $this->load->view('adm/profiler', array(
				'profiler'			=> $result,
				'pagination'		=> new Pagination('', $total, 10, $start),
				'total_page'		=> ceil($total / 10)
			)
		);
	}
}
?>