<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Infobox_Controller extends Controller
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
		Breadcrumb::add(url('@user?controller=Vote'), 'Komunikaty');

		$infobox = &$this->getModel('infobox');

		$this->infobox = $infobox->fetch('infobox_enable = 1', 'infobox_id DESC', (int) $this->get['start'], 1)->fetchAssoc();
		$this->pagination = new Pagination('', $infobox->count(), 1, (int) $this->get['start']);

		return true;
	}
}
?>