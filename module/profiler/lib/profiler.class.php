<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa sluzy do obliczania informacji na temat czasow generowania poszczegolnych stron
 */
class Profiler
{
	/**
	 * Metoda zapisuje w bazie danych informacje o czasach generowania 
	 * strony
	 */
	public static function set()
	{
		if (defined('IN_CRON'))
		{
			return false;
		}
		
		$core = &Core::getInstance();
		$core->load->model('profiler');
		
		// nalezy sprawdzic, czy profiler jest wlaczony w panelu administracyjnym
		if ($core->module->profiler('enable') == 0)
		{
			return false;
		}
		
		$sqlTime = 0;

		if (isset($core->db))
		{ 
			$profiler = Db_Profiler::getInstance();

			if ($profiler->getTotalNumQueries())
			{
				foreach ($profiler->get() as $query)
				{
					$sqlTime += (float) $query->getElapsedTime();	

					if ($core->module->profiler('enableSql') == '1')
					{
						$core->model->profiler->sql->insert($query->getQuery(), $query->getElapsedTime());
					}
				}
				
			}
		}
		
		$core->model->profiler->insert(array(
				'profiler_page'			=> User::getPage(),
				'profiler_sql'			=> number_format($sqlTime, 4),
				'profiler_time'			=> Benchmark::estimated()
			)
		);
	}
}
?>