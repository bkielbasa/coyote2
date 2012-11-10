<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Snippet_Bookmark extends Snippet
{
	public $bookmarkLimit = 10;

	public function display(IView $instance = null)
	{
		$bookmark = &$this->load->model('bookmark');

		$result = $bookmark->fetch(null, 'bookmark.bookmark_rank DESC, bookmark.bookmark_id DESC', 0, $this->bookmarkLimit)->fetch();
		$bookmark = new Bookmark;

		if ($instance != null)
		{
			$instance->result = $query->fetch();
		}
		else
		{
			$instance = $bookmark->decorate($result);
		}

		echo $instance;
	}

	public function __toString()
	{
		return (string)$this->display();
	}
}
?>