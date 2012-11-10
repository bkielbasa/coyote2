<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Filtr usuwa z tekstu odnosniki URL. Dzialanie jest bardzo proste, usuwa z tekstu
 * wystapienia slowa http:// oraz www. Ma to zapobiegac wykorzystywaniu elementow systemu
 * do pozycjonowania
 */
class Filter_StripUrl implements IFilter
{
	private function filterData($value)
	{
		return str_replace(array('http://', 'www.'), '', $value);
	}

	public function filter($value)
	{
		// jezeli obecna jest kolumna "post", oznaca, ze zainstalowany jest modul forum.
		// sprawdzamy, czy user napisal min 20 postow. Jezeli nie - nie mozemy pozwolic mu na wstawienie URL-a
		if (User::data('post') !== false)
		{
			if (User::data('post') < 20)
			{
				$value = $this->filterData($value);
			}
		}
		else
		{
			if (time() - User::data('regdate') < Time::MONTH * 2)
			{
				$value = $this->filterData($value);
			}
		}

		return $value;
	}
}
?>