<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/** 
 * Dekorator generujacy znacznik <label>
 */
class Form_Decorator_Label extends Form_Decorator_Abstract
{
	protected $placement = self::PREPEND;
	protected $tag = 'label';

	/**
	 * Ustawia atrybut title dla dekoratora
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->addAttribute('title', $title);
		return $this;
	}

	/**
	 * Zwraca atrybut title dla dekoratora
	 * @return string
	 */
	public function getTitle()
	{
		return $this->getAttribute('title');
	}

	/**
	 * Glowna metoda dekoratora
	 * @param string $content Dotychczasowa zawartosc
	 * @return string
	 */
	public function display($content)
	{
		if (!$label = $this->getElement()->getLabel())
		{
			$label = '&nbsp;';
		}

		if ($this->getElement()->isRequired())
		{
			$tag = new Form_Decorator_Tag;
			$tag->setPlacement(self::WRAP)->setTag('em');

			$label .= $tag->display('*');
		}
		$label = Html::tag($this->tag, true, $this->getAttributes(), $label);

		switch ($this->getPlacement())
		{
			case self::APPEND:

				$content .= $this->getSeparator() . $label;
			break;

			case self::PREPEND:

				$content = $label . $this->getSeparator() . $content;
			break;
		}

		return $content;
	}
}
?>