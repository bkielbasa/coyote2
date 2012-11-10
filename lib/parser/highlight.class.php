<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa parsera sluzaca do kolorowania skladnii
 */
class Parser_Highlight implements Parser_Interface
{
	function __construct()
	{
		// ladowanie zewnetrznej biblioteki - geshi
		Load::loadFile('lib/geshi/geshi.php');
	}

	public function parse(&$content, Parser_Config_Interface &$config)
	{
		/* wyrazenie regularne "wyciaga" tekst pomiedzy znacznikiem <code> */
		preg_match_all('|<code(?:(?:=([a-z\d#-]+))?(?::((?:[a-z]+\|)*[a-z]+))?)?>(.+?)</code>|is', $content, $snippets);

		/* nie podejmuj zadnej akcji jezeli nie ma w tekscie znacznika <code>	*/
		if (count($snippets[0]) == 0)
		{
			return;
		}

		/* tworzenie klasy kolorowania skladnii */
		$geshi = new GeSHi('', '');
		/* tekst bedzie zawarty w	znaczniku <pre>	*/
		$geshi->set_header_type(GESHI_HEADER_PRE);

		$snippets_count = sizeof($snippets[1]);

		for ($i = 0; $i < $snippets_count; $i++)
		{
//			if (count(explode("\n", $snippets[3][$i])) > 10)
//			{
//				/* numerowanie wierszy jezeli  */
//				$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
//			}
//			else
//			{
//				$geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS);
//			}

			//$snippets[3][$i] = trim($snippets[3][$i],	"\n");

			if (!$snippets[1][$i])
			{
				$snippets[1][$i] = 'text';
			}
			/* ustaw kod zrodlowy	*/
			$geshi->set_source(htmlspecialchars_decode($snippets[3][$i]));
			/* nadaj jezyk kolorowania skladnii */
			$geshi->set_language($snippets[1][$i]);
//			$geshi->set_line_ending('<br />');

			/* odczyt atrybutow dostarczonych	w znaczniku	*/
			$attribute_ary = explode('|', $snippets[2][$i]);

			/* przypisanie stylu obramowania */
			//$frame_style = $user->theme['cfg']['overall_frame'];
			//$box_style = '';

			/* odczyt	atrybutow */
			foreach ($attribute_ary as $attribute)
			{
				switch ($attribute)
				{
					case 'num':

					$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
					break;

					case 'noframe':

					//$frame_style = $user->theme['cfg']['overall_no_frame'];
					break;

					case 'nolink':

					/* nie chcemy	aby	slowa kluczowe w listingu byly podlinkowane	do manuala */
					$geshi->set_url_for_keyword_group(0, '');

					break;

					case 'box':

					//$box_style = $user->theme['cfg']['overall_box'];
					break;
				}
			}

			//$geshi->set_overall_style($frame_style . $box_style);

			//$geshi->set_line_style($user->theme['cfg']['line_normal'], $user->theme['cfg']['line_fancy'],	true);

			//$geshi->set_code_style($user->theme['cfg']['code_normal'], $user->theme['cfg']['code_fancy']);  //bez	tego styl linii	jest taki jak numeru
			//$geshi->set_link_styles(GESHI_LINK, $user->theme['cfg']['link_normal']);
			//$geshi->set_link_styles(GESHI_HOVER, $user->theme['cfg']['link_hover']);

			/* zamiana oryginalnego tekstu na	ten	pokolorowany */
			$content =	str_replace($snippets[0][$i], $geshi->parse_code(), $content);
		}
		unset($geshi);
	}
}

?>