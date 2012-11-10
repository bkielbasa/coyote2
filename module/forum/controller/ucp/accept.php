<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Accept extends Controller
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
		Breadcrumb::add(url('@user?controller=Accept'), 'Zaakceptowane odpowiedzi');

		$post = &$this->getModel('post');
		$result = $post->accept->getUserAcceptedPosts(User::$id, (int) $this->get['start'], 20);

		$this->accept = $result->fetchAll();
		$this->pagination = new Pagination('', $post->accept->getUserTotalAcceptedPosts(User::$id), 20, (int) $this->get['start']);

		return true;
	}
}
?>