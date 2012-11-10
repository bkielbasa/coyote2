<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa parsuje mechanizm wiki stosowany w 4programmers.net na znaczniki HTML
 */
class Parser_Wiki implements Parser_Interface
{
	/**
	 * Na podstawie parametru $value tworzy parametr ID dla znacznika HTML
	 * @param string $value
	 * @return string
	 */
	private function generateAnchor($value)
	{
		return 'id-' . preg_replace('#[^0-9a-zA-Z\-_]+#', '', str_replace(' ', '-', strip_tags($value)));
	}

	private function setWikiHeadline($matches)
	{
		$title = trim($matches[2]);
		$anchor = $this->generateAnchor($title);

		if ($matches[1])
		{
			$depth = strlen($matches[1]);
			return ("<h{$depth} id=\"$anchor\">$title</h{$depth}>");
		}
		else
		{
			return $matches[0];
		}
	}

	private function setCoyoteHeadline($matches)
	{
		$anchor = $this->generateAnchor($matches[1]);

		/* poziom	nadajemy na	podstawie pierwszego znaku */
		switch ($matches[2][1])
		{
			case '=':

			$depth = 1;
			break;

			case '-':

			$depth = 2;
			break;

			case '~':

			$depth = 3;
			break;
		}

		/* formatujemy naglowek HTML */
		return ("<h{$depth} id=\"$anchor\">$matches[1]</h{$depth}>");
	}

	/**
	 * Funkcja formatuje naglowki	w tekscie i	tworzy z nich spis tresci
	 * @param string &$content Tresc parsownego tekstu
	 */
	private function formatHeadline(&$content)
	{
		$content = preg_replace_callback('#^(={1,6}) (.*?) \1(?=\s|$)#m', array(&$this, 'setWikiHeadline'), $content);
		/* zamiana skladnii Coyote'a na HTML */
		$content =	preg_replace_callback("#(.*?)\n([=\-\~]+)\n#", array(&$this, 'setCoyoteHeadline'), $content);

		/*
		 * Usuniecie znakow nowej linii na koncu znacznika </h*>. Jest to dosc nietypowe i niezbyt "ladne"
		 * rozwiazanie
		 */
		$content = preg_replace('#<\/h([1-6])>\n#', '</h\\1>', $content);

		/* warunek sprawdza, czy w tekscie znajduje sie fraza	{{CONTENT}}	*/
		if (strpos($content, '{{CONTENT}}', 0) !== false && !$this->config->getOption('wiki.disableContent'))
		{
			/* odczyt	naglowkow (potrzebny do	spisu tresci) */
			preg_match_all('#<h([1-6])( id="([0-9a-zA-Z\-_]+)")?>(.*?)<\/h([1-6])>#i',	$content, $matches);

			$headline	= '';
			$level = $prev_level = 0;

			/* petla po tablicy z	naglowkami */
			for ($i = 0, $headline_count	= sizeof($matches[0]); $i <	$headline_count; $i++)
			{
				$number_str =	'';

				/* ponizsze instrukcje maja okreslic ile jest	naglowkow o	danym "zaglebieniu"	*/
				if ($level)
				{
					$prev_level =	$level;
				}
				$level = $matches[1][$i];

				if ($prev_level && $level > $prev_level)
				{
					$sub_ary[$level] = 0;
				}
				elseif ($level < $prev_level)
				{
					$sub_ary[$level + 1] = 0;
				}
				if (isset($sub_ary[$level]))
				{
					$sub_ary[$level]++;
				}
				else
				{
					$sub_ary[$level] = 1;
				}

				$dot_str = false;

				/* ponizsza petla	ustala numerowanie - np. 1.1.2.2 */
				for ($j = 1; $j <= $level; $j++ )
				{
					if (!empty($sub_ary[$j]))
					{
						if ($dot_str)
						{
							$number_str .= '.';
						}
						$number_str .= $sub_ary[$j];
						$dot_str = true;
					}
				}

				if (!$matches[3][$i])
				{
					$matches[3][$i] =	$this->generateAnchor($matches[4][$i]);

					$content =	str_replace($matches[0][$i], '<h' .	$matches[1][$i]	. '	id="' .	$matches[3][$i]	. '">' . $matches[4][$i] . '</h' . $matches[1][$i] . '>', $content);
				}
				/* formatowanie linka	spisu tresci */
				$url = '<a href="#' .	$matches[3][$i]	. '">' . $number_str . ' ' . $matches[4][$i] . '</a>';

				$headline  .= str_repeat('&nbsp;',	$level * 5)	. $url . "\n";
			}
			/* formatowanie spisu	tresci */
			$headline	= "<div class=\"page-content\"><strong>Spis treści</strong>\n\n"	. $headline	. '</div>';
			/* tutaj wlasciwa	operacja - zastapienie znacznika, wlasciwym	spisem tresci */
			$content =	str_replace('{{CONTENT}}', $headline, $content);
		}
	}

