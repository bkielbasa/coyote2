<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Pastebin_Controller extends Adm
{
	function main()
	{
		$pastebin = &$this->getModel('pastebin');
		if ($this->input->isPost())
		{
			if ($delete = $this->post->delete)
			{
				$pastebin->delete('pastebin_id IN(' . implode(',', $delete) . ')');
				$this->message = 'Zaznaczone rekordy zostały usunięte';
			}
		}

		$this->pastebin = $pastebin->fetch(null, 'pastebin_id DESC', (int)$this->get['start'], 20)->fetch();
		$this->pagination = new Pagination('', $pastebin->count(), 20, (int)$this->get['start']);

		return true;
	}
}
?>