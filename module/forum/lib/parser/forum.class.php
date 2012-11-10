<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa parsera zwiazana ze wsteczna kompatybilnoscia w serwisie 4programmers.net
 * Wyrazenia regularne zamieniajaca znaczniki charakterystyczna dla forum 4programmers.net,
 * na tradycyjne znaczniki HTML
 */
class Parser_Forum implements Parser_Interface
{
	private $post;

	public function parse(&$content, Parser_Config_Interface &$config)
	{
		if ($this->post = $config->getOption('quote.postId'))
		{
			$content = preg_replace_callback("#<quote=\"(\d+)\"\>#is", array(&$this, 'setAnchor') , $content);
		}

		/* tablica wyrazen regularnych do zastapienia w tekscie */
		$regexp = array(
			'pattern'	=> array(
				"#<ort>([^<]*)</ort>#sie",
				"#<quote=(.*?)\>#is",
				"#<quote>#i",
				"#</quote>#i"
			),

			'replacement'	=> array(
				"'<span style=\"color: #666\" title=\"'.htmlspecialchars(stripslashes('$1')).'\">[błąd ortograficzny]</span>'",
				'<blockquote class="quote"><strong class="quote-user">$1:</strong>',
				"<blockquote class=\"quote\">",
				"</blockquote>"
			)
		);

		/* wykonanie wyrazen regularnych */
		$content = preg_replace($regexp['pattern'], $regexp['replacement'], $content);
	}

	private function setAnchor($matches)
	{
		$html = '<blockquote class="quote">';

		if (isset($this->post[$matches[1]]))
		{
			$html .= '<strong class="quote-user">';

			$post = &$this->post[$matches[1]];
			$html .= '<a title="Przejdź do cytowanego postu" class="quote-post" href="' . url($post['location_text']) . '?p=' . $post['post_id'] . '#id' . $post['post_id'] . '"></a> ' . $post['post_user'] . ' napisał(a) ' . User::formatDate($post['post_time']) . ':</strong>';
		}

		return $html;
	}
}

?>