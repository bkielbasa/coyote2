<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_Error extends Connector_Document implements Connector_Interface
{
	/**
	 * Kody statusow HTTP
	 */
	protected $statusText = array(

			'100'	=> '[100] Continue',
			'101'	=> '[101] Switching Protocols',
			'200'	=> '[200] OK',
			'201'	=> '[201] Created',
			'202'	=> '[202] Accepted',
			'203'	=> '[203] Non-Authoritative Information',
			'204'	=> '[204] No Content',
			'205'	=> '[205] Reset Content',
			'206'	=> '[206] Partial	Content',
			'300'	=> '[300] Multiple Choices',
			'301'	=> '[301] Moved Permanently',
			'302'	=> '[302] Found',
			'303'	=> '[303] See Other',
			'304'	=> '[304] Not Modified',
			'305'	=> '[305] Use Proxy',
			'306'	=> '[306] (Unused)',
			'307'	=> '[307] Temporary Redirect',
			'400'	=> '[400] Bad Request',
			'401'	=> '[401] Unauthorized',
			'402'	=> '[402] Payment Required',
			'403'	=> '[403] Forbidden',
			'404'	=> '[404] Not Found',
			'405'	=> '[405] Method Not Allowed',
			'406'	=> '[406] Not Acceptable',
			'407'	=> '[407] Proxy Authentication Required',
			'408'	=> '[408] Request Timeout',
			'409'	=> '[409] Conflict',
			'410'	=> '[410] Gone',
			'411'	=> '[411] Length Required',
			'412'	=> '[412] Precondition Failed',
			'413'	=> '[413] Request Entity Too Large',
			'414'	=> '[414] Request-URI Too Long',
			'415'	=> '[415] Unsupported Media Type',
			'416'	=> '[416] Requested Range Not	Satisfiable',
			'417'	=> '[417] Expectation Failed',
			'500'	=> '[500] Internal Server	Error',
			'501'	=> '[501] Not Implemented',
			'502'	=> '[502] Bad Gateway',
			'503'	=> '[503] Service Unavailable',
			'504'	=> '[504] Gateway Timeout',
			'505'	=> '[505] HTTP Version Not Supported'
	);
	
	protected $vars = array();

	public function renderForm()
	{
		parent::renderForm();

		$this->removeFieldset('meta');
		$this->getFieldset('content')->removeElement('page_parent');
		$this->getFieldset('content')->removeElement('page_path');
		$this->getFieldset('content')->removeElement('page_title');

		$this->getFieldset('setting')->removeElement('page_publish');
		$this->getFieldset('setting')->removeElement('page_published');
		$this->getFieldset('setting')->removeElement('page_unpublished');
		$this->getFieldset('setting')->removeElement('linekreak1');
		$this->getFieldset('setting')->removeElement('page_cache');

		$element = &$this->getFieldset('content')->getElement('page_subject');
		$element->setLabel('Tytuł strony');
		$element->setDescription('Tytuł, nagłówek strony z komunikatem błędu');

		$this->getFieldset('content')->createElement('select', 'page_path',
			array(
			),
			array(
				'MultiOptions'			=> $this->statusText,
				'Label'					=> 'Kod błędu wysyłanego w nagłówku HTTP',
				'Filters'				=> array('int')
			)
		);

		$this->setDefaults();
	}

	public function onBeforeSave()
	{
		// ID modulu glownego
		$moduleId = $this->module->getId('main');
		$values = $this->getValues();

		// dokument macierzysty (ID)
		$this->setParentId(0);

		// tytul strony
		$this->setSubject(@$values['page_subject']);

		$this->setPath(@$values['page_path']);
		$this->setModuleId($moduleId);
		$this->setConnectorId(@$values['page_connector']);
		$this->setContent(@$values['text_content']);
		$this->setContentType(@$values['page_content']);
		$this->setLog(@$values['log']);
		$this->setIp($this->input->getIp());
		$this->setRichTextId(@$values['page_richtext']);
		$this->setIsPublished(true);
		$this->setIsCached(false);
		$this->setTemplate(@$values['page_template']);
	}
	
	public function addVar($var, $value)
	{
		$this->vars[$var] = $value;
		return $this;
	}
	
	public function getVar($var)
	{
		return isset($this->vars[$var]) ? $this->vars[$var] : null;
	}
	
	protected function initializeParsers()
	{
		parent::initializeParsers();
		$this->parser->addParser(new Parser_Var);
		
		$this->parser->setOption('vars', $this->vars);
	}
}
?>