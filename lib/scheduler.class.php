<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa Scheduler wywolywana jest przez aplikacje cron do realizacji cyklicznych dzialan
 * ustalony w konfiguracji projektu
 */
class Scheduler
{
	/**
	 * Metoda wywyolywana jest przez trigger system.onBeforeSystem
	 * @static
	 */
	public static function load()
	{
		/**
		 * Dalsze czynnosci zostana wykonane tylko wowczas, gdy zadeklarowana
		 * jest stala IN_CRON
		 */
		if (!defined('IN_CRON'))
		{
			return false;
		}
		$core = &Core::getInstance();

		/**
		 * Inicjalizacja podstawowych klas projektu
		 */
		$core->load = new Load(array('library' => array('context', 'input')));
		$input = &Load::loadClass('input');
		/**
		 * Zaladowanie listy modulow wlaczonych w projekcie (ID, nazwy)
		 */
		Load::loadClass('module')->getModules();

		/*
		 * Zabezpieczenie przed nieautoryzowanym dostepem
		 */
		if (Config::getItem('scheduler') !== $input->get->key)
		{
			die('Hacking attempt...');
		}

		$scheduler = new Scheduler_Model;

		foreach ($scheduler->getJobs() as $row)
		{
			try
			{
				$scheduler->setLock($row['scheduler_id']);

				$object = new $row['scheduler_class'];
				$object->$row['scheduler_method']();
				$scheduler->setLunch($row['scheduler_id']);

				Log::add('Zadanie #' . $row['scheduler_id'] . ' wykonane prawidłowo', E_CRON);
				$scheduler->setUnlock($row['scheduler_id']);
			}
			catch (Exception $e)
			{
				$scheduler->setUnlock($row['scheduler_id']);

				Log::add('Zadanie #' . $row['scheduler_id'] . '. Błąd: ' . $e->getMessage(), E_CRON);
				file_put_contents(Config::getBasePath() . 'log/error.log', sprintf("%s: %s\n", date('d-m-Y H:i:s', time()), $e->getMessage()), FILE_APPEND | LOCK_EX);

				echo $e->getMessage();
			}
		}

		Trigger::call('system.onShutdown');
		exit;
	}
}
?>