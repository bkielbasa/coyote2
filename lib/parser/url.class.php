<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Parser_Url implements Parser_Interface
{
	public function parse(&$content, Parser_Config_Interface &$config)
	{
		if ($config->getOption('url.disable') == true)
		{
			$content = Text::transformEmail($content);
			return false;
		}
		/* tablica wyrazen regularnych do zastapienia w tekscie */
		$regexp = array(
			'pattern'	=> array(
				"#<url>([a-z]+?://)([][()^{}%$0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ.,?!%*_\#:;~\\&$@/=+-]+)</url>#sie",
				"#<url=([a-z]+?://)([][()^{}%$0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ.,?!%*_\#:;~\\&$@/=+-]+)>(.+)</url>#Usi",
				"#<email>([a-z0-9\-_.]+?@[a-z0-9\-_.]+?)</email>#si",
				"#<image>([a-z]+?://)([a-z0-9\-\.,\?!%\*\[\]_\#:;~\\&$@\/=\+\^{}() ]+)</image>#si",
				"#<wiki href=\"(.*?)\">(.*?)</wiki>#sie",
				"#<wiki>([^<]*)</wiki>#sie"
			),

			'replacement'	=> array(
				"'<a href=\"$1$2\">'.Url::limit('$1$2').'</a>'",
				"<a href=\"$1$2\">$3</a>",
				"<a href=\"mailto:$1$2\">$1$2</a>",
				"<img src=\"$1$2\" alt=\"user image\" />",
				"'<a title=\"Wikipedia\" href=\"http://pl.wikipedia.org/wiki/'.htmlspecialchars(stripslashes('$1')).'\"> $2 </a>'",
				"'<a title=\"Wikipedia\" href=\"http://pl.wikipedia.org/wiki/'.htmlspecialchars(stripslashes('$1')).'\"> $1 </a>'"
			)
		);

		$code = Core::getInstance()->parser->extract('code|tt|kbd|samp|quote');

		/* wykonanie wyrazen regularnych */
		$content = preg_replace($regexp['pattern'], $regexp['replacement'], $content);

		$content = Text::transformUrl($content, 70);
		$content = Text::transformEmail($content);
		Core::getInstance()->parser->retract($code);
	}
}
?>