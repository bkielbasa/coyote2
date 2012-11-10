<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Snippet_News extends Snippet
{
	/**
	 * Limit wyswietlanych naglowkow
	 */
	public $newsLimit = 5;
	/**
	 * Okresla, czy stronnicowanie powinno byc wlaczone (true)
	 * czy tez nie (false)
	 */
	public $enablePagination = false;
	
	public function display(IView $instance = null)
	{
		$news = &$this->getModel('news');
		$result = $news->getTopNews(null, (int) $this->input->get['start'], $this->newsLimit);
		
		$maxLength = $this->module->news('snippetLimit', $this->page->getId());
		$store = $this->module->news('store', $this->page->getId());
		
		if ($instance != null)
		{
			$instance->result = $result;
		}
		else
		{
			$instance = new View('_partialNews', array(
				'news'			=> $result,
				'maxLength'		=> $maxLength,
				'store'			=> $store,
				'location'		=> Path::connector('newsHome')
				)
			);
			
							
			if ($this->enablePagination)
			{
				$instance->pagination = new Pagination('', $news->getFoundRows(null, ''), $this->newsLimit, (int) $this->input->get['start']);
			}			
		}
		
		$this->output->addStylesheet('../module/news/template/css/news');
		$this->output->addJavascript('../../module/news/template/js/news');
		
		echo $instance;		
	}
	
	public function __toString()
	{
		return (string) $this->display();
	}
}
?>