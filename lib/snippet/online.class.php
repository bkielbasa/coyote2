<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Snippet wyswietla liste uzytkownikow online wraz z liczba w postacii
 * Uzytkownicy online (<strong>N</strong): User1, User2
 * Snippet nie posiada opcjii konfiguracji
 */
class Snippet_Online extends Snippet
{
	/**
	 * Metoda wyswietlajaca snippet.
	 * @param object $instance Opcjonalnie instancja klasy implementujacej interfejs IView do ktorej
	 * zostana przekazane dane odczytane z bazy danych
	 */
	public function display(IView $instance = null)
	{
		/**
		 * Pobranie listy userow online wraz z liczba sesji
		 */
		$query = $this->db->select('COUNT(t1.session_id) AS sessions, t2.*, user_name')
					  ->from('session AS t1, session AS t2, user')
					  ->where('user_id = t2.session_user_id')
					  ->group('t1.session_id');
		if ($instance)
		{
			$instance->result = $query;
		}
		else
		{
			$query = $query->get();
			$result = array();
			
			foreach ($query->fetchAll() as $row)
			{
				$result[] = Html::a(url('@profile?id=' . $row['session_user_id']), $row['user_name']);
			}

			$xhtml = 'Użytkownicy online (<strong>' . $query->fetchField('sessions') . '</strong>): ';
			$xhtml .= implode(', ', $result);
		}

		echo $xhtml;
	}
}
?>