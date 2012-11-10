<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa konfiguracji parsujaca pliki INI
 */
class Config_INI implements IConfig
{
	private function parseKey($config, $key, $value)
	{
		if (strpos($key, '.') !== false)
		{
			$part = explode('.', $key, 2);

			if (!isset($config[$part[0]]))
			{
				$config[$part[0]] = array();
			}
			if (isset($part[1]))
			{
				$config[$part[0]] = $this->parseKey($config[$part[0]], $part[1], $value);
			}
		}
		else
		{
			$config[$key] = $value;
		}
		return $config;
	}

	public function load($path)
	{
		try
		{
			if (!$config = parse_ini_file($path, true))
			{
				throw new ConfigFileNotFoundException("Could not find {$path} file");
			}
		}
		catch (ConfigFileNotFoundException $e)
		{
			echo $e->getMessage() . "<br />\n";
			echo "Should be on: {$path} \n";
			exit;
		}
		$result = array();
		foreach ($config as $section => $data)
		{ 
			if (!is_array($data))
			{
				$result = array_merge_recursive($result, $this->parseKey(array(), $section, $data));
			}
			else
			{
				$result[$section] = array();
				foreach ($data as $key => $value)
				{
					$result[$section] = array_merge_recursive($result[$section], $this->parseKey(array(), $key, $value));
				}
			}
		}

		return $result;
	}

	public function isAccept($suffix)
	{
		if ($suffix == 'ini')
		{
			return true;
		}
	}
}

?>