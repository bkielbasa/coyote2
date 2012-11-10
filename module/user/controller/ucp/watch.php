<?php
/**
 * @package Coyote CMF
 * @author Lukasz Nidecki <lukasz@4programmers.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Watch extends Controller
{
	function __start()
	{
		if (User::$id == User::ANONYMOUS)
		{
			$this->redirect(Path::connector('login') . '?redirect=' . url('@user?controller=Watch'));
		}
	}

	function main()
	{
		Breadcrumb::add(url('@user'), 'Panel użytkownika');
		Breadcrumb::add(url('@user?controller=Watch'), 'Obserwowane');

		$watch = &$this->getModel('watch');

		if ($this->input->isPost())
		{
			$delete = array_map('intval', $this->post['delete']);

			if ($delete)
			{
				$watch->delete('user_id = ' . User::$id . ' AND page_id IN(' . implode(',', $delete) . ')');
				$this->session->message = 'Zaznaczone strony zostały usunięte z listy obserwowanych';
			}
		}

		$totalItems = $watch->select('COUNT(*)')->where('user_id = ' . User::$id)->fetchField('COUNT(*)');

		$this->watch = $watch->fetch('user_id = ' . User::$id, 'watch_time DESC', (int) $this->get->start, 25)->fetch();
		$this->pagination = new Pagination('', $totalItems, 25, (int) $this->get->start);

		return View::getView('ucp/watch');
	}
}
?>