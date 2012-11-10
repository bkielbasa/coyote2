<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Glowny adapter dla pliko PHP. 
 * Pobiera konfiguracje z tablicy w okreslonym pliku
 */
class Config_PHP implements IConfig
{
	public function load($path)
	{ 
		$config = array();

		try
		{ 
			if ((include_once($path)) === false)
			{
				throw new ConfigFileNotFoundException("Could not find {$path} file");
			}
		}
		catch (ConfigFileNotFoundException $e)
		{
			echo $e->getMessage() . "<br />\n";
			echo "Should be on: {$cfg} \n";
			exit;
		}
		
		return $config;
	}

	public function isAccept($suffix)
	{
		if ($suffix == 'php')
		{
			return true;
		}
	}
}

?>