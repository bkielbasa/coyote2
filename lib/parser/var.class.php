<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Parser_Var implements Parser_Interface
{
	public function parse(&$content, Parser_Config_Interface &$config)
	{
		if ($config->getOption('vars'))
		{
			foreach ($config->getOption('vars') as $name => $value)
			{
				$vars['{{' . $name . '}}'] = $value;
			}

			$content = str_replace(array_keys($vars), array_values($vars), $content);
		}
	}
}
?>