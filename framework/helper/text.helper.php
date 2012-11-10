<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Text
{
	/**
	 * Umozliwia uciecie przekazanego lanacucha znakow
	 * @param string $text Lancuch znakow
	 * @param int $maxLength Maksymalna mozliwa dlugosc lancucha
	 * @param string $end Opcjonalny argument. Jego wartosc bedzie dodawana na koncu ucietego lancucha
	 * @param string $encoding Kodowanie lancucha (domyslnie UTF-8)
	 * @return string
	 */
	public static function limit($text, $maxLength, $end = '...', $encoding = 'UTF-8')
	{
		if (!$maxLength)
		{
			return $text;
		}
		/**
		 * Jezeli nie ma biblioteki mbstring, nalezy uzyc standardowych funkcji
		 */
		if (!function_exists('mb_strlen'))
		{
			function mb_substr($str, $start, $length, $encoding)
			{
				return substr($str, $start, $length);
			}
		}

		if (self::length($text, $encoding) > $maxLength - self::length($end))
		{
			$text = mb_substr($text, 0, $maxLength - self::length($end), $encoding) . $end;
		}
		return $text;
	}

	/**
	 * Umozliwia uciecie przekazanego lanacucha znakow.
	 * Metoda "zaklada", ze wartosc lancucha $text to kod (x)HTML.
	 * Najpierw wykonywana jest metoda htmlspecialchars_decode() aby zamienic
	 * &lt; na < tak, aby dlugosc lancucha byla liczona prawidlowo
	 *
	 * @param string $text Lancuch znakow
	 * @param int $maxLength Maksymalna mozliwa dlugosc lancucha
	 * @param string $end Opcjonalny argument. Jego wartosc bedzie dodawana na koncu ucietego lancucha
	 * @param string $encoding Kodowanie lancucha (domyslnie UTF-8)
	 * @return string
	 */
	public static function limitHtml($text, $maxLength, $end = '...', $encoding = 'UTF-8')
	{
		$text = htmlspecialchars_decode($text);
		$text = self::limit($text, $maxLength, $end, $encoding);

		return htmlspecialchars($text);
	}

	/**
	 * Zwraca dlugosc lancucha
	 * @param string $text
	 * @param string $encoding kodowanie tekstu
	 * @return int
	 */
	public static function length($text, $encoding = 'UTF-8')
	{
		if (!function_exists('mb_strlen'))
		{
			function mb_strlen($str, $encoding)
			{
				return strlen($str);
			}
		}

		return mb_strlen($text, $encoding);
	}

	/**
	 * Metoda "oczyszcza" tekst ze znacznikow HTML, znakowej nowej linii czy zbednych spacji
	 * @param string $text
	 * @return text
	 */
	public static function plain($text, $stripTags = true)
	{
		if ($stripTags)
		{
			$text = strip_tags($text);
		}
		$text = str_replace(array("\n", "\t", "\r"), ' ', $text);
		$text = trim(preg_replace('/ {2,}/', ' ', htmlspecialchars($text, ENT_QUOTES, 'UTF-8')));

		return $text;
	}

	/**
	 * Funckja umozliwia wygenerowanie losowego ciagu znakow
	 * @param int $length Dlugosc wygenerowanego lancucha
	 * @return string Losowo wygenerowany lancuch
	 */
	public static function random($length = 10)
	{
		$rand_str = '';

		for ($i = 0; $i < $length; $i++)
		{
			/* dolaczenie losowego znaku */
			$rand_str .= chr(rand(65, 90));
		}

		return $rand_str;
	}

	/**
	 * Zamienia wszystkie spacje w tekscie na  _
	 * @param string $text
	 * @return string
	 */
	public static function underscore($text)
	{
		return str_replace(' ', '_', $text);
	}

	/**
	 * Zamienia wszystkie znaki _ w tekscie na spacje
	 * @param string $text
	 * @return string
	 */
	public static function humanize($text)
	{
		return str_replace('_', ' ', $text);
	}

	/**
	 * Obejmuje tekst w cudzyslowie
	 * @param string|array $text Tablica lub lancuch
	 * @return string|array
	 */
	public static function quote($text)
	{
		if (is_array($text))
		{
			return array_map(array('Text', 'quote'), $text);
		}
		else
		{
			return '"' . $text . '"';
		}
	}

	/**
	 * Zamienia znaki w lancuchu na male
	 * W tym celu uzywana jest metoda mb_strtolower() wraz kodowaniem utf
	 * @param string $text Tekst
	 */
	public static function toLower($text)
	{
		return mb_strtolower($text, 'UTF-8');
	}

	/**
	 * Zamienia znaki w lancuchu na duze
	 * W tym celu uzywana jest metoda mb_strtolower() wraz kodowaniem utf
	 * @param string $text Tekst
	 */
	public static function toUpper($text)
	{
		return mb_strtoupper($text, 'UTF-8');
	}

	/**
	 * Metoda wyswietla jeden z argumentow podanych w metodzie (naprzemian)
	 * @example echo Text::alternate('Adam', 'Boduch'); // Adam
	 * @example echo Text::alternate('Adam', 'Boduch'); // Boduch
	 * @return string
	 */
	public static function alternate()
	{
		static $i;

		if (func_num_args() === 0)
		{
			return '';
		}
		$args = func_get_args();
		return $args[$i++ % count($args)];
	}

	/**
	 * Formatuje rozmiar pliku/danych w bajtach na postac przyjazna (np. 1.76 MB, 1 KB itp)
	 * @param int $fileSize Rozmiar danych w bajtach
	 * @return string
	 */
	public static function formatSize($size)
	{
		// mniejsze niz kB...
		if ($size < 1024)
		{
			$result = $size . ' b';
		}
		// mniejsze niz 1 MB
		elseif ($size < 1048576)
		{
			$result = round($size / 1024, 2) . ' KB';
		}
		// mniejsze niz 1 GB
		elseif ($size < 1073741824)
		{
			$result = round($size / 1048576, 2) . ' MB';
		}

		return $result;
	}

	/**
	 * Formatuje wartosc zmiennoprzecinkowa do tekstu okreslajacego
	 * liczbe sekund lub milisekund.
	 *
	 * @example
	 * <code>
	 * echo Text::formatBenchmark(1.0991); // 1.10 s
	 * echo Text::formatBenchmark(0.01); // 10 ms
	 * </code>
	 * @param float		$benchmark
	 * @return string
	 */
	public static function formatBenchmark($benchmark)
	{
		$benchmark = str_replace(',', '.', sprintf('%.3f', $benchmark));

		if ($benchmark < 1)
		{
			list(, $ms) = explode('.', $benchmark);
			return (int) substr($ms, 0, 3) . ' ms';
		}
		else
		{
			return number_format($benchmark, 2) . ' s';
		}
	}

	/**
	 * @deprecated
	 */
	public static function fileSize($size)
	{
		return self::formatSize($size);
	}

	/**
	 * Metoda usuwa z tekstu polskie znaki i inne niepotrzebne znaki, tak, aby
	 * tekst mogl byc wykorzystany z przyjaznym linku
	 * @param string $text Wartosc tekstowa, ktora zostanie poddana konwersji
	 * @param string $spaceChar Symbol, ktory zastapi znak spacji
	 * @return string
	 */
	public static function path($text, $spaceChar = '-')
	{
		$diacritics = array(
				"\xc4\x85" => "a", "\xc4\x84" => "A", "\xc4\x87" => "c", "\xc4\x86" => "C",
				"\xc4\x99" => "e", "\xc4\x98" => "E", "\xc5\x82" => "l", "\xc5\x81" => "L",
				"\xc3\xb3" => "o", "\xc3\x93" => "O", "\xc5\x9b" => "s", "\xc5\x9a" => "S",
				"\xc5\xbc" => "z", "\xc5\xbb" => "Z", "\xc5\xba" => "z", "\xc5\xb9" => "Z",
				"\xc5\x84" => "n", "\xc5\x83" => "N"
		);
		$text = strtolower(strtr($text, $diacritics));
		$text = str_replace(' ', $spaceChar, $text);

		$text = preg_replace('/[^0-9a-z\\' . $spaceChar . ']+/', '', $text);
		$text = trim(preg_replace('/[\\' . $spaceChar . ']+/', '-', $text), $spaceChar);

		return $text;
	}

	/**
	 * @deprecated
	 * @param string $text
	 * @param string $spaceChar
	 */
	public static function seo($text, $spaceChar = '-')
	{
		return self::path($text, $spaceChar);
	}

	/**
	 * Metoda "skraca" adresy URL doprowadzajac je do ksztaltu: www.dlugi-adres[...]dalsza-czesc.com
	 * @param string $url Tekst, adres URL
	 * @param int $limit Limit dozwolonej dlugosci znakow
	 * @return string
	 */
	public static function limitUrl($url, $limit = false, $encoding = 'UTF-8')
	{
		if (!$limit)
		{
			return $url;
		}

		/**
		 * Jezeli nie ma biblioteki mbstring, nalezy uzyc standardowych funkcji
		 */
		if (!function_exists('mb_substr'))
		{
			function mb_substr($str, $start, $length, $encoding)
			{
				return substr($str, $start, $length);
			}
		}

		if (($length = self::length($url)) > $limit)
		{
			$count = ($limit / 2) - 5;
			$url = mb_substr($url, 0, $count, $encoding) . '[...]' . mb_substr($url, $length - $count, $count, $encoding);
		}

		return $url;
	}

	/**
	 * Metoda zwrotna dla metody transformUrl()
	 */
	private static function makeClickable($url, $limit)
	{
		$append = '';
		$split = false;

		$url = stripslashes($url);

		foreach (array('<', '>', '&lt;', '&gt;') as $char)
		{
			$next = strpos($url, $char);

			if ($next !== false)
			{
				$split = ($split !== false) ? min($split, $next) : $next;
			}
		}

		if ($split !== false)
		{
			$append = substr($url, $split);
			$url = substr($url, 0, $split);
		}

		$lastChar = substr($url, strlen($url) -1);

		switch ($lastChar)
		{
			case '?':
			case '.':
			case ',':
			case '!':
			case ':':
			case '"':
			case "'":

				$url = substr($url, 0, -1);
				$append = $lastChar . $append;
				break;

			default:

				$lastChar = '';
		}

		if (substr($url, strlen($url) -1) == ')')
		{
			if (substr_count($url, '(') < substr_count($url, ')'))
			{
				$url = substr($url, 0, -1);
				$append = ')' . $append;
			}
		}

		$absoluteUrl = $url;
		if (!preg_match('#^[\w]+?://.*?#i', $absoluteUrl))
		{
			$absoluteUrl = 'http://' . $absoluteUrl;
		}

		return '<a href="' . htmlspecialchars($absoluteUrl, ENT_QUOTES, 'UTF-8', false) . '">' . self::limitUrl($url, $limit) . '</a>' . $append;
	}

	/**
	 * Przeksztalca odnisniki w tekscie w dzialajace tagi (x)HTML
	 * @param string $text Tekst do transformacji
	 * @param int $limit Limit dlugosci URLa (dluzsze URL'e beda "przyciete" [nie w atrybucie href!])
	 * @return string
	 */
	public static function transformUrl($text, $limit = false)
	{
		$limit = (int) $limit;

		//linki z	protokolem [np.	http://cos.pl, ftp://cos.pl]
//		$patterns[] = "#(^|[\n\t (>.])([a-z][a-z\d+]*:/{2}(?:(?:[a-z0-9ąęśćźżółĄĘŚĆŻŹÓŁ\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\])(?::\d*)?(?:/(?:[a-z0-9ąęśćźżółĄĘŚĆŻŹÓŁ\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9ąęśćźżółĄĘŚĆŻŹÓŁ\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9ąęśćźżółĄĘŚĆŻŹÓŁ\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?)#ie";
//		$patterns[] = "#(^|[\n\t (>.])([a-z][a-z\d+]*:/{2}(?:(?:[a-z0-9\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\])(?::\d*)?(?:/(?:[a-z0-9\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?)#iue";
		$patterns[] = '~(?<![\p{L}\p{N}_])(?<!href="|src="|">)(?:ht|f)tps?://\S+(?:/|(?![\p{L}\p{N}_]))~iue';
		$replacements[] = "self::makeClickable('$0', $limit)";

		//linki bez protokolu, z 'www' na poczatku
//		$patterns[] = "#(^|[\n\t (>])(www\.(?:[\S+\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})+(?::\d*)?(?:/(?:[a-z0-9\-._~!$&'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'(*+,;=:@/?|]+|%[\dA-F]{2})*)?)#ie";
		$patterns[] = '~(?<![\p{L}\p{N}_])(?<!://|">|=|[\w+][?\/:])www(?:\.\S+)+\.[a-z]{2,6}(?:\S+)?(?:/|(?![\p{L}\p{N}_]))~iue';
		$replacements[] = "self::makeClickable('$0', $limit)";

		return preg_replace($patterns,	$replacements, $text);
	}

	/**
	 * Przeksztalca adresy e-mail w dzialajace odnosniki
	 * @param string $text
	 * @return string
	 */
	public static function transformEmail($text)
	{
		return preg_replace('#(^|[\n \[\]\:<>&;]|\()([a-z0-9&\-_.]+?@[\w\-]+\.(?:[\w\-\.]+\.)?[\w]+)#i', "\$1<a href=\"mailto:\$2\">$2</a>", $text);
	}

	/**
	 * Meta wykonuje kod PHP znajdujacy sie w tekscie pomiedzy znacznikami <?php a ?>
	 * @param string $text
	 * @return text
	 */
	public static function evalCode($text)
	{
		return preg_replace_callback('#<\?php(.*)\?>#is', array('Text', 'evalMatches'), $text);
	}

	private static function evalMatches($matches)
	{
		ob_start();
		eval($matches[1]);

		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}
}
?>