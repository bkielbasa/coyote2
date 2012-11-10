<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Funkcja zwraca okreslony element z tablicy
 * Szczegolnie przyadtne w momencie, gdy $array jest zwracane przez funkcje
 * @example
 * <code>
 * element(Foo(), 1); // element o indeksie = 1, zwracany przez Foo()
 * </code>
 * @param mixed $array Tablica
 * @param int $index Numer indeksu
 * @return mixed
 */
function element($array, $index)
{
	return @$array[$index];
}

/**
 * Funkcja zmienia klucze w tablicy asocjacyjnej dodajac na poczatku lub na koncu prefix lub suffix
 * @param mixed $array Tablica
 * @param string $pad Prefiks albo sufiks (wartosc, ktora zostanie dodana na koncu lub na poczatku klucza)
 * @param int $pad_type STR_PAD_LEFT, STR_PAD_RIGHT lub STR_PAD_BOTH
 * @return mixed
 */
function array_key_pad($array, $pad = '', $pad_type = STR_PAD_LEFT)
{
	foreach ($array as $key => $value)
	{
		if ($pad_type == STR_PAD_LEFT) 
		{
			$array2[$pad . $key] = $value;
		}
		else if ($pad_type == STR_PAD_RIGHT)
		{
			$array2[$key . $pad] = $value;
		}
		else
		{
			$array2[$pad . $key . $pad] = $value;
		}
	}
	return $array2;
}

/**
 * Funkcja przygotowuje tablice do dodania do bazy danych, pobierajac wartosci z tablicy _POST
 * @example
 * <code>
 * $arr = array('name' => '', 'active => 0);
 * $arr = array_post($arr, 'user_');
 * debug($arr); 
 * // pobrane zostana wartosci name oraz active z HTTP POST. Dodatkowo w kluczach
 * // zostanie dodany prefik user_
 * </code>
 * @param mixed $array Tablica
 * @param string $pad Prefiks albo sufiks kluczy
 * @param int $pad_type @see array_key_pad()
 * @return mixed
 */
function array_post($array, $pad = '', $pad_type = STR_PAD_LEFT)
{
	$core = &Core::getInstance();
	foreach ($array as $key => $value)
	{
		$array[$key] = $core->input->post->value($key);
	}
	return array_key_pad($array, $pad, $pad_type);
}

/**
 * Funkcja przygotowuje tablice do dodania do bazy danych, pobierajac wartosci z tablicy _GET
 * @example
 * <code>
 * $arr = array('name' => '', 'active => 0);
 * $arr = array_post($arr, 'user_');
 * debug($arr); 
 * // pobrane zostana wartosci name oraz active z HTTP GET. Dodatkowo w kluczach
 * // zostanie dodany prefik user_
 * </code>
 * @param mixed $array Tablica
 * @param string $pad Prefiks albo sufiks kluczy
 * @param int $pad_type @see array_key_pad()
 * @return mixed
 */
function array_get($array, $pad = '', $pad_type = STR_PAD_LEFT)
{
	$core = &Core::getInstance();
	foreach ($array as $key => $value)
	{
		$array[$key] = $core->input->get->value($key);
	}
	return array_key_pad($array, $pad, $pad_type);
}


?>