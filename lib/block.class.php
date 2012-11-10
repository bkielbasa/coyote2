<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa sluzaca do wyswietlania zawartosci blokow
 */
class Block
{
	/**
	 * Stala okreslajaca, iz region nie bedzie cachowany w ogole
	 */
	const CACHE_NONE = 0;
	/**
	 * Stala okreslajaca, ze region bedzie cachowany tylko dla osob niezalogowanych
	 */
	const CACHE_ANONYMOUS = 1;
	/**
	 * Stala okreslajaca, iz region bedzie cachowany dla kazdego
	 */
	const CACHE_ALL = 2;

	const HIDE = 0;
	const SHOW = 1;

	private static $block;
	private static $region;

	public static function getBlocks()
	{
		if (!self::$block)
		{
			$core = &Core::getInstance();
			$block = &$core->load->model('block');

			if (isset($core->cache->blocks))
			{
				foreach ($core->cache->blocks as $id => $row)
				{
					if ($row['cache_flag'] != self::CACHE_ALL)
					{
						if ($row['cache_flag'] == self::CACHE_ANONYMOUS && User::$id > User::ANONYMOUS)
						{
							continue;
						}
					}

					if ($row['block_pages'])
					{
						if (!self::getScope($row['block_scope'], $row['block_pages']))
						{
							continue;
						}
					}

					self::$block[$row['block_name']] = $row;
					if ($row['block_region'])
					{
						self::$region[$row['block_region']][] = &self::$block[$row['block_name']];
					}
				}
			}

			$query = $block->getBlocks();

			while ($row = $query->fetchAssoc())
			{
				if (isset(self::$block[$row['block_name']]))
				{
					continue;
				}

				if ($row['block_auth'])
				{
					if (!Auth::get($row['block_auth']))
					{
						continue;
					}
				}
				if ($row['block_pages'])
				{
					if (!self::getScope($row['block_scope'], $row['block_pages']))
					{
						continue;
					}
				}

				self::$block[$row['block_name']] = $row;

				if ($row['block_region'])
				{
					self::$region[$row['block_region']][] = &self::$block[$row['block_name']];
				}
			}

			/**
			 * Jezeli warunek zwroci FALSE, oznacza, ze nie ma zadnych blokow, albo sa
			 * lecz zadne grupy nie sa do niego przydzielone. To oznacza, ze nie wyswietlamy
			 * zadnych blokow. Dlatego przydzielamy do pola $block wartosc TRUE, aby uniknac
			 * wykonywania tego samego kodu przy kolejnej probie odczytania blokow z regionu
			 */
			if (!self::$block)
			{
				self::$block = true;
			}
		}

		return self::$block;
	}

	public static function getRegionBlocks($region)
	{
		if (!self::$region)
		{
			self::getBlocks();
		}
		if (!isset(self::$region[$region]))
		{
			return false;
		}
		return self::$region[$region];
	}

	public static function getBlock($name)
	{
		$blocks = self::getBlocks();

		if (!isset($blocks[$name]))
		{
			return false;
		}
		return $blocks[$name];
	}

	private static function addStylesheet($fileName)
	{
		echo Html::tag('style', true, array('type' => 'text/css') , '@import url(' . Url::__('store/css/' . $fileName) . '.css);');
	}

	public static function display($name)
	{
		if (!$block = self::getBlock($name))
		{
			return false;
		}
		extract($block, EXTR_REFS);
		if ($block_plugin)
		{
			if (!Core::getInstance()->module->isPluginEnabled($plugin_name))
			{
				return false;
			}
		}

		if (!empty($cache))
		{
			$content = &$cache;
		}
		else
		{
			ob_start();

			if ($block_style)
			{
				echo self::addStylesheet($block_style);
			}

			if (strpos($block_header, '<?php') !== false)
			{
				$block_header = Text::evalCode($block_header);
			}
			echo $block_header;

			if ($block_plugin)
			{
				$class = &Load::loadClass($plugin_name);
				if ($item_data)
				{
					$class->setItem($item_data);
				}
				echo $class->display();
			}

			if (strpos($block_footer, '<?php') !== false)
			{
				$block_footer = Text::evalCode($block_footer);
			}
			echo $block_footer;

			$content = ob_get_contents();
			ob_end_clean();

			if ($block_trigger)
			{
				UserErrorException::__(Trigger::call($trigger_name, $block_region, $block_name, array(&$content)));
			}

			if ($block_cache == self::CACHE_ALL || ($block_cache == self::CACHE_ANONYMOUS && User::$id == User::ANONYMOUS))
			{
				$block['cache'] = $content;
				$block['cache_flag'] = $block_cache;

				$blocks = Core::getInstance()->cache->get('blocks');
				$blocks[$block_id] = $block;

				Core::getInstance()->cache->put('blocks', $blocks);
			}
		}

		UserErrorException::__(Trigger::call('application.onBlockDisplay', $block_region, $block_name, array(&$content)));
		echo $content;
	}

	/**
	 * Metoda zwraca wartosc FALSE jezeli blok powinien zostac pominiety (NIE wyswietlony na tej stronie)
	 */
	private static function getScope($scope, &$pages)
	{
		$result = true;
		$core = &Core::getInstance();

		foreach (explode("\n", $pages) as $page)
		{
			$page = trim($page, '/');
			if (!strlen($page))
			{
				continue;
			}

			if ($page[0] == '@' && isset($core->router) && $core->router->getName())
			{
				$exp = (bool) ($core->router->getName() == substr($page, 1));
			}
			else
			{
				$exp = (bool) preg_match(self::getRegexp($page), '/' . Core::getInstance()->input->getPath());
			}


			$result = ($scope == self::SHOW ? !$exp : $exp);
			if ($result == ($scope == self::SHOW ? false : true))
			{
				break;
			}
		}
		return $result;
	}

	private function getRegexp($page)
	{
		$elements = array();

		if ($page == '' || $page == '/')
		{
			$elements[] = '[\/]+';
		}
		elseif ($page == '*')
		{
			$elements[] = '(.*)';
		}
		else
		{
			foreach (explode('/', $page) as $element)
			{
				$element = trim($element);

				if ($element == '*')
				{
					$elements[] = '(?:\/(.*))?';
				}
				else
				{
					$elements[] = '/' . $element;
				}
			}
		}

		return '#^' . implode('', $elements) . '$#i';
	}
}
?>