<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Pastebin extends Page_Controller
{
	public static function getSyntaxData()
	{
		$syntax_arr = array(
			  'Plain'        => __('Czysty tekst'),
			  'AS3'			 => 'AS3',
			  'Bash'         => 'BASH',
			  'ColdFusion'	 => 'ColdFusion',
			  'Cpp'          => 'C++',
			  'CSharp'       => 'C#',
			  'Css'          => 'CSS',
			  'Delphi'       => 'Delphi',
			  'Diff'		 => 'Diff',
			  'Java'         => 'Java',
			  'JavaFX'		 => 'JavaFX',
			  'JScript'		 => 'JavaScript',
			  'Perl'		 => 'Perl',
			  'PowerShell'	 => 'PowerShell',
			  'Php'          => 'PHP',
			  'Python'       => 'Python',
			  'Ruby'		 => 'Ruby',
			  'Scala'        => 'Scala',
			  'Sql'          => 'SQL',
			  'Vb'           => 'Visual Basic',
			  'Xml'          => 'XML'
		 );

		 return $syntax_arr;
	}

	public static function getExpiredData()
	{
		return array(
				'0'			=> 'Nigdy',
				'72'		=> __('72 godz.'),
				'48'		=> __('48 godz.'),
				'24'		=> __('24 godz.'),
				'1'			=> __('1 godz.')
		);
	}

	function main()
	{
		$this->post->disableFilter();
		$pastebin = &$this->getModel('pastebin');

		$pastebin->delete('pastebin_expire != 0 AND pastebin_expire < ' . time());
		$this->pastebin = $pastebin->fetch(null, 'pastebin_id DESC', 0, 10)->fetch();

		$this->form = $this->getForm();
		if ($this->form->isValid())
		{
			$values = $this->form->getValues();

			$pastebin->insert(array(
				'pastebin_user'			=> User::$id,
				'pastebin_username'		=> (string)@$values['username'],
				'pastebin_time'			=> time(),
				'pastebin_expire'		=> strtotime('+' . $values['expire'] . ' hours'),
				'pastebin_syntax'		=> $values['syntax'],
				'pastebin_content'		=> $values['content']
				)
			);

			$id = $pastebin->nextId();

			Log::add("Dodanie nowego wpisu: #$id", 'Dodanie wpisu do Pastebin', $this->page->getId());
			$this->redirect('@page_' . $this->page->getId() . '?id=' . $id);
		}
		$view = parent::main();

		if ($this->router->id)
		{
			$id = (int)$this->router->id;
			if (!$result = $pastebin->find($id)->fetchAssoc())
			{
				throw new Error(404, 'Wpis o tym ID nie został odnaleziony');
			}

			$view->assign($result);

			$this->output->setJavaScript('../../module/pastebin/template/js/shCore');
			$this->output->setStylesheet('../module/pastebin/template/js/styles/shCore.css');
			$this->output->setStylesheet('../module/pastebin/template/js/styles/shThemeDefault.css');
			$this->output->setJavaScript('../../module/pastebin/template/js/brush/shBrush' . ucfirst($result['pastebin_syntax']) . '.js');

			if (!$this->input->isPost())
			{
				$this->form->getElement('content')->setValue($result['pastebin_content']);
				$this->form->getElement('syntax')->setValue($result['pastebin_syntax']);
			}
		}

		return $view;
	}

	public function getForm()
	{
		$form = new Forms('', Forms::POST);
		Load::loadFile('lib/validate.class.php', false);

		$antispam = $this->post->antispam(Text::random(5));

		$form->createElement('hidden', 'antispam')->setValue($antispam)->setEnableDefaultDecorators(false);

		$form->createElement('text', 'postkey')
			->addFilter('trim')
			->setLabel("Klucz ($antispam)")
			->setDescription("Nie posiadasz obsługi JavaScript. Aby potwierdzić, że nie jesteś botem, wpisz tutaj wartość <b>$antispam</b>")
			->addFilter('htmlspecialchars')
			->addValidator(new Validate_Equal($antispam));

		if (User::$id == User::ANONYMOUS)
		{
			$username = &$form->createElement('text', 'username');
			$username->addValidator('string', array(true, 3, 100));
			$username->addFilter('htmlspecialchars');
			$username->setLabel(__('Nazwa użytkownika'));

			/*
			 * Podawanie nazwy uzytkownika nie jest wymagane. ALE jezeli user poda nazwe nazytkownika, nastepuje
			 * proces walidacji, sprawdzajacy, czy user nie podal nazwy uzytkownika, ktora jest zajeta (istnieje konto o takim loginie).
			 * Ma to na celu ukrucenie procesu podszywania sie pod zarejestrowanych userow
			 */
			if ($this->post->username)
			{
				$username->addValidator('login');
			}
		}

		$form->createElement('hash', 'pastebin');
		$form->createElement('select', 'expire', array(), array(
				'MultiOptions'	=>	$this->getExpiredData(),
				'Label'			=>	__('Wygaśnie'),
				'Filters'		=>	array('int'),
				'Value'			=>	'72'
			)
		);
		$form->createElement('select', 'syntax', array(), array(
				'MultiOptions'	=>	$this->getSyntaxData(),
				'Label'			=>	__('Kolorowanie składni'),
				'Validators'	=>	array(array('string', false)),
				'Filters'		=>	array('strip_tags', 'htmlspecialchars', 'trim')
			)
		);

		$content = $form->createElement('textarea', 'content', array(
				'rows'			=>	10,
				'cols'			=>	70,
				'style'			=>	'width: 98%'
			),
			array(
				'Filters'		=> array('htmlspecialchars')
			)
		);

		$submit = $form->createElement('submit')->setValue( __('Wyślij'));
		$submit->addDecorator('description', array('tag' => 'small'))
			   ->addDecorator('tag', array('tag' => 'li', 'placement' => 'WRAP'));

		return $form;
	}
}

?>