<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Snippet wyswietla liste ostatnich zmian na forum
 * Lista bedzie wyswietlana w postacii listy HTML - np.;
 * <ul>
 *	<li><a title="test" href="Test">Test</a> <small>1 godz. temu</small></li>
 *	<li><a title="foo" href="Foo">Foo</a> <small>2 godz. temu</small></li>
 * </ul>
 */
class Snippet_Topic extends Snippet
{
	/**
	 * Limit wyswietlanych naglowkow (zmian na forum)
	 */
	public $topicLimit = 10;
	/**
	 * Max. dlugosc tytulu. Jezeli tytul watku jest dluzszy, bedzie przycinany (na koncu zostanie dodany wielokropek)
	 */
	public $topicLength = 100;
	/**
	 * Domyslnie wynik zapytania nie jest cachowany, lecz mozna ustalic sekundowa
	 * wartosc cache
	 */
	protected $cacheLifetime = false;

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

		$query = $this->db->select('page_title, page_subject, location_text, topic_last_post_time')->from('topic')->order('topic_last_post_time DESC')->limit($this->topicLimit);
		$query->innerJoin('forum', 'forum_id = topic_forum AND forum_lock = 0');
		$query->innerJoin('page', 'page_id = topic_page');
		$query->innerJoin('location', 'location_page = page_id');

		$query->where('topic_page IN(SELECT pg.page_id FROM page_group pg WHERE pg.group_id IN(' . implode(',', $groups) . '))');

		if ($this->cacheLifetime)
		{
			if (!isset($this->cache->snippetTopicSql))
			{
				$result = $query->fetchAll();
				$this->cache->save('snippetTopicSql', $result, $this->cacheLifetime);
			}
			else
			{
				$result = $this->cache->load('snippetTopicSql');
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
				$date = User::date($row['topic_last_post_time']);
				$instance .= '<li><a title="' . ($row['page_title'] ? $row['page_title'] : $row['page_subject']) . ' - ' . Url::__($row['location_text']) . '" href="' . Url::__($row['location_text']) . '?view=unread">' . Text::limitHtml($row['page_subject'], $this->topicLength - Text::length($date)) . '</a> <small>' . $date . '</small></li>';
			}
			$instance .= '</ul>';
		}

		echo $instance;
	}
}
?>