<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Abstrakcyjne klasa dla adapterow
 */
abstract class Lang_Abstract 
{
	protected $data = array();
	protected $option = array();
	protected $default;

	function __construct()
	{
		$this->default = Config::getItem('locale');

		if (!$this->default)
		{
			$dir = @opendir('i18n');
			while ($entry = @readdir($dir))
			{
				if ($entry{0} != '.')
				{
					$this->default = $entry;
					break;
				}
			}
			@closedir($dir);

			if (!$this->default)
			{
				throw new Exception('Unable to find default i18n directory');
			}
		}
	}

	public function option($option)
	{
		$this->option = array_merge($this->option, (array)$option);
	}

	public function setDefault($locale)
	{
		$this->default = $locale;
	}

	public function translate($message, $locale = null)
	{
		if ($locale == null)
		{
			$locale = $this->default;
		} 
		if (!isset($this->data[$locale][$message]))
		{ 
			if (isset($this->option['add']))
			{
				$this->add($this->option['add'], $message, $locale);
			}
			return $message;
		}
		else
		{ 
			return $this->data[$locale][$message];
		}
	}

	public function _($message, $locale = null)
	{
		return $this->translate($message, $locale);
	}

	abstract public function load($lang_file, $locale = null, array $option = array());
}

/**
 * I18n
 * @experimental
 */
class Lang
{
	/**
	 * Nazwa adaptera 
	 */
	const Adapter = 'array';
	/**
	 * Instancja klasy adaptera
	 */
	private $adapter;

	/**
	 * Konstruktor 
	 * @param string $adapter Nazwa adaptera obslugujacego i18n
	 * @param string $lang_file Nazwa pliku z tlumaczeniem
	 * @param string $locale OPCJONALNIE locale (np. pl-PL), inaczej nazwa katalogu z tlumaczeniami
	 */
	function __construct($adapter = '', $lang_file = '', $locale = null)
	{
		if (!$adapter)
		{
			$adapter = self::Adapter;
		}
		$this->adapter($adapter, $lang_file, $locale);
	}
	
	/** 
	 * Ustawianie nowego adaptera
	 * @param string $adapter Nazwa adaptera
	 * @param string $lang_file Nazwa pliku z tlumaczeniem
	 * @param string $locale OPCJONALNIE locale (np. pl-PL), inaczej nazwa katalogu z tlumaczeniami
	 */
	public function adapter($adapter, $lang_file = '', $locale = null)
	{
		$class = 'Lang_' . $adapter;

		if ((Load::loadFile("lib/lang/{$adapter}.class.php")) === false)
		{
			throw new Exception("Adapter $adapter does not exists");
		}
		$this->adapter = new $class($lang_file, $locale);
		if ($lang_file)
		{
			$this->adapter->load($lang_file, $locale);
		}
	}

	public function __call($method, $options)
	{
		if (!method_exists($this->adapter, $method))
		{
			throw new Exception("Method $method does not exists");
		}
		return call_user_func_array(array($this->adapter, $method), $options);
	}
}
?>