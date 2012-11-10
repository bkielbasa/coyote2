<?php
	/**
	 * @package Coyote CMF
	 * @author Adam Boduch <adam@boduch.net>
	 * @copyright Copyright (c) 4programmers.net
	 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
	 */

class Vote extends Controller
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
		Breadcrumb::add(url('@user?controller=Vote'), 'Oceny moich postów');

		$post = &$this->getModel('post');
		$result = $post->vote->getUserRecievedVotes(User::$id, (int) $this->get['start'], 20);

		$this->votes = $result->fetchAll();
		$this->pagination = new Pagination('', $post->vote->getUserTotalRecievedVotes(), 20, (int) $this->get['start']);

		return true;
	}
}
?>