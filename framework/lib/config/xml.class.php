<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Adapter XML. Pobiera konfiguracje ze wskazanego pliku XML
 */
class Config_XML implements IConfig
{
	/**
	 * Metoda konwertuje XML (SimpleXml) do tablicy PHP
	 * @param object $xml
	 * @return mixed
	 */
	private function toArray($xml)
	{	
		$config = array();

		if (count($xml->attributes()))
		{
			foreach ($xml->attributes() as $key => $value)
			{
				$value = (string)$value;

				if (array_key_exists($key, $config))
				{
					if (!is_array($config[$key]))
					{
						$config[$key] = array($config[$key]);
					}
					$config[$key][] = $value;
				}
				else
				{
					$config[$key] = $value;
				}
			}
		}

		if (count($xml->children()))
		{
			foreach ($xml->children() as $key => $value)
			{
				if (count($value->children()))
				{
					$value = $this->toArray($value);
				}
				else if (count($value->attributes()))
				{
					$value = $this->toArray($value);
				}
				else 
				{
					$value = (string)$value;
				}

				if (array_key_exists($key, $config))
				{
					if (!is_array($config[$key]) || !array_key_exists(0, $config[$key]))
					{
						$config[$key] = array($config[$key]);
					}
					$config[$key][] = $value;
				}
				else
				{
					$config[$key] = $value;
				}
			}
		}
		else
		{
			$config = (array)$xml;
		}
		return $config;
	}

	/**
	 * Metoda realizuje pobranie konfiguracji projektu z pliku XML
	 * @param string $cfg Nazwa pliku konfiguracyjnego (bez sciezki)
	 */
	function load($cfg)
	{
		try
		{
			if (!file_exists($cfg))
			{
				throw new ConfigFileNotFoundException("Could not find {$cfg} file");
			}
			$config = $this->toArray(simplexml_load_file($cfg));			
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
		if ($suffix == 'xml')
		{
			return true;
		}
	}
}

?>