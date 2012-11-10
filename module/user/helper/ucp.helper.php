<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Ucp
{
	/**
	 * Metoda statyczna zwraca menu panelu uzytkownika. W parametrze nalezy podac nazwe klasy CSS
	 * elementu, ktory ma byc aktywny
	 *
	 * @static
	 * @param $selected Klasa CSS oznaczajaca, ktory element ma byc zaznaczony
	 * @return string   Kod HTML
	 */
	public static function loadMenu($selected)
	{
		$html = '';

		$core = &Core::getInstance();
		foreach ($core->module->getModules() as $row)
		{
			$xml = simplexml_load_file('module/' . $row['module_name']  . '/' . $row['module_name'] . '.xml');

			if (isset($xml->ucp->menu))
			{
				$data = $xml->ucp->menu;

				foreach ($data as $index => $menu)
				{
					$class = (string) $menu->class;

					if ($class == $selected)
					{
						$class .= ' focus';
					}
					$html .= Html::tag('li', true, array(), Html::a(url((string) $menu->url), Text::evalCode($menu->name), array('title' => $menu->title, 'class' => $class)));
				}
			}
		}

		/**
		 * @todo Duplikacja kodu! Do przerobienia
		 */

		foreach ($core->module->getPlugins($core->module->getCurrentModule()) as $row)
		{
			$xml = simplexml_load_file('plugin/' . $row['plugin_name']  . '/' . $row['plugin_name'] . '.xml');

			if (isset($xml->ucp->menu))
			{
				$data = $xml->ucp->menu;

				foreach ($data as $index => $menu)
				{
					$class = (string) $menu->class;

					if ($class == $selected)
					{
						$class .= ' focus';
					}
					$html .= Html::tag('li', true, array(), Html::a(url((string) $menu->url), Text::evalCode($menu->name), array('title' => $menu->title, 'class' => $class)));
				}
			}
		}

		return $html;
	}
}
?>