<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Lacznik strony glownej forum
 */
class Connector_Forum extends Connector_Document implements Connector_Interface
{
	public function renderForm()
	{
		parent::renderForm();
		$this->getFieldset('setting')->getElement('page_template')->setValue('forum.php');

		$this->setDefaults();
	}

	public function delete()
	{
		if ($this->getChildren())
		{
			throw new Exception('Connector_Forum: deleting not implemented yet. Please remove children nodes first');
		}
		else
		{
			parent::delete();
		}
	}

	public function prune()
	{
		if (!defined('IN_CRON'))
		{
			die('Hacking attempt...');
		}

		$forum = &$this->getModel('forum');
		$query = $forum->select('forum_id, forum_prune')->where('forum_prune > 0')->get();

		$forumIds = $query->fetchPairs();
		if ($forumIds)
		{
			foreach ($forumIds as $forumId => $prune)
			{
				$query = $this->db->select('topic_page')
								  ->from('topic')
								  ->where("topic_forum = $forumId")
								  ->where('topic_last_post_time < ' . (time() - (Time::DAY * $prune)));

				$pageIds = $query->fetchCol();

				foreach ($pageIds as $pageId)
				{
					try
					{
						Core::getInstance()->db->begin();

						$page = Page::load((int) $pageId);
						if ($page !== false)
						{
							$page->delete();
						}

						unset($page);

						Core::getInstance()->db->commit();
					}
					catch (Exception $e)
					{
						Core::getInstance()->db->rollback();

						Log::add('Usuwanie stron z forum wykonane nieprawidłowo: ' . $e->getMessage(), E_CRON);
					}
				}
			}

			Core::getInstance()->cache->remove('sql_' . $this->module->getId('forum') . '*', Cache::PATTERN);
		}
	}
}
?>