<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Bookmark_Controller extends Controller
{
	function __start()
	{
		if (User::$id == User::ANONYMOUS)
		{
			$this->redirect(Path::connector('login'));
		}
	}

	function main()
	{
		$bookmark = &$this->getModel('bookmark');

		if ($this->input->isAjax())
		{
			if ($delete = (int)$this->post->delete)
			{
				$bookmark->user->delete('bookmark_user = ' . User::$id . ' AND bookmark_id = ' . $delete);
			}

			exit;
		}
		
		Breadcrumb::add(url('@user'), 'Panel użytkownika');
		Breadcrumb::add(url('@user?controller=Bookmark'), 'Zakładki');
		
		$this->output->setStylesheet('../module/bookmark/template/css/bookmark');

		$this->bookmark = $bookmark->user->fetch(User::$id, (int)$this->get['start'], 20)->fetch();

		$totalItems = $bookmark->user->select('COUNT(*)')->where('bookmark_user = ' . User::$id)->get()->fetchField('COUNT(*)');
		$this->pagination = new Pagination('', $totalItems, 20, (int)$this->get['start']);

		return true;
	}
}
?>