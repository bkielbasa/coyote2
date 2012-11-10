<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Atom_Controller extends Controller
{
	function main()
	{
		$feedsLimit = $this->module->atom('feedsLimit', $this->page->getId());
		$order = $this->module->atom('order', $this->page->getId());
		$cacheLifetime = $this->module->atom('cache', $this->page->getId());
			
		$connectorId = $this->page->getConnectorId();

		$root = $this->page->getParentId();
		$page = &$this->getModel('page');

		$query = $this->db->select('page_title, page_subject, page_id, page_time, page_edit_time, location_text, text_id, text_content, meta_description');
		$query->from('page p');
		$query->innerJoin('location', 'location_page = p.page_id');
		$query->leftJoin('page_text', 'text_id = p.page_text');		
		$query->leftJoin('meta', 'meta_page = p.page_id'); 
		
		$user = &$this->getModel('user');

		/**
		 * Jezeli naglowki maja byc cachowane, nalezy wyswietlic jedynie te wpisy, do ktoryh kazdy,
		 * ogolnie ma dostep. Poniewaz plik ATOM moze zostac wygenerowany przez administratora, ktory
		 * ma dostep do wszystkich stron. Tak zapisany plik (w cache) bedzie natomiast prezentowany
		 * osobom, ktore nie maja dostepu do niektorych stron
		 */
		if ($cacheLifetime)
		{
			$groups = array(1, 2);
		}
		else
		{
			$groups = $user->getGroups();			
		}
		$query->where('p.page_id IN(SELECT pg.page_id FROM page_group pg WHERE pg.group_id IN(' . implode(',', $groups) . '))');

		if ($root)
		{
			$query->innerJoin('path', 'parent_id = ' . $root);
			$query->where("p.page_id = child_id");
		}
		
		$order = $order == 'time' ? 'page_time' : 'page_edit_time';
		$query->where('(p.page_publish = 1 AND p.page_delete = 0 AND p.page_connector != ' . $connectorId . ')')->order("$order DESC")->limit($feedsLimit);
		
		$cacheId = (string) $query;
		
		if ($cacheLifetime)
		{
			if (!$feeds = $this->cache->load($cacheId))
			{
				$feeds = $query->fetchAll();
				$this->cache->save($cacheId, $feeds, $cacheLifetime);
			}		
		}
		else
		{
			$feeds = $query->fetchAll();
		}		
		
		$atom = new Feed_Atom;
		$atom->setTitle($this->page->getTitle() ? $this->page->getTitle() : $this->page->getSubject());
		$atom->setLink(url($this->page->getLocation()));
		$atom->setId(Feed_Atom::getUuid('urn:uuid:', $this->page->getId()));
		
		foreach ($feeds as $row)
		{
			$element = $atom->createElement();

			$element->setTitle($row['page_subject']);
			$element->setLink(url($row['location_text']));
			$element->setId(Feed_Atom::getUuid('urn:uuid:', $row['page_id']));
			$element->setUpdated($row['page_edit_time']);
			
			if ($row['meta_description'])
			{
				$element->setSummary($row['meta_description']);
			}
			else
			{
				$element->setSummary(Text::limit(Text::plain($row['text_content']), 255));
			}
		}

		$this->output->setHttpHeader('Last-Modified', date(DATE_RFC1123, $atom->getUpdated()));
		echo $atom;	
		
		exit;
	}
}
?>