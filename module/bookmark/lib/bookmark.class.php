<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Bookmark extends Context
{
	public function decorate($result)
	{
		if ($this->input->isAjax())
		{
			if (isset($this->input->post->value) && isset($this->input->post->id))
			{
				$bookmark = new Bookmark_Model;
				$value = $this->input->post->value == 'digg' ? 1 : -1;

				echo $bookmark->rank->setRank((int)$this->input->post->id, $value); 
				exit;
			}
		}

		$view = new View('_partialBookmark');
		$view->bookmark = $result;

		return $view;
	}
}
?>