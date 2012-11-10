<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Ta mala wtyczka "podczepia" sie pod trigger system.onDisplay
 * aby zaznaczyc w tekscie slowa szukane przez uzytkownika.
 * Jezeli zadanie pochodzi z Google, na podstawie URL sprawdza szukane
 * slowa kluczowe i zamienia je na znacznika <span class="search-term">...</span>
 */
class Highlight extends Plugin
{
	const NAME = 'highlight';

	/**
	 * Wtyczka nie jest nigdzie wyswietlana - metoda pusta
	 */
	public function display() {}

	/**
	 * Statyczna metoda umozliwia szukanie okreslonych fraz w tekscie
	 * @param array $data	Tablica zawierajaca dwa elementy (0 = tresc szablonu, 1 - nazwe szablonu)
	 * @static
	 */
	public static function render($data)
	{
		$core = &Core::getInstance();

		$content = &$data[0];
		$referer = $core->input->getReferer();
		
		$pattern = "/^.*q=([^&]+)&?.*\$/iu";
		$keywords = array();

		if (preg_match($pattern, $referer) && !preg_match($pattern, $core->input->getCurrentUrl()))
		{
			$keywords = str_replace(array("'", '"'), '', urldecode(preg_replace($pattern, '$1', $referer)));
		}
		elseif (isset($core->input->get->hl))
		{
			$keywords = str_replace(array("'", '"'), '', $core->input->get->hl);
		}

		if ($keywords)
		{
			/**
			 * Bardzo kiepskie rozwiazanie :/
			 * Poniewaz nie chcemy, aby zadne zmiany w <head> nie byly modyfikowane, tymczasowo
			 * usuwamy jej zawartosc
			 *
			 * @todo Zastapic wyrazenia regularne operacjami na DOM
			 */
			$content = preg_replace('#<head>(.*?)</head>#ise', "'<head>' . base64_encode('$1') . '</head>'", $content);
			$content = preg_replace('#<style type="text/css">(.*?)</style>#ise', "'<style type=\"text/css\">' . base64_encode('$1') . '</style>'", $content);

			foreach (preg_split("/[\s,\+\.]+/", $keywords) as $keyword)
			{
				if (preg_match('#\S+#u', $keyword))
				{
					$keyword = '\b' . preg_quote(str_replace(array('(', ')'), '', $keyword)) . '\b';
					$content = preg_replace(sprintf('~(?!<.*?)(%s)(?![^<>]*?>)~i', $keyword), '<em class="search-term">$1</em>', $content);
				}
			}

			$content = preg_replace('#<head>(.*?)</head>#ise', "'<head>' . stripslashes(base64_decode('$1')) . '</head>'", $content);
			$content = preg_replace('#<style type="text/css">(.*?)</style>#ise', "'<style type=\"text/css\">' . base64_decode('$1') . '</style>'", $content);
		}
	}
}
?>