	private function formatTypography(&$content)
	{
		$pattern = array(
			'>>',
			'<<',
			'<=>',
			'->',
			'<-',
			'=>',
			'<=',
			'(tm)',
			'(r)',
			'(c)',
		);
		if (!$this->config->getOption('html.allowAmp'))
		{
			$pattern = array_map('htmlspecialchars', $pattern);
		}

		$content = str_replace(
			$pattern,
			array(
				'&raquo;',
				'&laquo;',
				'&hArr;',
				'&rarr;',
				'&larr;',
				'&rArr;',
				'&lArr;',

				'&trade;',
				'&reg;',
				'&copy;'

			),
		$content);

		// zamiana cudzyslowi na cudzyslowia drukarskie
		//$content = preg_replace('#[^=]"([^\'\n]+)"[ \n]#', '&bdquo;$1&rdquo;', $content);
	}

	/**
	 * Ustawia formatowanie tekstu. Pogrubienie <b> oraz kursywa <i>
	 * @param string $content Referencja do tresci
	 * @return string
	 */
	private function formatStyle(&$content)
	{
		$content = preg_replace(
			array(
				'#(^|[\n\t (>])__([^\n\_]+)__#',
				'#(^|[\n\t (>])//([^\n<>]+?)(?<!:)//#',
				'#\*\*([^\n]+?)\*\*#',
				//'#--(.+?)--#',
				'#,,([^\n\,]+),,#',
				'#\^([^\n\^\, ]+)\^#',
				'#@@([^\n\@]+)@@#'
			),
			array(
				'$1<u>$2</u>',
				'$1<em>$2</em>',
				'<strong>$1</strong>',
				//'<del>$1</del>',
				'<sub>$1</sub>',
				'<sup>$1</sup>',
				'<var>$1</var>'
			),
		$content);
	}

	/**
	 * Formatuje listy punktowane oraz numerowane
	 * @param string $content Referncja do tresci
	 * @return string
	 */
	private function formatList(&$content)
	{
		$line_arr = explode("\n", $content);
		$line_arr[] = ' ';
		$root = array();

		foreach ($line_arr as $line_n => $line)
		{
			$char = isset($line{0}) ? $line{0} : '';
			$indent =	strspn($line, '#*');

			if ($char == '#' || $char == '*')
			{
				if ($char == '#')
				{
					$root_tag = 'ol';
				}
				else
				{
					$root_tag = 'ul';
				}
				$value = substr($line, $indent);
				$line = '';

				if (!isset($root[$indent]) || !$root[$indent])
				{
					$line = "<$root_tag>";
					$root[$indent] = true;
					$count = $indent -1;

					while ($count > 0 && (!isset($root[$count]) || !$root[$count]))
					{
						$line .= "<$root_tag>";
						--$count;
					}

					unset($count);
				}
				$line_arr[$line_n] = ($line .= '<li>' . $value . '</li>');

				if (($curr = strspn($line_arr[$line_n + 1], $char)) < $indent)
				{
					while ($curr != $indent)
					{
						$line_arr[$line_n] .= "</$root_tag>";
						$root[$indent] = false;
						--$indent;
					}
				}
			}
		}

		array_pop($line_arr);
		$content = implode("\n", $line_arr);
	}

