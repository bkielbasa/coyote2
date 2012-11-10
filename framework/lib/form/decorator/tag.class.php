<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/** 
 * Dekorator tworzy dowolny znacznik xHTML
 */
class Form_Decorator_Tag extends Form_Decorator_Abstract
{
	protected $placement = self::WRAP;

	public function display($content)
	{
		// zwracamy tresc, jezeli nie ustawiono zadnego znacznika
		if (!$this->getTag())
		{
			return $content;
		}
		$tag = Html::tag($this->tag, true, $this->getAttributes());

		switch ($this->getPlacement())
		{
			case self::APPEND:

				$content .= $this->getSeparator() . $tag;
			break;

			case self::PREPEND:

				$content = $tag . $this->getSeparator() . $content;
			break;

			case self::WRAP:

				$content = Html::tag($this->tag, true, $this->getAttributes(), $content);
			break;
		}

		return $content;
	}
}
?>