<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa parsera sluzaca do lamania linii tekstu (zamiana znakow nowej linii na <br />)
 */
class Parser_Br implements Parser_Interface
{
	public function parse(&$content, Parser_Config_Interface &$config)
	{
		$core = &Core::getInstance();
		// wydziel znaczniki pomiedzy ktorymi zostana zachowana oryginalnosc tekstu
		// (nie zamieniamy znakow nowej linii na <br />

		$plain = $core->parser->extract('nobr');
		$tags = $core->parser->extract('(nobr|table|ul|ol|pre)');

		$content = preg_replace("/^ /m", '&nbsp;', $content);

		/* lamanie linii */
        $content = nl2br(str_replace('  ', '&nbsp;&nbsp;', $content));
        $core->parser->retract($tags);
		$core->parser->retract($plain);

		$content = str_replace(array('<nobr>', '</nobr>'), '', $content);
	}
}

?>