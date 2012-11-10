<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Snippet wyswietlajacy ostatnie wizyty w serwisie w postacii listy HTML
 * Informacje beda zwarte w znacznikach <ul> oraz <li>, np.:
 * <ul>
 *	<li><a href="http://foo.com/Profile/1">Adam Boduch</a> <small>1 godz. temu</small></li>
 *	<li>Anonim <small>2 godz. temu</small></li>
 * </ul>
 */
class Snippet_Visit extends Snippet
{
	/**
	 * Limit wyswietlanych pozycji. Ilosc ostatnich wizyt jakie beda wyswietlane
	 */
	protected $limit = 10;
	/**
	 * Pole okresla czy data ma byc wyswietlana relatywnie w odniesieniu do obecnej daty
	 * Jezeli wartosc jest FALSE, data bedzie wyswietlana w formacie takim, jaki zostal
	 * zadeklarowany w profilu uzytkownika
	 */
	protected $relativeDate = true;

	/**
	 * Metoda wyswietlajaca snippet.
	 * @param object $instance Opcjonalnie instancja klasy implementujacej interfejs IView do ktorej
	 * zostana przekazane dane odczytane z bazy danych
	 */
	public function display()
	{
		$query = $this->db->select('log_stop, user_id, user_name')->from('session_log, user')->where('user_id = log_user');
		$query->order('log_stop DESC')->limit(0, (int) $this->limit);

		if (is_string($this->relativeDate))
		{
			$this->relativeDate = $this->relativeDate == 'true';
		}

		$xhtml = '<ul>';
		foreach ($query->get() as $row)
		{
			$xhtml .= '<li>' . ($row['user_id'] > User::ANONYMOUS ? Html::a(url('@profile?id=' . $row['user_id']), $row['user_name']) : $row['user_name']) . ' <small>' . 
				($this->relativeDate ? Time::diff($row['log_stop']) . ' temu' : User::formatDate($row['log_stop'])) . '</small></li>';
		}

		$xhtml .= '</ul>';
		echo $xhtml;
	}
}
?>