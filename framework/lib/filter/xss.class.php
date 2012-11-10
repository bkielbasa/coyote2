<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Filtr usuwajacy potencjalnie niebezpieczne znaki z tekstu
 * Takie jak znaczniki HTML czy atrybuty tych znacznikow
 * Filtruje tekst pod katem luk XSS
 */
class Filter_XSS implements IFilter
{
	private $syntax = array(		
	
				'document.cookie'	=> '',
				 'document.write'	=> '',
				 '.parentNode'		=> '',
				 '.innerHTML'		=> '',
				 'window.location'	=> '',
				 '-moz-binding'		=> '',
				 '<!--'				=> '&lt;!--',
				 '-->'				=> '--&gt;',
				 '<!CDATA['			=> '&lt;![CDATA['
	);

	private	$events = array(
		
				'onblur',
				'onchange',
				'onclick',
				'onfocus',
				'onload',
				'onmouseover',
				'onmouseup',
				'onmousedown',
				'onselect',
				'onsubmit',
				'onunload',
				'onkeypress',
				'onkeydown',
				'onkeyup',
				'onresize', 
				'xmlns'
				
	);


	function __construct($syntax = null)
	{
		if ($syntax)
		{
			$this->syntax = $syntax;
		}
	}

	public function filter($value)
	{ 		
		$value = str_replace(array_keys($this->syntax), array_values($this->syntax), $value);
		$value = preg_replace("#<([^>]+)(" . implode('|', $this->events) . ")([^>]*)>#iU", "&lt;\\1\\2\\3&gt;", $value);
		
		$value = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2', $value);
		$value = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $value);
		$value = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $value);
		
		$value = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#is', '$1>', $value);
		$value = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#is', '$1>', $value);
		$value = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#ius', '$1>', $value);

		$value = preg_replace('#<(/*\s*)(alert|applet|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|layer|link|meta|object|plaintext|style|script|textarea|title|xml|xss)([^>]*)>#is', "&lt;\\1\\2\\3&gt;", $value);

		return $value;	
	}
}
?>