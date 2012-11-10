<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Filtr usuwajacy "biale" znaki na koncu i poczatku lancucha
 */
class Filter_PregReplace implements IFilter
{
	private $pattern;
	private $replacement;

	function __construct($pattern, $replacement)
	{
		$this->setPattern($pattern);
		$this->setReplacement($replacement);
	}

	public function setPattern($pattern)
	{
		$this->pattern = $pattern;
		return $this;
	}

	public function getPattern()
	{
		return $this->pattern;
	}

	public function setReplacement($replacement)
	{
		$this->replacement = $replacement;
		return $this;
	}

	public function getReplacement()
	{
		return $this->replacement;
	}

	public function filter($value)
	{
		return preg_replace($this->pattern, $this->replacement, $value);
	}
}
?>