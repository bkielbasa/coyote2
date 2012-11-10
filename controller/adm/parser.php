<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Parser_Controller extends Adm
{
	function main()
	{
		$parser =&$this->getModel('parser');

		if ($this->input->isPost())
		{
			if ($delete = $this->post->delete)
			{
				$parser->delete($delete);
				$this->message = 'Zaznaczone rekordy zostały usunięte';
			}
		}

		if ($this->get->mode)
		{
			$mode = $this->get->mode == 'up' ? 'up' : 'down';
			$parser->$mode((int)$this->get->id);
		}

		$this->parser = $parser->fetch(null, 'parser_order')->fetch();
		return true;
	}

	public function submit($id = 0)
	{
		$id = (int)$id;
		$parser = &$this->getModel('parser');

		$result = array();
		if ($id)
		{
			if (!$result = $parser->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Parser o tym ID nie istnieje!');
			}
		}

		$this->filter = new Filter_Input;

		if ($this->input->isMethod(Input::POST))
		{
			$data['validator'] = array(

				'name'			=> array(

												array('string', false, 1, 50)
								),
				'text'			=> array(
												array('string', false, 1, 100)
								),
				'description'	=> array(
												array('string', true, 1, 255)
								)
			);
			$data['filter'] = array(

				'name'			=> array('trim', 'strip_tags', 'htmlspecialchars'),
				'text'			=> array('trim', 'htmlspecialchars'),
				'description'	=> array('htmlspecialchars'),
				'default'		=> array('int')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				load_helper('array');
				$data = array_key_pad($this->filter->getValues(), 'parser_');

				if ($id)
				{
					$parser->update($data, "parser_id = $id");
				}
				else
				{
					$parser->insert($data);
				}

				$this->session->message = 'Zmiany zostały zapisane';
				$this->redirect('adm/Parser');
			}
		}

		return View::getView('adm/parserSubmit', $result);
	}
}
?>