<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Report extends Controller
{
	function main()
	{
		$id = (int)$this->get->id;
		if (!$id)
		{
			throw new Error(500, 'Nieprawidłowe wywołanie strony');
		}

		$result = array();

		$page = &$this->getModel('page');
		if (!$result = $page->find($id)->fetchAssoc())
		{
			throw new Error(500, 'Strona o danym ID nie istnieje!');
		}

		$path = &$this->getModel('path');

		if ($this->input->isPost())
		{
			$this->form = $this->getForm($this->post->token);

			if ($this->form->isValid())
			{
				if (time() - $this->session->flood > 10)
				{
					$report = &$this->getModel('report');
					$report->insert(array(
						'report_page'			=> $id,
						'report_user'			=> User::$id,
						'report_time'			=> time(),
						'report_ip'				=> $this->input->getIp(),
						'report_message'		=> $this->post->message,
						'report_email'			=> (string) $this->post->email,
						'report_section'		=> htmlspecialchars($this->get->section),
						'report_anchor'			=> htmlspecialchars(base64_decode($this->get->anchor))
						)
					);
				}

				$this->session->flood = time();

				$paths = $path->asArray($id)->fetchAll();
				$this->redirect($paths[count($paths) -1]['location_text'] . '?' . base64_decode($this->get->anchor));
			}
		}
		else
		{
			$this->token = Text::random(10);
			$this->form = $this->getForm($this->token);
		}

		foreach ($path->asArray($id) as $row)
		{
			Breadcrumb::add(url($row['location_text']), $row['page_subject']);
		}
		Breadcrumb::add('', 'Raportowanie strony');

		return View::getView('ucp/report', $result);
	}

	private function getForm($token = null)
	{
		Load::loadFile('lib/validate.class.php');

		$form = new Forms('', Forms::POST);
		$form->createElement('hash', 'hash');
		$form->createElement('hidden', 'token')->setValue($token)->setEnableDefaultDecorators(false);

		if (User::$id == User::ANONYMOUS)
		{
			$element = new Form_Element_Text('email');
			$element->setLabel('Adres e-mail')
					->setRequired(true)
					->addValidator(new Validate_Email);

			$form->addElement($element);
		}

		$element = new Form_Element_Textarea('message');
		$element->setLabel('Wiadomość')
				->setDescription('Opisz dokładnie problem, przyczynę zgłoszenia raportu')
				->setRequired(true)
				->addFilter('htmlspecialchars')
				->setAttributes(array('cols' => 90, 'rows' => 15));

		$form->addElement($element);
		$form->createElement('submit', '')->setValue('Wyślij raport');

		return $form;
	}
}
?>