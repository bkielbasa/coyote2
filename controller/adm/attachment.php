<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Attachment_Controller extends Adm
{
	function main()
	{
		$attachment = &$this->getModel('attachment');

		if ($this->input->getMethod() == Input::POST)
		{
			$delete = array_map('intval', $this->post->delete);
			if ($delete)
			{
				$attachment->delete($delete, true);
				Box::information('Załączniki usunięte!', 'Zaznaczone załączniki zostały usunięte!', '', 'adm/information_box');
				exit;
			}
		}

		load_helper('sort');
		// ustawienie domyslnego trybu sortowania
		Sort::setDefaultSort('attachment_id', Sort::DESC);		

		$totalItems = $attachment->count();
		$this->attachment = $attachment->fetch(null, Sort::getSortAsSQL(), (int)$this->get['start'], 50)->fetch();

		$this->pagination = new Pagination('', $totalItems, 50, (int)$this->get['start']);
		return true;
	}

	public function submit($id = 0)
	{
		$id = (int)$id;
		$attachment = &$this->getModel('attachment');

		if (!$result = $attachment->find($id)->fetchAssoc())
		{
			throw new UserErrorException('Brak załącznika o podanym ID!');
		}

		if ($this->input->isMethod(Input::POST))
		{
			if (isset($this->post->delete))
			{
				$attachment->delete($id, true);
				Box::information('Załącznik usunięty', 'Załącznik został bezpowrotnie usunięty.', url('adm/Attachment'), 'adm/information_box');

				exit;
			}
		}
		
		/**
		 * @todo Przeniesc do modelu!
		 */

		$query = $this->db->select('t.text_time, t.text_id, p.page_subject, location_text')
						  ->from('page_attachment a, page_text t, page_version v, page p')
						  ->innerJoin('location', 'location_page = p.page_id')
						  ->where('a.attachment_id = ' . $id . ' AND t.text_id = a.text_id')
						  ->where('v.text_id = a.text_id AND p.page_id = v.page_id')
						  ->get();
		$this->versions = $query->fetch();

		$this->thumbnail = array();
		foreach (glob('store/_aa/*-' . $result['attachment_file']) as $fileName)
		{
			preg_match('#store/_aa/([0-9]+)-' . $result['attachment_file'] . '#', $fileName, $match);
			
			$this->thumbnail[] = array(
				'size'			=> filesize($fileName),
				'time'			=> filemtime($fileName),
				'width'			=> $match[1]
			);
		}

		return View::getView('adm/attachmentSubmit', $result);
	}

	public function purge($id = 0)
	{
		$id = (int)$id;
		$attachment = &$this->getModel('attachment');

		if (!$result = $attachment->find($id)->fetchAssoc())
		{
			throw new UserErrorException('Brak załącznika o podanym ID!');
		}

		if ($this->input->isPost())
		{
			foreach ($this->post->delete as $item)
			{
				@unlink('store/_aa/' . $item . '-' . $result['attachment_file']);
			}
		}

		$this->redirect('adm/Attachment/Submit/' . $id);
	}
}
?>