<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Path
{
	protected $filter;
	protected $enableDefaultSettings = true;

	function __construct($enableDefaultSettings = true)
	{
		$this->filter = new Filter;

		$this->setDefaultSettings($enableDefaultSettings);

		if ($this->isEnableDefaultSettings())
		{
			$this->loadDefaultSettings();
		}
	}

	public function setDefaultSettings($flag)
	{
		$this->enableDefaultSettings = $flag;
	}

	public function isEnableDefaultSettings()
	{
		return $this->enableDefaultSettings;
	}

	public function loadDefaultSettings()
	{
		$this->filter->reset();

		$this->filter->addFilter('strip_tags');

		Load::loadFile('lib/filter/stringTrim.class.php');
		$this->filter->addFilter(new Filter_StringTrim('/.'));

		if (Config::getItem('url.remove'))
		{
			$charList = str_split(Config::getItem('url.remove'));
			$charList[] = '/';

			$this->filter->addFilter(new Filter_Replace($charList));
		}
		if (Config::getItem('url.diacritics') == 'true')
		{
			$this->filter->addFilter(new Filter_Diacritics);
		}
		if (Config::getItem('url.lowercase') == 'true')
		{
			$this->filter->addFilter('strtolower');
		}
		if (Config::getItem('url.ucfirst') == 'true')
		{
			$this->filter->addFilter('ucfirst');
		}
	}

	public function &getFilter()
	{
		return $this->filter;
	}

	public function encode($path)
	{
		$spaceChar = Config::getItem('url.spaceChar');

		$path = $this->filter->filterData($path);
		$path = str_replace(' ', $spaceChar, str_replace(array("\t", "\n"), '', $path));

		$path = trim(preg_replace('/[\\' . $spaceChar . ']+/', $spaceChar, $path), $spaceChar);

		return $path;
	}

	public static function connector($name)
	{
		static $path = array();

		if (!isset($path[$name]))
		{
			$core = &Core::getInstance();
			$query = $core->db->select('location_text')->from('page')
							  ->leftJoin('location', 'location_page = page_id')
							  ->where('page_connector = (SELECT connector_id FROM connector WHERE connector_name = "' . $name . '")')
							  ->limit(1)
							  ->get();

			$path[$name] = $query->fetchField('location_text');
		}

		return $path[$name];
	}
}
?>