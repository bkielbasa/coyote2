<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Log_Model extends Model
{
	protected $name = 'log';
	protected $prefix = 'log_';
	protected $primary = 'log_id';

	public function getLogTypes()
	{
		$logTypes = array(
			E_ERROR						=> 'Błąd podczas działania systemu',
			E_UCP_LOGIN					=> 'Logowanie do systemu',
			E_UCP_LOGIN_FAILED			=> 'Nieudane logowanie do systemu',
			E_ACP_LOGIN					=> 'Logowanie do panelu administracyjnego',
			E_ACP_LOGIN_FAILED			=> 'Nieudane logowanie do panelu administracyjnego',
			E_REGISTER					=> 'Rejestracja w systemie',
			E_CONFIRM					=> 'Potwierdzenie adresu e-mail',
			E_PAGE_SUBMIT				=> 'Utworzenie/edycja strony',
			E_PAGE_DELETE				=> 'Usunięcie strony',
			E_PAGE_MOVE					=> 'Przeniesienie strony',
			E_PAGE_COPY					=> 'Skopiowanie strony',
			E_PAGE_RESTORE				=> 'Przywrócenie usuniętej strony',
			E_PAGE_PURGE				=> 'Opróżnienie kosza',
			E_REPORT_CLOSE				=> 'Zamknięcie raportu',
			E_USER_UPDATE				=> 'Uaktualnienie informacji w profilu',
			E_PM_SUBMIT					=> 'Wysłanie grupowej wiadomości prywatnej',
			E_USER_UPDATE				=> 'Uaktualnienie profilu użytkownika',
			E_BAN_SUBMIT				=> 'Utworzenie/modyfikacja blokady użytkownika',
			E_CRON                      => 'Zadania cykliczne'
		);

		$query = $this->select('log_type')->where('log_type NOT IN(' . implode(',', array_keys($logTypes)) . ')')->group('log_type')->get();
		foreach ($query as $row)
		{
			$logTypes[$row['log_type']] = $row['log_type'];
		}

		return $logTypes;
	}

	public function filter($logId = null, $logType = null, $logTime = null, $logUser = null, $logIp = null, $pageId = null, $order = null, $count = null, $limit = null)
	{
		$query = $this->select('log.*, user_name, page_id, page_subject, location_text');
		$query->leftJoin('user', 'user_id = log_user');
		$query->leftJoin('page', 'page_id = log_page');
		$query->leftJoin('location', 'location_page = log_page');

		$where = array();

		if ($logId)
		{
			$where[] = 'log_id = ' . (int) $logId;
		}
		else
		{
			if ($pageId)
			{
				$where[] = 'log_page = ' . (int) $pageId;
			}
			if ($logType)
			{
				$where[] = 'log_type IN(' . implode(',', array_map(array('Text', 'quote'), (array) $logType)) . ')';
			}
			if ($logTime)
			{
				$where[] = 'log_time > ' . (time() - $logTime);
			}
			if ($logUser)
			{
				if (is_numeric($logUser))
				{
					$where[] = 'log_user = ' . (int) $logUser;
				}
				else
				{
					$where[] = 'log_user IN(SELECT user_id FROM user AS t1 WHERE t1.user_name LIKE "' . str_replace('*', '%', $logUser) . '")';
				}
			}
			if ($logIp)
			{
				$where[] = 'log_ip LIKE "' . str_replace('*', '%', $logIp) . '"';
			}
		}

		foreach ($where as $syntax)
		{
			$query->where($syntax);
		}

		if ($order)
		{
			$query->order($order);
		}
		if ($count || $limit)
		{
			$query->limit($count, $limit);
		}

		$result = $query->get()->fetchAll();

		$query = $this->select('COUNT(*)');
		foreach ($where as $syntax)
		{
			$query->where($syntax);
		}

		$this->foundRows = $query->fetchField('COUNT(*)');
		return $result;
	}

	public function getFoundRows()
	{
		return $this->foundRows;
	}

	/**
	 * Zwraca informacje o ostatnim komunikacie zapisanym w dzienniku, ktory jest przypisany do danej strony
	 *
	 * @param $pageId   ID strony
	 * @param $logType  ID typu komunikatu
	 * @return mixed
	 */
	public function getLastLogMessage($pageId, $logType)
	{
		$query = $this->select('user_name, log_user, log_time, log_message')
					  ->leftJoin('user', 'user_id = log_user')
					  ->where('log_page = ' . $pageId . ' AND log_type = "' . $logType . '"')
					  ->order('log_id DESC')
					  ->limit(1);

		return $query->fetchAssoc();
	}
}
?>