	/**
	 * Formatuje tabele
	 * @param string $content Referncja do tresci
	 * @return string
	 */
	private function formatTable(&$content)
	{
		$line_arr = explode("\n", $content);
		$table = false;

		foreach ($line_arr as $line_n => $line)
		{
			if (strspn($line, '||', 0, 2) == 2)
			{
				$colspan = false;
				$td = 'td';

				$limit = 2;

				if (strlen($line) >= 3)
				{
					if ($line{2} == '-')
					{
						$colspan = true;
						++$limit;
					}
					elseif ($line{2} == '=')
					{
						$td = 'th';
						++$limit;
					}
				}
				$line = substr($line, $limit);
				$cols = explode('||', $line);

				$line_arr[$line_n] = '';
				if (!$table)
				{
					$line_arr[$line_n] = '<table>';
					$table = true;
				}

				$line_arr[$line_n] .= '<tr>';

				$cols_n = count($cols);
				foreach ($cols as $col)
				{
					$col = trim($col);
					if ($colspan)
					{
						$line_arr[$line_n] .= '<' . $td .' colspan="' . $cols_n . '">' . $col . "</$td>";
					}
					else
					{
						$line_arr[$line_n] .= "<$td>$col</$td>";
					}
				}
				$line_arr[$line_n] .= '</tr>';

				if (!isset($line_arr[$line_n + 1]) || !strspn($line_arr[$line_n + 1], '||', 0, 2))
				{
					$table = false;
					$line_arr[$line_n] .= '</table>';
				}
			}
		}
		$content = implode("\n", $line_arr);
	}

	/**
	 * Funkcja zwrotna - ustawianie przypisu przy	pomocy znacznika <sup>
	 */
	private function setFootnotes($counter)
	{
		/* formatowanie kodu HTML	*/
		return ('<a href="#foot-' . $this->config->getOption('footnotes.prefix') .  $counter .	'"><sup>' .	$counter . '</sup></a>');
	}

	/**
	 * Funkcja zwrotna - formatowanie	przypisu na	stronie
	 */
	private function getFootnotes($footnotes)
	{
		return '<a name="foot-' . $this->config->getOption('footnotes.prefix') . $footnotes .	'"></a><b>[' . $footnotes .	']</b>';
	}

	/**
	 * Formatuje pzypisy w tekscie
	 * @param string $content Referencja do tresci
	 * @return string
	 */
	private function formatFootnotes(&$content)
	{
		$counter = 0;

		/* ustaw przypisy	*/
		$content =	preg_replace("#\[\#\]_#ie",	"\$this->setFootnotes(++\$counter)", $content);

		$counter = 0;
		/* objasnienie przypisow */
		$content =	preg_replace("#^\.\. \[\#\]#ime", '$this->getFootnotes(++$counter)', $content);
	}

	/**
	 * Formatuje odnosniki w tekscie, do innych stron
	 * @param string $content Referencja do tresci
	 * @return string
	 */
	private function formatAccessor(&$content)
	{
		preg_match_all('/\[\[(.*?)(\|(.*?))*\]\]/i', $content, $matches);
		if (!$matches[0])
		{
			return;
		}
		$encoder = new Path;

		$ref_arr = $this->config->getOption('wiki.accessor');
		$base_url = $this->config->getOption('wiki.accessorUrl') ? $this->config->getOption('wiki.accessorUrl') : Url::base();

		for ($i = 0, $limit = sizeof($matches[0]); $i < $limit; $i++)
		{
			$subject = $matches[3][$i];
			if (!$subject)
			{
				if (strpos($matches[1][$i], '/') === false)
				{
					$subject = $matches[1][$i];
				}
				else
				{
					$subject = end(explode('/', $matches[1][$i]));
				}
			}
			$attr_arr = array();
			$path = $matches[1][$i];

			$path = explode('/', $path);
			foreach ($path as $index => $part)
			{
				$path[$index] = $encoder->encode($part);
			}
			$path = implode('/', $path);

			if (isset($ref_arr[mb_strtolower($path)]))
			{
				$data = &$ref_arr[mb_strtolower($path)];
				$attr_arr['class'] = isset($data['class']) ? $data['class'] : '';
				$attr_arr['title'] = isset($data['title']) ? $data['title'] : '';
			}
			else
			{
				if ($this->config->getOption('wiki.highlightBrokenLinks'))
				{
					$attr_arr['class'] = 'broken';
					$attr_arr['rel'] = 'nofollow';
					$attr_arr['title'] = 'Ten dokument nie istnieje.';
				}
			}

			$accessor_arr[] = Html::a($base_url . $path, $subject, $attr_arr);
		}
		$content = str_replace($matches[0], $accessor_arr, $content);
	}

