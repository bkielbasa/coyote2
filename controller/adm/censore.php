<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Censore extends Adm
{
	function main()
	{
		$censore = &$this->getModel('censore');
		$this->censore = $censore->select()->fetchPairs();
		
		if ($this->input->isPost())
		{
			$censore->delete();
			$items = array_combine($this->post['text'], $this->post['replacement']);

			$sql = array();
			
			foreach ($items as $key => $value)
			{
				$sql[] = array(
					'censore_text'		=> $key,
					'censore_replacement'	=> $value
				);
			}
			
			if ($sql)
			{
				$this->db->multiInsert('censore', $sql);
			}			
			
			$this->session->message = 'Zmiany zostały zapisane';
			$this->redirect('adm/Censore');			
		}
		
		return true;
	}
}