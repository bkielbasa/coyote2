<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Submit extends Forms
{
	private $enableSticky;
	private $enableAnnoucement;
	private $enableAnonymous;
	private $enableTags;
	private $enableSmilies;
	private $enableHtml = false;
	private $enableSubject = true;
	private $enableWatch = true;
	private $enableAntiSpam = true;
	private $isWatch;
	private $isAnnouncement = false;
	private $isSticky = false;
	private $isHtml = false;
	private $isReady;
	private $html = array();
	private $hash;

	function __construct($action = '', $method = self::POST)
	{
		parent::__construct($action, $method);

		Load::loadFile('lib/validate.class.php');
	}

	public function setEnableSticky($flag)
	{
		$this->enableSticky = (bool) $flag;
		return $this;
	}

	public function getEnableSticky()
	{
		return $this->enableSticky;
	}

	public function setEnableAnnouncement($flag)
	{
		$this->enableAnnoucement = (bool) $flag;
		return $this;
	}

	public function getEnableAnnouncement()
	{
		return $this->enableAnnoucement;
	}

	public function setEnableAnonymous($flag)
	{
		$this->enableAnonymous = (bool) $flag;
		return $this;
	}

	public function getEnableAnonymous()
	{
		return $this->enableAnonymous;
	}

	public function setEnableTags($flag)
	{
		$this->enableTags = (bool) $flag;
		return $this;
	}

	public function getEnableTags()
	{
		return $this->enableTags;
	}

	public function setEnableSmilies($flag)
	{
		$this->enableSmilies = (bool) $flag;
		return $this;
	}

	public function getEnableSmilies()
	{
		return $this->enableSmilies;
	}

	public function setEnableHtml($flag)
	{
		$this->enableHtml = (bool) $flag;
		return $this;
	}

	public function getEnableHtml()
	{
		return $this->enableHtml;
	}

	public function setIsHtml($flag)
	{
		$this->isHtml = (bool) $flag;
		return $this;
	}

	public function isHtml()
	{
		return $this->isHtml;
	}

	protected function setIsReady($flag)
	{
		$this->isReady = (bool) $flag;
		return $this;
	}

	protected function isReady()
	{
		return $this->isReady;
	}

	public function setEnableSubject($flag)
	{
		$this->enableSubject = (bool) $flag;
		return $this;
	}

	public function getEnableSubject()
	{
		return $this->enableSubject;
	}

	public function setEnableWatch($flag)
	{
		$this->enableWatch = (bool) $flag;
		return $this;
	}

	public function getEnableWatch()
	{
		return $this->enableWatch;
	}

	public function setIsWatch($flag)
	{
		$this->isWatch = (bool) $flag;
		return $this;
	}

	public function isWatch()
	{
		return $this->isWatch;
	}

	public function setEnableAntiSpam($flag)
	{
		$this->enableAntiSpam = (bool) $flag;
		return $this;
	}

	public function getEnableAntiSpam()
	{
		return $this->enableAntiSpam;
	}

	public function setIsAnnouncement($flag)
	{
		$this->isAnnouncement = (bool) $flag;
		return $this;
	}

	public function isAnnouncement()
	{
		return $this->isAnnouncement;
	}

	public function setIsSticky($flag)
	{
		$this->isSticky = (bool) $flag;
		return $this;
	}

	public function isSticky()
	{
		return $this->isSticky;
	}

	public function addHtml($html)
	{
		$this->html[] = $html;
		return $this;
	}

	public function setHtml(array $html)
	{
		$this->html = $html;
		return $this;
	}

	public function getHtml()
	{
		return $this->html;
	}

	public function setHash($hash)
	{
		$this->hash = $hash;
		return $this;
	}

	public function getHash()
	{
		return $this->hash;
	}

	public function renderForm()
	{
		$tabindex = 0;

		$element = new Form_Element_Hidden('hash');
		$element->setValue($this->getHash())->setEnableDefaultDecorators(false);

		$this->addElement($element);

		if ($this->getEnableAntiSpam())
		{
			$post = &Load::loadClass('input')->post;
			$antispam = $post->antispam(Text::random(5));

			$element = new Form_Element_Hidden('antispam');
			$element->setValue($antispam)->setEnableDefaultDecorators(false);

			$this->addElement($element);

			$element = new Form_Element_Text('postkey');
			$element->addFilter('trim')
					->setLabel("Klucz ($antispam)")
					->setDescription("Nie posiadasz obsługi JavaScript. Aby potwierdzić, że nie jesteś botem, wpisz tutaj wartość <b>$antispam</b>")
					->addFilter('htmlspecialchars')
					->addValidator(new Validate_Equal($antispam))
					->setOrder(5);

			$this->addElement($element);
		}

		if ($this->getEnableSubject())
		{
			$element = new Form_Element_Text('subject');
			$element->setLabel('Temat')
					->setRequired(true)
					->addValidator(new Validate_String(false, 2, 100))
					->addFilter('stringTrim')
					->addFilter('htmlspecialchars')
					->setDescription('Postaraj się dokładnie, zwięźle opisać problem w temacie')
					->setAttribute('maxlength', 100)
					->setAttribute('tabindex', ++$tabindex)
					->setAttribute('style', 'width: 400px')
					->setOrder(1);

			$this->addElement($element);
		}

		if ($this->getEnableAnonymous())
		{
//			$validateMatch = new Validate_Match('/^[0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ.=:|#_ ()[\]^-]+$/');
//			$validateMatch->setTemplate(Validate_Match::NOT_MATCH, 'Nazwa użytkownika jest nieprawidłowa');

			$element = new Form_Element_Text('username');
			$element->setLabel('Autor')
					->setRequired(true)
					->addFilter('strip_tags')
					->addFilter('htmlspecialchars')
					->addFilter('stringTrim')
					->addFilter(new Filter_PregReplace('/\s+/', ' '))
					->addValidator(new Validate_Login)
					->setDescription('Wpisz swoją nazwę użytkownika')
					->setAttribute('maxlength', 20)
					->setAttribute('tabindex', ++$tabindex)
					->setOrder(2);

			$this->addElement($element);
		}

		$validateLength = new Validate_String(false, 1, 60000);
		$validateLength->setTemplate(Validate_String::TOO_LONG, 'Treść wiadomości jest zbyt długa');

		$element = new Form_Element_Textarea('content');
		$element->setRequired(true)
				->addFilter('stringTrim')
				->setAttributes(array('cols' => 120, 'rows' => 10))
				->setAttribute('tabindex', ++$tabindex)
				->addValidator($validateLength)
				->setOrder(3)
				->addDecorator('errors', array('tag' => 'ul'));

		$htmlContent = '';
		foreach ($this->getHtml() as $html)
		{
			 $htmlContent .= (string) $html;
		}

		if ($htmlContent)
		{
			$element->addDecorator('html', array('html' => $htmlContent));
		}
		$element->addDecorator('tag', array('tag' => 'li', 'placement' => 'WRAP'));

		$this->addElement($element);

		if ($this->getEnableTags())
		{
			$element = new Form_Element_Text('tag');
			$element->addFilter('trim')
					->setLabel('Tagi')
					->setDescription('Możesz opisać swój wątek słowami kluczowymi - np. C#, CLR (max. 5 tagów)')
					->setAttribute('maxlength', 200)
					->setAttribute('tabindex', ++$tabindex)
					->addValidator(new Validate_String(true, 0, 200))
					->setOrder(4);

			$this->addElement($element);
		}

		if ($this->getEnableAnnouncement())
		{
			$element = $this->createElement('checkbox', 'announcement')->setOrder(6)->addAfterText(' Ogłoszenie');

			if ($this->isAnnouncement())
			{
				$element->setChecked(true);
			}
		}
		if ($this->getEnableSticky())
		{
			$element = $this->createElement('checkbox', 'sticky')->setOrder(7)->addAfterText(' Przyklejony');

			if ($this->isSticky())
			{
				$element->setChecked(true);
			}
		}

		if ($this->getEnableWatch())
		{
			$element = $this->createElement('checkbox', 'watch')->setOrder(8)->addAfterText(' Obserwuj wątek');

			if ($this->isWatch())
			{
				$element->setChecked(true);
			}
		}

		if (User::data('allow_smilies'))
		{
			$element = $this->createElement('checkbox', 'enableSmilies')->setOrder(9)->addAfterText(' Wyświetlaj uśmieszki');
			if ($this->getEnableSmilies())
			{
				$element->setChecked(true);
			}
		}

		if ($this->getEnableHtml())
		{
			$element = $this->createElement('checkbox', 'enableHtml')->setOrder(10)->addAfterText(' Nie usuwaj kodu HTML');

			if ($this->isHtml())
			{
				$element->setChecked(true);
			}
		}
		$this->createElement('submit', '')->setOrder(32555)->setAttribute('tabindex', ++$tabindex)->setAttribute('title', 'Wyślij (Ctrl+Enter)')->setValue('Wyślij');

		$this->setIsReady(true);
	}

	public function isValid($data = null)
	{
		if (!$this->isReady())
		{
			$this->renderForm();
		}

		return parent::isValid($data);
	}
}
?>