	/**
	 * Formatuje szablony, w miejsce znacznika wstawiana jest zawartosc tekstu o podanym URL
	 * @param string $content Referencja do tresci
	 * @return string
	 */
	private function formatTemplate(&$content)
	{
		preg_match_all("#{{Template:(.*?)(\|(.*))*}}#i", $content, $matches);
		if (!$matches[0])
		{
			return;
		}
		if (!$template_arr = &$this->config->getOption('wiki.template'))
		{
			return;
		}
		$encoder = new Path;

		$replacement_arr = array();
		for ($i = 0, $limit = sizeof($matches[0]); $i < $limit; $i++)
		{
			$path = explode('/', $matches[1][$i]);

			foreach ($path as $index => $part)
			{
				$path[$index] = $encoder->encode($part);
			}
			$path = implode('/', $path);
			$path = mb_strtolower($path, 'UTF-8');

			// zawartosc szablonu
			$template = isset($template_arr[$path]) ? $template_arr[$path] : '';

			// argumenty (wartosci), ktore maja zostac przekazane do szablonu
			$args = $matches[3][$i] ? explode('|', $matches[3][$i]) : array();
			if ($args)
			{
				foreach ($args as $index => $value)
				{
					$template = str_replace('{{' . ($index + 1) . '}}', $value, $template);
				}
			}
			$replacement_arr[$matches[0][$i]] = $template;
		}

		if ($replacement_arr)
		{
			$content = str_replace(array_keys($replacement_arr), array_values($replacement_arr), $content);
		}
	}

	/**
	 * Formatuje zalaczniki oraz obrazy. Zamienia znaczniki na zalaczniki dodane w tekscie
	 * @param string $content Referencja do tresci
	 * @return string
	 */
	private function formatAttachment(&$content)
	{
		preg_match_all("#{{(Image|File):(.*?)(\|(.*))*}}#i", $content, $matches);
		if (!$matches[0])
		{
			return;
		}
		if (!$attachment_arr = &$this->config->getOption('wiki.attachment'))
		{
			return;
		}

		$baseDir = $this->config->getOption('wiki.attachmentDir') ? $this->config->getOption('wiki.attachmentDir') : 'store/_aa/';
		$baseUrl = $this->config->getOption('wiki.attachmentUrl') ? $this->config->getOption('wiki.attachmentUrl') : 'Attachment/Get/';

		$replacement_arr = array();
		for ($i = 0, $limit = sizeof($matches[0]); $i < $limit; $i++)
		{
			$result = '';
			$matches[2][$i] = Text::toLower($matches[2][$i]);

			if (isset($attachment_arr[$matches[2][$i]]))
			{
				$attr_arr = array();
				$row = &$attachment_arr[$matches[2][$i]];

				/**
				 * @todo
				 *
				 * Znaczenie warunku jest jasne: kazdy zalacznik moze znajdowac sie w innym folderze.
				 * Nalezy dodac kolumne attachment_path do tabeli attachment
				 */
				if (!empty($row['attachment_path']))
				{
					$baseDir = $row['attachment_path'];
				}

				if (strcasecmp($matches[1][$i], 'Image') == 0)
				{
					@list($alt, $width) = explode('|', $matches[4][$i]);
					$alt = htmlspecialchars($alt); // kwestia bezpieczenstwa
					$width = (int) $width;

					if ($width > 10 && $width <= $row['attachment_width'])
					{
						if (!file_exists("{$baseDir}{$width}-" . $row['attachment_file']))
						{
							$image = &Load::loadClass('image');
							$image->open($baseDir . $row['attachment_file']);

							$scale = $image->getHeight() / $image->getWidth();
							$height = $width * $scale;

							$image->scale($width, $height);
							$image->save("{$baseDir}{$width}-" . $row['attachment_file']);
							$image->close();
						}
					}
					else
					{
						$width = null;
					}

					$result = Html::img(url($baseDir . ($width ? $width . '-' : '') . $row['attachment_file']), array(
						'width'		=> $width ? $width : $row['attachment_width'],
						'alt'		=> $alt,
						'title'		=> $alt
						)
					);

					if ($baseUrl)
					{
						$result = Html::a(url($baseUrl . $row['attachment_id']), $result);
					}
				}
				else
				{
					$result = ' <sup>(' . Text::fileSize($row['attachment_size']) . ')</sup>';

					if ($baseUrl)
					{
						$result = Html::a(url($baseUrl . $row['attachment_id']), $row['attachment_name']) . $result;
					}
					else
					{
						$result = $row['attachment_name'] . $result;
					}
				}
			}

			$replacement_arr[$matches[0][$i]] = $result;
		}

		if ($replacement_arr)
		{
			$content = str_replace(array_keys($replacement_arr), array_values($replacement_arr), $content);
		}
	}

