<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Forum extends Context
{
	public static function loadParsers($enableHtml = false, $enableSmilies = true, $attachments = array())
	{
		$core = self::getInstance();

		$core->getLibrary('parser');
		$core->parser->removeParsers();

		if (!$enableHtml)
		{
			/*
			 * Domyslne dzialanie to usuniecie zbednych znacznikow HTML.
			 * Jest to podstawowe dzialanie zwiazane z kwestia bezpieczenstwa.
			 *
			 * Ten parser zawsze musi byc wywolywany jako pierwszy, aby pozostawic
			 * do dalszej obrobki tylko te znaczniki HTML ktore sa dozwolone do uzycia
			 */
			$core->parser->addParser(new Parser_Html);
		}

		/*
		 * Parsowanie skladni Wiki
		 */
		$core->parser->addParser(new Parser_Wiki);

		/*
		 * Parser adresow URL. Glownie sluzy do tego, aby wyszukiwac ciagi mogace byc
		 * adresami URL i zamieniac je na "klikalne". Musi byc umieszczony ZA parserem
		 * Wiki poniewaz taka skladnia (''http://foo.com'') nie powinna zamieniac
		 * tekstu na znacznik <a>
		 *
		 * Parser ten nie dokonuje zmiany w wewnatrz znacznikow <code>, <tt>, <kbd>, <samp>
		 */
		$core->parser->addParser(new Parser_Url);

		/*
		 * Parser cenzurowania moze generowac kod HTML. Stad musi byc umiesczony ZA parserem HTML,
		 * ktory moglby taki kod HTML - usunac. Jak mozna zauwazyc, ze parser Censore jest umiesczony
		 * ZA parserami Wiki oraz Url z uwagi na to, ze tamte parsery moga wyprodukowac tagi
		 * <a>, <code> czy <kbd>, a wewnatrz tych znacznikow nie dokonujemy cenzury
		 */
		$core->parser->addParser(new Parser_Censore);

		if ($enableSmilies)
		{
			/*
			 * Zamienia tekst na znaczniki <img /> stad musi byc umieszczony ZA parserem HTML
			 *
			 * Parser ten nie dokonuje zmiany w wewnatrz znacznikow <code>, <tt>, <kbd>, <samp>
			 */
			$core->parser->addParser(new Parser_Smilies);
		}

		/*
		 * Kolorowanie skladni. Ten parser koloruje skladnie pomiedzy
		 * znacznikami <code>
		 */
		$core->parser->addParser(new Parser_Highlight);
		$core->parser->addParser(new Parser_Forum);
		$core->parser->addParser(new Parser_Login);
		$core->parser->addParser(new Parser_Br);

		$core->parser->setOption('wiki.disableTemplate', true);
		$core->parser->setOption('wiki.disableTypography', true);
		$core->parser->setOption('wiki.disableHeadline', true);
		$core->parser->setOption('tex.url', 'http://4programmers.net/cgi-bin/mimetex2.cgi');

		$allowedTags = array(

			'a' => 'href',
			'b',
			'i',
			'u',
			'del',
			'strong',
			'tt',
			'dfn',
			'ins',
			'pre',
			'blockquote',
			'hr',
			'sub',
			'sup',
			'font' => array('size', 'color'), // deprecated
			'ort',
			'wiki' => 'href',
			'image',
			'img' => array('src', 'alt'),
			'email',
			'url' => '*',
			'quote' => '*',
			'code' => '*',
			'nobr',
			'plain',
			'tex',
			'ul' => array('class'),
			'li',
			'em'
		);
		$core->parser->setOption('html.allowTags', $allowedTags);
		$wikiAttachment = array();

		if ($attachments)
		{
			$image = new Image;

			foreach ($attachments as $id => $fileName)
			{
				$width = $height = 0;

				if (file_exists('tmp/' . $id))
				{
					$path = 'tmp/';
				}
				else
				{
					$path = 'store/forum/';
				}

				if (in_array(pathinfo(Text::toLower($id), PATHINFO_EXTENSION), array('jpg', 'gif', 'png')))
				{
					$image->open($path . $id);

					$width = $image->getWidth();
					$height = $image->getHeight();
					$image->close();
				}

				$wikiAttachment[Text::toLower($fileName)] = array(

					'attachment_id'					=> 0,
					'attachment_name'				=> $fileName,
					'attachment_file'				=> $id,
					'attachment_width'				=> $width,
					'attachment_height'				=> $height,
					'attachment_size'				=> filesize($path . $id),
					'attachment_path'				=> $path
				);
			}
		}

		$core->parser->setOption('wiki.attachmentUrl', '');
		$core->parser->setOption('wiki.attachment', $wikiAttachment);
	}

	public static function getLogins(&$content)
	{
		$core = self::getInstance();
		$core->getLibrary('parser');

		$core->parser->removeParsers();

		/**
		 * Obiekt klasy Parser_Login (parsujemy komentarze)
		 */
		$parser = new Parser_Login;
		$config = new Parser_Config;

		$content = Text::transformEmail(Text::transformUrl($content, 70));
		// parsowanie komentarza i zwrocenie ID uzytkownikow, ktorych loginy znajduja sie w parsowanym komentarzu
		return $parser->parse($content, $config);
	}
}
?>