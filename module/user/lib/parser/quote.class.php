<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Parser zamieniajacy bloki cytowanego tekstu (znak > na poczatku linii)
 * w znacznik <blockquote>
 */
class Parser_Quote implements Parser_Interface
{
	public function parse(&$content, Parser_Config_Interface &$config)
	{
		$lines = explode("\n", $content);
		$lines[] = ' ';
		$root = array();

		foreach ($lines as $index => $line)
		{
			if (!empty($line[0]) && $line[0] == '>')
			{
				$indent = strspn($line, '>');
				$value = substr($line, $indent);

				$line = '';

				if (!isset($root[$indent]) || !$root[$indent])
				{
					$line = '<blockquote>';
					$root[$indent] = true;

					for ($i = $indent; $i > 0; $i--)
					{
						if (!isset($root[$i]) || !$root[$i])
						{
							$root[$i] = true;
							$line .= '<blockquote>';
						}
					}
				}

				$lines[$index] = $line . $value;

				if (($nextIndent = strspn($lines[$index + 1], '>')) < $indent)
				{
					while ($nextIndent < $indent)
					{
						$lines[$index] .= '</blockquote>';
						$root[$indent--] = false;
					}
				}
			}
		}

		array_pop($lines);
		$content = implode("\n", $lines);
	}
}
?>