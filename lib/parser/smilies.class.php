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
class Parser_Smilies implements Parser_Interface
{
	private $smilies = array(
		
		':)'		=> "smile.gif",
		':-)'		=> "smile.gif",
		';)'		=> "wink.gif",
		';-)'		=> "wink.gif",
		':-|'		=> "neutral.gif",
		':D'		=> "laugh.gif",
		':-D'		=> "laugh.gif",
		':('		=> "sad.gif",
		':-('		=> "sad.gif",
		',('		=> "cry.gif",
		',-('		=> "cry.gif",
		':O'		=> "wonder.gif",
		':-O'		=> "wonder.gif",
		':P'		=> "tongue1.gif",
		':p'		=> "tongue1.gif",
		':-P'		=> "tongue1.gif",
		',P'		=> "tongue2.gif",
		',p'		=> "tongue2.gif",
		',-P'		=> "tongue2.gif",
		',)'		=> "wink.gif",
		',-)'		=> "wink.gif",
		':-/'		=> "confused.gif",
		':/'		=> "damn.gif",
		':['		=> "mad.gif",
		':-['		=> "mad.gif",
		'8-O'		=> "eyes.gif",
		':|'		=> "zonk.gif",
		'[sciana]'	=> "wall.gif",
		'[glowa]'	=> "wall.gif",
		'[diabel]'	=> "devil.gif",
		'[???]'		=> "how.gif",
		'[!!!]'		=> "exclaim.gif",
		'[green]'	=> "green.gif",
		'[browar]'	=> "beer.gif",
		'[soczek]'	=> "juice.gif",
		'[rotfl]'	=> "rotfl.gif",
		':]'		=> "squared.gif",
		',]'		=> "squared.gif",
		'[wstyd]'	=> "shame.gif",
		':d'		=> "teeth.gif"
	);

	public function parse(&$content, Parser_Config_Interface &$config)
	{
		if ($config->getOption('smilies.disable') == true)
		{
			return false;
		}
		/* tablice statyczne */
		static $patterns = array();
		static $replacements = array();
		
		$code = Core::getInstance()->parser->extract('code|tt|kbd|samp');

		$baseDir = $config->getOption('smilies.baseDir') ? $config->getOption('smilies.baseDir') : 'template/img/smilies/';
		$baseUrl = Url::base() . $baseDir;

		if (!$patterns)
		{
			while (list($var, $value) = each($this->smilies))
			{
				$patterns[] =	'#(?<=^|[\n ]|\.)' . preg_quote($var, '#') . '#';
				$replacements[] =	'<img alt="' . $var	. '" title="' . $var . '" src="'	. $baseUrl . $value . '" />';
			}
			reset($this->smilies);
		}

		/* zamiana */
		$content = substr(preg_replace($patterns, $replacements, ' ' . $content	. '	'),	1, -1);
		Core::getInstance()->parser->retract($code);
	}
}

?>