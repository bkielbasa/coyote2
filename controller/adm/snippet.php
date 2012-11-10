<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Snippet_Controller extends Adm
{
	function main()
	{
		$snippet = &$this->getModel('snippet');

		if ($this->input->isPost())
		{
			$delete = $this->post->delete;
			if ($delete)
			{
				$snippet->delete('snippet_id IN(' . implode(',', $delete) . ')');
				$this->message = 'Zaznaczone rekordy zostały usunięte';

				$this->cache->destroy('_snippet');
			}
		}

		$this->snippet = $snippet->fetch()->fetchAll();
		return true;
	}

	public function submit($id = 0)
	{
		$id = (int)$id;

		$snippet = &$this->getModel('snippet');
		$result = array();

		if ($id)
		{
			if (!$result = $snippet->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Brak rekordu o tym ID!');
			}
		}

		$this->filter = new Filter_Input;

		if ($this->input->isPost())
		{
			$data['validator'] = array(

				'name'				=> array(
												array('string', false, 3, 50)
									),
				'class'				=> array(
												array('string', true, 3, 50)
									),
				'content'			=> array(
												array('string', true)
									)
			);
			$data['filter'] = array(

				'name'				=> array('htmlspecialchars'),
				'class'				=> array('htmlspecialchars'),
				'text'				=> array('htmlspecialchars')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				load_helper('array');
				$data = array_key_pad($this->filter->getValues(), 'snippet_');

				if (!$id)
				{
					$data += array(
						'snippet_user'		=> User::$id,
						'snippet_time'		=> time()
					);
					$snippet->insert($data);
				}
				else
				{
					$snippet->update($data, "snippet_id = $id");
				}

				$this->session->message = 'Dane zostały zapisane!';
				$this->cache->destroy('_snippet');				

				$this->redirect('adm/Snippet');
			}
		}

		if ($id)
		{
			if (!$result['snippet_content'])
			{
				$snippetPath = 'lib/snippet/' . $result['snippet_class'] . '.class.php';

				if (Load::fileExists($snippetPath))
				{
					$this->isFile = true;
					$result['snippet_content'] = @file_get_contents($snippetPath);
				}
			}
		}

		return View::getView('adm/snippetSubmit', $result);
	}
}
?>