<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Dekorator wyswietlajacy informacje o polu
 */
class Form_Decorator_Description extends Form_Decorator_Abstract
{
	protected $placement = self::APPEND;
	protected $tag = 'p';

	public function display($content)
	{
		$description = $this->getElement()->getDescription();
		if (!$description)
		{
			return $content;
		}

		$description = Html::tag($this->tag, true, $this->getAttributes(), $description);

		switch ($this->getPlacement())
		{
			case self::APPEND:

				$content .= $this->getSeparator() . $description;
			break;

			case self::PREPEND:

				$content = $description . $this->getSeparator() . $content;
			break;
		}

		return $content;
	}
}
?>