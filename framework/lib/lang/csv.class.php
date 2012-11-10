<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Adapter poswiecony formatowi CSV
 */
class Lang_CSV extends Lang_Abstract
{
	public function load($lang_file, $locale = null, array $option = array())
	{
		$option = array_merge($this->option, $option);
		if (!isset($option['separator']))
		{
			$option['separator'] = ';';
		}

		if ($locale == null)
		{
			$locale = $this->default;
		}
		if (!$fp = @fopen("i18n/$locale/$lang_file.lang.csv", 'r'))
		{
			throw new Exception("Unable to find language file: $lang_file.lang.csv");
		}

		while ($line = fgets($fp))
		{ 
			if (substr($line, 0, 1) != '#')
			{ 
				list($k, $v) = explode($option['separator'], $line);
				$this->data[$locale][$k] = $v;
			}
		} 
		fclose($fp);
	}

	public function add($lang_file, $key, $message, $locale, $option = array())
	{
		$option = array_merge($this->option, $option);
		if (!isset($option['separator']))
		{
			$option['separator'] = ';';
		}

		$fp = @fopen("i18n/$locale/$lang_file.lang.csv", 'a');
		@flock($fp, LOCK_EX);
		@fputs($fp, sprintf("%s%s%s\n", $key, $option['separator'], $message));
		@flock($fp, LOCK_UN);
		@fclose($fp);
	}
}
?>