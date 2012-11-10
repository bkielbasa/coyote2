<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Parser znacznikow HTML
 */
class Parser_Html implements Parser_Interface
{
	/**
	 * Funkcja usuwa znacnziki HTML
	 */
	public function parse(&$content, Parser_Config_Interface &$config)
	{
		if ($config->getOption('html.enable') == true)
		{
			return false;
		}
		$core = &Core::getInstance();
		$code = $core->parser->extract('code');

		if ($config->getOption('html.allowTags'))
		{
			$allowedTags = $config->getOption('html.allowTags');
		}
		else
		{
			$allowedTags = array(

				'a' => 'href',
				'b',
				'i',
				'u',
				'cite',
				'ins',
				'del',
				'tt',
				'kbd',
				'samp',
				'var',
				'dfn',
				'ins',
				'pre',
				'blockquote',
				'hr',
				'sub',
				'sup',
				'font' => array('size', 'color'), // deprecated
				'img' => array('src', 'alt'),
				'email',
				'url' => '*',
				'code' => '*',
				'nobr',
				'plain',
				'div' => array('style', 'class'),
				'wiki' => array('href'),
				'tex',
				'acronym' => array('title')
			);
		}

		$filter = new Filter_Html;
		$filter->setAllowedTags($allowedTags);
		$filter->setIsCommentsAllowed($config->getOption('html.commentsAllowed') ? $config->getOption('html.commentsAllowed') : true);

		if (!$config->getOption('html.allowAmp'))
		{
			$content = str_replace(array('&'), array('&amp;'), $content);
//			$content = str_replace(array('&amp;', '&lt;', '&gt;', '&nbsp;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;', '&amp;nbsp;'), $content);
		}

		$content = $filter->filter($content);
		$core->parser->retract($code);

		$content = preg_replace('|<code(?:(?:=([a-z\d#-]+))?(?::((?:[a-z]+\|)*[a-z]+))?)?>(.+?)</code>|ise', "'<code' . (strlen('$1') ? '=$1' : '') . '>' . htmlspecialchars(str_replace('\\\"', '\"', '$3')) . '</code>'", $content);
	}
}

?>