<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Comment extends Plugin
{
	const NAME = 'comment';

	public function display()
	{
		if (!(@$this->page instanceof Page))
		{
			return;
		}

		$pageId = $this->page->getId();

		if (!$pageId)
		{
			return;
		}
		// pobranie modulu, w ktorym uruchomione sa komentarze
		$module = $this->module->getCurrentModule();
		// sprawdzenie, czy w danym module wlaczone sa komentarze
		if (!$this->module->isPluginEnabled(self::NAME))
		{
			return;
		}
		$moduleId = $this->module->getId($module);
		$pluginId = $this->module->getPluginId('comment');

		$enableAnonymous = $this->module->$module('commentEnableAnonymous', $pageId);
		$enableHtml = $this->module->$module('commentEnableHtml', $pageId);

		$watch = &$this->load->model('watch');

		$view = $this->load->view('comment', array(
				'moduleId'			=> $moduleId,
				'pageId'			=> $pageId,

				'enableAnonymous'	=> $enableAnonymous,
				'enableDelete'		=> $this->module->$module('commentEnableDelete', $pageId),

				'isWatched'			=> User::$id > User::ANONYMOUS && $watch->isWatched($pageId, $moduleId, $pluginId)
			)
		);

		$parser = &$this->load->library('parser');

		$parser->removeParsers();
		$parser->addParser(new Parser_Html);
		$parser->addParser(new Parser_Url);
		$parser->addParser(new Parser_Br);

		$query = $this->db->select()->from('comment')->where("comment_page = $pageId")->leftJoin('user', 'user_id = comment_user')->order('comment_id DESC')->get();
		while ($row = $query->fetchAssoc())
		{
			// domyslnie - dozwolony jest pewien zestaw znacznikow
			$parser->setOption('html.allowTags', array('b', 'i', 'u', 'del', 'hr', 'sup', 'sub', 'code', 'kbd', 'tt', 'pre', 'strong', 'a'));

			// jezeli html jest wylaczony - nalezy ustawic to w konfiguracji parsera
			if ($enableHtml == 0)
			{
				$parser->setOption('html.allowTags', array(''));

			}
			else if ($enableHtml == 1)
			{
				// jezeli html jest wlaczony dla zarejestrowanych, nalezy
				// usunac liste dozwolonych znacznikow jezeli user jest anonimem
				if ($row['comment_user'] == User::ANONYMOUS)
				{
					$parser->setOption('html.allowTags', array(''));
				}
			}

			$row['comment_content'] = $parser->parse($row['comment_content']);
			$view->append('comment', $row);
		}

		echo $view;
	}
}
?>