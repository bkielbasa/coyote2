<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Dekorator wyswietlajacy bledy walidacji
 */
class Form_Decorator_Errors extends Form_Decorator_Abstract
{
	protected $placement = self::APPEND;

	public function display($content)
	{
		// zwracamy jezeli brak bledow walidacji
		if (!$this->getElement()->hasErrors())
		{
			return $content;
		}

		$errors = '';
		foreach ($this->getElement()->getErrors() as $error)
		{
			$errors .= Html::tag('li', true, array(), $error);
		}

		$errors = Html::tag($this->getTag(), true, $this->getAttributes(), $errors);

		switch ($this->getPlacement())
		{
			case self::PREPEND:

				$content = $errors . $this->getSeparator() . $content;
			break;

			case self::APPEND:

				$content .= $this->getSeparator() . $errors;
			break;
		}

		return $content;
	}
}
?>