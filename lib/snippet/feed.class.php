<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Snippet wyswietla liste ostatnich zmian w dokumentach w serwisie
 * Lista bedzie wyswietlana w postacii listy HTML - np.;
 * <ul>
 *	<li><a title="test" href="Test">Test</a> <small>1 godz. temu</small></li>
 *	<li><a title="foo" href="Foo">Foo</a> <small>2 godz. temu</small></li>
 * </ul>
 */
class Snippet_Feed extends Snippet
{
	/**
	 * Limit wyswietlanych naglowkow (zmian w tekscie)
	 */
	public $feedLimit = 10;
	/**
	 * Max. dlugosc tytulu. Jezeli tytul dokument jest dluzszy, bedzie przycinany (na koncu zostanie dodany wielokropek)
	 */
	public $feedLength = 100;
	/**
	 * Pole okresla czy data ma byc wyswietlana relatywnie w odniesieniu do obecnej daty
	 * Jezeli wartosc jest FALSE, data bedzie wyswietlana w formacie takim, jaki zostal
	 * zadeklarowany w profilu uzytkownika
	 */
	protected $relativeDate = true;
	/**
	 * Tablica z ID modulow stron, ktore maja byc wyswietlone
	 */
	protected $module;
	/**
	 * Domyslnie wynik zapytania nie jest cachowany, lecz mozna ustalic sekundowa
	 * wartosc cache
	 */
	protected $cacheLifetime = false;

	/**
	 * Metoda wyswietlajaca snippet.
	 * @param object $instance Opcjonalnie instancja klasy implementujacej interfejs IView do ktorej
	 * zostana przekazane dane odczytane z bazy danych
	 */
	public function display(IView $instance = null)
	{
		if ($this->cacheLifetime)
		{
			$groups = array(1, 2);
		}
		else
		{		
			$user = &$this->getModel('user');
			$groups = $user->getGroups();
		}
		
		$query = $this->db->select('page_title, page_subject, location_text, page_edit_time')->from('page_v')->where('page_delete = 0 AND page_publish = 1')->order('page_edit_time DESC')->limit(0, $this->feedLimit);
		$query->where('page_id IN(SELECT pg.page_id FROM page_group pg WHERE pg.group_id IN(' . implode(',', $groups) . '))');
		
		if ($this->module)
		{
			$query->in('page_module', $this->module);
		}
		
		if ($this->cacheLifetime)
		{
			if (!isset($this->cache->feedSql))
			{
				$result = $query->fetchAll();
				$this->cache->save('feedSql', $result, $this->cacheLifetime);
			}
			else
			{
				$result = $this->cache->load('feedSql');
			}
		}
		else
		{
			$result = $query->fetchAll();
		}
		$query->reset();		
		
		if ($instance != null)
		{			
			$instance->result = $result;
		}
		else
		{
			$instance = '<ul>';

			foreach ($result as $row)
			{
				$date = User::date($row['page_edit_time']);
				$instance .= '<li><a title="' . ($row['page_title'] ? $row['page_title'] : $row['page_subject']) . ' - ' . Url::__($row['location_text']) . '" href="' . Url::__($row['location_text']) . '">' . Text::limitHtml($row['page_subject'], $this->feedLength - Text::length($date)) . '</a> <small>' . $date . '</small></li>';
			}
			$instance .= '</ul>';
		}

		echo $instance;
	}
}
?>