<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Helper sluzacy do prezentacji trybu sortowania w tabelach
 */
class Sort
{
	const ASC = 'ASC';
	const DESC = 'DESC';

	/**
	 * Okrsla, czy wlaczona jest opcja prezentacji kolejnosci sortowanych wynikow
	 * Jezeli pole ma wartosc false, w znacznikach <th> tabeli nie beda prezentowane
	 * ikony sortowania wynikow
	 */
	private static $enable = true;
	/**
	 * Tablica okresla domyslne sposoby sortowania wynikow
	 */
	private static $default;

	/**
	 * Metoda szyfrowania typu XOR. Proste szyfrowanie ma ukryc nazwy pol
	 * wedlug ktorych odbedzie sie szyfrowanie. Metoda odszyfrowania jest prosta
	 * jezeli projekt jest typu Open Source. Jednak skrypt jest zabezpieczon przed
	 * atakami XSS
	 * @param string $value Nazwa pola SQL
	 */
	public static function encrypt($value)
	{
		$password = md5(phpversion());
		$password_length = strlen($password);

		for ($i = 0, $len = strlen($value); $i < $len; $i++)
		{
			$pos = $i % $password_length;
			$value[$i] = chr(ord($value[$i]) ^ ord($password[$pos]));
		}
		return $value;
	}

	/**
	 * Zwraca tablice parametrow URL (Query String)
	 * @return mixed
	 */
	private static function getQueryString()
	{
		static $query_arr = array();

		if (!$query_arr)
		{
			parse_str($_SERVER['QUERY_STRING'], $query_arr);
		}
		return $query_arr;
	}

	/**
	 * Ustawia nazwy pol wedlug ktorych ma odbyc sie prezentacja wynikow w tabeli
	 * @example setDefaultSort('user_id', Sort::ASC); 
	 */
	public static function setDefaultSort()
	{
		$args = func_get_args(); 
		foreach (array_chunk($args, 2) as $element)
		{ 
			self::$default[$element[0]] = $element[1];
		}
	}

	/**
	 * Dezaktywacja klasy. Przy naglowkach tabeli nie bedzie ikon sluzacych do sortowania danych
	 */
	public static function disable()
	{
		self::$enable = false;
	}

	/**
	 * Odczyt metody sortowania danych. Metoda odczytuje wartosc parametru sort z GET
	 * Jezeli metoda sortowania nie zostala okreslona w GET, przyjmowane sa domyslne wartosci
	 */
	public static function getSort()
	{
		if (empty(Core::getInstance()->input->get->sort))
		{
			return self::$default;
		}
		$order = Core::getInstance()->input->get('order', Sort::ASC); 
		$order = $order != 'ASC' && $order != 'DESC' ? self::ASC : $order;

		$decrypt = self::encrypt(base64_decode(Core::getInstance()->input->get('sort')));
		$drop_char_match = array('^', '$', ';', '#', '&', '(', ')', '`', '\'', '|', ',', '?', '%', '~', '[', ']', '{', '}', ':', '\\', '=', '\'', '!', '"', '-', '%20', "'");

		/**
		 * Filtracja danych majaca na celu zapobieganie atakom SQL Injection oraz XSS
		 */
		$filter = new Filter_Replace($drop_char_match);
		$decrypt = htmlspecialchars(strip_tags($filter->filter($decrypt)));
		
		return array($decrypt => $order);
	}

	/**
	 * Zwraca lancuch znakow SQL ktory moze zostac uzyty w zapytaniu SQL 
	 * wedlug ktorego powinno odbyc sie sortowanie, np. session_stop DESC, user_id ASC
	 * @return string
	 */
	public static function getSortAsSQL()
	{
		foreach ((array) self::getSort() as $sort => $order)
		{			
			$sql[] = '`' . $sort . '` ' . $order;
		}

		if (!isset($sql))
		{
			return false;
		}
		else
		{
			return implode(', ', $sql);
		}
	}

	/**
	 * Metoda wyswietla znacznik <th>
	 * @param string $col Nazwa kolumny z tabeli SQL, np. user_id
	 * @param string $text Etykieta kolumny
	 * @deprecated
	 */
	public static function displayTh($col, $text)
	{
		if (self::$enable)
		{
			if (in_array($col, array_keys(self::getSort())))
			{
				$sort = self::getSort();
				$order = $sort[$col];

				if ($order == Sort::DESC)
				{
					$class = 'asc';
				}
				else
				{
					$class = 'desc';
				}
				$order = $order == self::ASC ? self::DESC : self::ASC;
			}
			else
			{
				$order = self::DESC;
				$class = '';
			}
			$http_query_arr = self::getQueryString();
			$http_query_arr['sort'] = base64_encode(self::encrypt($col));
			$http_query_arr['order'] = $order;

			echo Html::tag('th', true, array('class' => "br $class"), Html::a('?' . http_build_query($http_query_arr, '', '&amp;'), $text));
		}
		else
		{
			echo Html::tag('th', true, array(), $text);
		}
	}

	/**
	 * Metoda wyswietla znacznik <a> sluzacy do sortowania wartosci
	 * @param string $col Nazwa kolumny z tabeli SQL, np. user_id
	 * @param string $text Etykieta kolumny
	 * @param mixed $attributes Dodatkowe atrybuty znacznika HTMl - <a>
	 */
	public static function display($col, $text, $attributes = array())
	{
		if (self::$enable)
		{
			if (in_array($col, array_keys(self::getSort())))
			{
				$sort = self::getSort();
				$order = $sort[$col];

				if ($order == Sort::DESC)
				{
					$class = 'asc';
				}
				else
				{
					$class = 'desc';
				}
				$order = $order == self::ASC ? self::DESC : self::ASC;
			}
			else
			{
				$order = self::DESC;
				$class = '';
			}
			$http_query_arr = self::getQueryString();
			$http_query_arr['sort'] = base64_encode(self::encrypt($col));
			$http_query_arr['order'] = $order;

			if (isset($attributes['class']))
			{
				$attributes['class'] .= " br $class";
			}
			else
			{
				$attributes['class'] = " br $class";
			}
			echo Html::a('?' . http_build_query($http_query_arr, '', '&amp;'), $text, $attributes);
		}
		else
		{
			echo $text;
		}

	}
}
?>