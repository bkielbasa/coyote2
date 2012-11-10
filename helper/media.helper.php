<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Media_Base 
{
	public static function locate($filename, $datadir = '')
	{
		$result = '';
		$subdir = '';

		if (strpos($filename, '/') !== false)
		{
			list($subdir, $filename) = explode('/', $filename);
			$subdir .= '/';
		}

		if ($result = Load::locate(Config::getItem('core.template') . "/{$subdir}{$datadir}{$filename}"))
		{	
			$result = Url::site() . str_replace(getcwd() . '/', '', $result[0]);
		}

		return $result;
	}
}

final class Media extends Media_Base
{
	public static function img($filename)
	{
		return parent::locate($filename, 'img/');		
	}

	public static function css($filename)
	{
		return parent::locate($filename);
	}

	
}
?>