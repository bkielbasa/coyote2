<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Interfejs parsera. Kazda klasa parsera musi implementowac ten interfejs
 */
interface Parser_Interface
{
	/**
	 * Metoda parsowania. To w niej powinien znajdowac sie kod parsujacy tekst
	 * @param string &$content Tresc tekstu (referencja)
	 * @param mixed &$option referencja do obiektu konfiguracji (implementujacego interfejs IParser_Config)
	 */
	public function parse(&$content, Parser_Config_Interface &$config);
}

/**
 * Intefejs klasy konfiguracji parsera
 */
interface Parser_Config_Interface
{
	public function setOption($opt, $value);
	public function getOption($opt);
}

/**
 * Klasa konfiguracji parsera. Dzieki temu mozna przekazywac
 * do klasy opcje, ktore sa interpretowane dalej, w adapterach
 */
class Parser_Config implements Parser_Config_Interface
{
	private $config;

	/**
	 * Ustaw opcje parsowania
	 * @param string $opt Nazwa opcji
	 * @param string $value Wartosc
	 */
	public function setOption($opt, $value)
	{
		$this->config[$opt] = $value;
	}

	/**
	 * Zwraca opcje konfiguracyjna. Jezeli taka nie istnieje w tablicy, zwraca false
	 * @param string $opt Nazwa opcji
	 * @return mixed
	 */
	public function getOption($opt)
	{
		if (!isset($this->config[$opt]))
		{
			return false;
		}
		return $this->config[$opt];
	}
}

/**
 * Klasa sluzy do parsowania tekstu, usuwania niepotrzebnego kodu lub zamiany
 */
class Parser
{
	/**
	 * Stala okrsla tryb wyciecia tekstu (uzywany przez metode extact())
	 * Wyciecie obejmie znacznik otwierajacy, zamykajacy oraz zawartosc
	 */
	const CLOSED = 1;
	/**
	 * Stala okrsla tryb wyciecia tekstu (uzywany przez metode extact())
	 * Wyciecie obejmie tylko znacznik otwierajacy
	 */
	const SINGLE = 2;

	/**
	 * Tablica przechowujaca instancje klas parsera
	 */
	private $parser = array();
	/**
	 * Pole przechowuje instancje obiektu konfiguracji parsera
	 */
	private $config;
	/**
	 * Analizowana tresc wiadomosci, tekstu itp
	 */
	private $content;

	function __construct()
	{
		$this->config = new Parser_Config;
	}

	/**
	 * Metoda dodaje instancje klasy parsea. Oznacza to, ze w momencie parsowania tekstu
	 * zostanie wywolana metoda parse() z tego obiektu
	 */
	public function addParser(Parser_Interface $parser)
	{
		$this->parser[] = $parser;
	}

	/**
	 * Usuwa zadeklarowane parsery
	 */
	public function removeParsers()
	{
		$this->parser = array();
	}

	public function __isset($parser)
	{
		$result = false;

		foreach ($this->parser as $class)
		{
			if (strcasecmp(get_class($class), 'Parser_' . $parser) == 0)
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	/**
	 * Ustaw opcje parsowania
	 * @param string $opt Nazwa opcji
	 * @param string $value Wartosc
	 */
	public function setOption($opt, $value)
	{
		$this->config->setOption($opt, $value);
	}

	public function setContent(&$content)
	{
		$this->content = $content;
	}

	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Zwraca opcje konfiguracyjna. Jezeli taka nie istnieje w tablicy, zwraca false
	 * @param string $opt Nazwa opcji
	 * @return mixed
	 */
	public function getOption($opt)
	{
		return $this->config->getOption($opt);
	}

	/**
	 * Funkcja generuje losowy ciag znakow
	 * @return string
	 */
	private static function getRandStr()
	{
		return dechex(mt_rand(0, 0x7fffffff))	. dechex(mt_rand(0,	0x7fffffff));
	}

	/**
	 * Metoda usuwa z tekstu tekst pomiedzy znacznikami $tag_name (tymczasowo)
	 * @param string $tag_name Nazwa znacznika (plain, code	itp)
	 * @param int $mode Okresla, czy zostanie wyciety caly znacznik (otwierajacy i zamykajacy) wraz z trescia
	 * czy jedynie np. znacznik otwierajacy (self::SINGLE)
	 * @return mixed
	 */
	public function extract($tag_name, $mode = self::CLOSED)
	{
		return self::extractTags($this->content, $tag_name, $mode);
	}

	public static function extractTags(&$content, $element, $mode = self::CLOSED)
	{
		if (is_array($element))
		{
			$element = '(' . implode('|', $element) . ')';
		}

		if ($mode == self::CLOSED)
		{
			$regexp = "#(<($element)(.|\n)*?>).*?<\/($element)>#is";
		}
		else
		{
			$regexp = "#(<($element)(.|\n)*?>)#is";
		}

		$output = array();
		preg_match_all($regexp, $content, $matches);

		if ($matches)
		{
			for ($i = 0, $limit = sizeof($matches[0]); $i < $limit; $i++)
			{
				$match = &$matches[0][$i];

				$uniqId = self::getRandStr();
				$content = str_replace($match, $uniqId, $content);

				$output[$uniqId] = $match;
			}
		}

		/* zwrocenie informacji na temat aktualnego tekstu oraz wycietego	*/
		return array('message' => $content, 'stripped'	=> $output);
	}

	/**
	 * Funkcja dokonuje czynnosci odwrotnej od metody extract()
	 * @param mixed $data_arr Tablica zwrocona przez metode extract()
	 */
	public function retract(&$data_arr)
	{
		self::retractTags($this->content, $data_arr);
	}

	public static function retractTags(&$content, &$data)
	{
		/* po dokonaniu powyzszych czynnosci, wkompilowanie wczesniej wycietego tekstu pomiedzy tagiem <code> */
		$content = str_replace(array_keys($data['stripped']), array_values($data['stripped']), $content);
	}

	/**
	 * Metoda realizuje parsowanie tekstu korzystajac przy tym z klas, ktorych instancje
	 * zostaly zapisane w $parser
	 * @param string &$content Referencja do tekstu, wiadomosci, artykulu itp
	 * @return string
	 */
	public function parse(&$content)
	{
		$this->content =&$content;
		/* zamiana znakow tabulacji na 8 spacji */
        $content = str_replace(array("\r\n", "\t"), array("\n", "        "), $content);

		$plain_arr = $this->extract('plain');

		/* stare znaczniki okreslajace kolorowanie skladnii (kwestia kompatybilnosci) */
        $syntax_tags = 'php|delphi|cpp|asm';

        /* zastapienie starych znacznikow kolorowania skladni - nowymi */
        $content = preg_replace("#<({$syntax_tags}*)>(.*?)</({$syntax_tags}*)>#is", '<code=$1>$2</code>', $content);

		foreach ($this->parser as $parser)
		{
			$parser->parse($content, $this->config);
		}
		$this->retract($plain_arr);

		/* usuniecie kodu HTML znajdujacego sie pomiedzy znacznikami <plain> */
		$content = preg_replace('#<plain>(.*?)</plain>#ies', "preg_replace('#\n{2}#', '<br />', htmlspecialchars(str_replace('\\\"', '\"', '$1')))", $content);

		return $content;
	}
}
?>