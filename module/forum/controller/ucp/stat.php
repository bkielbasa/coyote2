<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Stat_Controller extends Controller
{
	function __start()
	{
		if (User::$id == User::ANONYMOUS)
		{
			$this->redirect(Path::connector('login') . '?redirect=' . url('@user'));
		}
	}

	public function main()
	{
		Breadcrumb::add(url('@user'), 'Panel użytkownika');
		Breadcrumb::add(url('@user?controller=Vote'), 'Statystyki moich postów');

		$post = &$this->getModel('post');
		$query = $post->select('page_subject, page_id, post_forum, COUNT(*), location_text')
					  ->from('post')
					  ->innerJoin('forum', 'forum_id = post_forum')
					  ->innerJoin('page', 'page_id = forum_page')
					  ->innerJoin('location', 'location_page = page_id')
					  ->where('post_user = ?', User::$id)
					  ->group('post_forum')
					  ->get();

		$page = &$this->getModel('page');
		$this->stat = array();

		$this->totalItems = array();

		foreach ($query as $row)
		{
			if ($page->isAllowed($row['page_id'], User::$id))
			{
				$this->stat[] = $row;
				$this->totalItems[] = $row['COUNT(*)'];
			}
		}

		array_multisort($this->totalItems, SORT_DESC, $this->stat);

		return true;
	}
}
?>