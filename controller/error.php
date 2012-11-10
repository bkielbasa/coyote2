<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Error_Controller extends Controller
{
	function main()
	{
		$this->output->setStatusCode($this->page->getPath());

		if ($this->page->getTemplate())
		{
			$view = new View($this->page->getTemplate());
		}
		else
		{
			$view = $this->page->getContent();
		}

		$this->output->setTitle(sprintf('%s :: %s', $this->page->getSubject(), Config::getItem('site.title')));

		if ($this->page->getContentType())
		{
			$contentType = $this->page->getContentType() . '; charset=utf-8';
			$this->output->setHttpMeta('Content-Type', $contentType);
			$this->output->setContentType($this->page->getContentType());
		}

		Trigger::call('application.onPageDisplay');

		echo $view;
	}
}
?>