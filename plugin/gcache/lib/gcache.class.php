<?php	
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class GCache
{
	const UNIQUE_ID		= 		'__cache__';
	
	private static function isValidPage()
	{
		$core = &Core::getInstance();		
		return (!$core->input->isPost() && !sizeof($_GET)) && @$core->page instanceof Page;
	}
	
	public static function load()
	{
		if (self::isValidPage())
		{
			$core = &Core::getInstance();
			if (!$core->module->isPluginEnabled('gcache'))
			{
				return false;
			}
			
			$module = $core->module->getCurrentModule();
			$mode = $core->module->$module('mode', $core->page->getId());
			if (!$mode)
			{
				return false;
			}
			elseif ($mode == 1 && User::$id > User::ANONYMOUS)
			{
				return false;
			}
			
			if ($core->cache->exists('page_' . $core->page->getId()))
			{				
				$content = $core->cache->load('page_' . $core->page->getId());
				
				if ($core->page->getEditTime() <= $content[1])
				{				
					Trigger::call('system.onDisplay', array(&$content[0], self::UNIQUE_ID));
					
					/*
					 * Strona jest odczytywana z cache. Tzn. ze ewentualna liczba oznaczajaca
					 * czas generowania strony - nie zmienia sie. Ponizsze wyrazenie regularne
					 * zamienia "stara" wartosc generowania strony, na nowa - na podstawie
					 * czasu generowania strony z cache
					 */
					$content[0] = preg_replace('#<!--benchmark-->(.*)<!--\/benchmark-->#', Benchmark::elapsed(), $content[0]);
					
					echo $content[0];
					Log::add('Strona została odczytana z cache', E_DEBUG);
	
					Trigger::call('system.onShutdown');
					exit;
				}
			}
		}
		
	}
	
	public static function save($args)
	{
		if (self::isValidPage())
		{
			$tplName = $args[1];
			
			if ($tplName != 'debug' && $tplName != self::UNIQUE_ID)
			{
				$core = &Core::getInstance();
				
				if (!$core->module->isPluginEnabled('gcache'))
				{
					return false;
				}
				$module = $core->module->getCurrentModule();
				$mode = $core->module->getConfig($module, 'mode', $core->page->getId());

				if (!$mode)
				{
					return false;
				}
				elseif ($mode == 1 && User::$id > User::ANONYMOUS)
				{
					return false;
				}

				$lifetime = $core->module->$module('lifetime', $core->page->getId());				
				$core->cache->save('page_' . $core->page->getId(), array($args[0], $core->page->getEditTime()), $lifetime);
			}		
		}
	}
}
?>