	/**
	 * Formatowanie wzorow w formacie TEX
	 */
	private function formatTex(&$content)
	{
		if (!$texUrl = $this->config->getOption('tex.url'))
		{
			return false;
		}

		$content = preg_replace("#<tex>(.*?)</tex>#sie", "'<img src=\"" . $texUrl . "?' . rawurlencode('$1') . '\" alt=\"' . htmlspecialchars('$1') . '\" />'", $content);
	}

	public function parse(&$content, Parser_Config_Interface &$config)
	{
		$this->config = &$config;
		$core = &Core::getInstance();

		if (!$config->getOption('wiki.disableTemplate'))
		{
			$this->formatTemplate($content);
		}
		$code = $core->parser->extract('code');

		$content = preg_replace('#%%(.+?)%%#', '<plain>$1</plain>', $content);
		$plain = $core->parser->extract('plain');

		/*
		 * Zamiana ''foo'' na <tt>foo</tt> oraz `bar` na <kbd>bar</kbd>
		 * Pomiedzy tymi znacznikami nie bedzie formatowania wiki, a kod html nie bedzie wyswietlany
		 */
		$content = preg_replace(
			array(
				'#\'\'([^\n]+?)\'\'#',
				'#`([^\n]+?)`#',
			),
			array(
				'<tt>$1</tt>',
				'<kbd>$1</kbd>',
			),
		$content);

		/**
		 * @todo To mozna zrobic jednym regexpem
		 */
		$content = preg_replace('#<tt>([^\n]+?)</tt>#ie', "'<tt>'.htmlspecialchars(str_replace('\\\"', '\"', htmlspecialchars_decode('$1'))).'</tt>'", $content);
		$content = preg_replace('#<kbd>([^\n]+?)</kbd>#ie', "'<kbd>'.htmlspecialchars(str_replace('\\\"', '\"', htmlspecialchars_decode('$1'))).'</kbd>'", $content);

		$tt = Core::getInstance()->parser->extract('tt|kbd|samp|a');

		if (!$config->getOption('wiki.disableTex'))
		{
			$this->formatTex($content);
		}
		if (!$config->getOption('wiki.disableStyle'))
		{
			$this->formatStyle($content);
		}
		if (!$config->getOption('wiki.disableTypography'))
		{
			$this->formatTypography($content);
		}
		if (!$config->getOption('wiki.disableImage'))
		{
			$this->formatAttachment($content);
		}
		if (!$config->getOption('wiki.disableHeadline'))
		{
			$this->formatHeadline($content);
		}
		if (!$config->getOption('wiki.disableList'))
		{
			$this->formatList($content);
		}
		if (!$config->getOption('wiki.disableTable'))
		{
			$this->formatTable($content);
		}
		if (!$config->getOption('wiki.disableFootnotes'))
		{
			$this->formatFootnotes($content);
		}
		if (!$config->getOption('wiki.disableAccessor'))
		{
			$this->formatAccessor($content);
		}

		/* znaki ____ zostana zamienione na znacznik <hr> */
        $content = preg_replace('/(^|\n)____*/m', '\\1<hr />', $content);

		$core->parser->retract($tt);
		$core->parser->retract($plain);
		$core->parser->retract($code);
	}
}

?>