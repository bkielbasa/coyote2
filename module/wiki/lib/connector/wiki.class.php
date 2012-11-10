<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Wiki extends Connector_Document implements Connector_Interface
{
	/**
	 * Znaczniki HTML dozwolone w artykulach w module wiki
	 */
	private static $allowedTags = array(

		'a' => 'href',
		'b',
		'i',
		'u',
		'cite',
		'strong',
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
		'h1' => array('style'),
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
		'table' => array('style', 'class'),
		'tbody',
		'thead',
		'tfooter',
		'th' => array('style', 'class'),
		'tr' => array('style', 'class'),
		'td' => array('style', 'class'),
		'div' => array('style', 'class'),
		'span' => array('style', 'class'),
		'br',
		'ul' => array('style'),
		'li' => array('style'),
		'ol' => array('style'),
		'wiki' => array('href'),
		'tex',
		'acronym' => array('title'),
		'dl',
		'dd',
		'dt',
		'p'
	);

	function __construct($data = array())
	{
		parent::__construct($data);

		if (!$data)
		{
			$this->setModuleId($this->module->getId('wiki'));
		}
	}

	public static function setAllowedTags($allowedTags)
	{
		self::$allowedTags = $allowedTags;
	}

	public static function getAllowedTags()
	{
		return self::$allowedTags;
	}

	protected function initializeParsers()
	{
		parent::initializeParsers();

		$this->parser->setOption('html.allowTags', self::getAllowedTags());
	}

	public function renderForm()
	{
		parent::renderForm();

		$fieldset = &$this->getFieldset('setting');
		$fieldset->getElement('page_template')->setValue('wikiView.php');

		$this->setDefaults();

	}
}
?>