<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Preview_Controller extends Controller
{
	function main()
	{
		$content = $this->post->value('text_content');

		$parser = &$this->load->library('parser');
		Load::loadFile('lib/parser/*.php', true);

		$parserIds = (array) $this->input->post['parser'];
		$model = &$this->getModel('parser');

		if ($parserIds)
		{
			$query = $model->select('parser_name')->in('parser_id', $parserIds)->order('parser_order')->get();
			if (count($query))
			{
				foreach ($query as $row)
				{
					$className = 'Parser_' . $row['parser_name'];
					$parser->addParser(new $className);
				}
			}
		}

		$accessor = &$this->load->model('accessor');

		$accessor_arr = array();
		$revisions = array((int)$this->post->revision);

		$page = &$this->getModel('page');
		$template = &$page->template;
		$template_arr = $template->fetchTemplates($content);

		if ($template_arr)
		{
			$quote_arr = array_map(array('Text', 'quote'), $template_arr);
			$template_arr = array();

			$query = $this->db->select('page_id, text_id, text_content, LOWER(location_text) AS location_text')->from('page_v')->where('location_text IN(' . implode(',', $quote_arr) . ')')->get();
			while ($row = $query->fetchAssoc())
			{
				$template_arr[$row['location_text']] = $row['text_content'];
				$revisions[] = $row['text_id'];

				$accessor_arr = array_merge($accessor_arr, $accessor->fetchAccessors($row['text_content']));
			}

			$parser->setOption('wiki.template', $template_arr);
		}		

		$accessor_arr = array_merge($accessor_arr, $accessor->fetchAccessors($content));
		if ($accessor_arr)
		{
			$quote_arr = array_map(array('Text', 'quote'), $accessor_arr);
			$accessor_arr = array();

			$query = $this->db->select('location_page, LOWER(location_text) AS location_text')->from('location')->where('location_text IN(' . implode(',', $quote_arr) . ')')->get();
			while ($row = $query->fetchAssoc())
			{
				$accessor_arr[Text::toLower($row['location_text'])] = array(

					'class'			=>		'accessor'
				);
			}
			if ($accessor_arr)
			{
				$parser->setOption('wiki.accessor', $accessor_arr);
			}
		}
		$attachment = &$page->attachment;
		$attachment_arr = array();

		$query = $attachment->fetch('text_id IN(' . implode(',', $revisions) . ')');
		while ($row = $query->fetchAssoc())
		{
			$attachment_arr[Text::toLower($row['attachment_name'])] = $row;
		}
		if ($this->post->attachment)
		{
			foreach ($this->load->model('attachment')->fetch('attachment.attachment_id IN(' . implode(',', $this->post->attachment) . ')')->fetch() as $row)
			{
				$attachment_arr[Text::toLower($row['attachment_name'])] = $row;
			}
		}
		if ($attachment_arr)
		{
			$parser->setOption('wiki.attachment', $attachment_arr);
		}
		$parser->setOption('wiki.highlightBrokenLinks', true);
		$parser->setOption('tex.url', 'http://4programmers.net/cgi-bin/mimetex2.cgi');
			
		echo $parser->parse($content);
		exit;
	}
}
?>