<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Infobox extends Plugin
{
	public function display()
	{
		if (!$this->module->isPluginEnabled('infobox'))
		{
			return false;
		}
		// robotom nie wyswietlamy - nie chcemy indeksowac tego
		if ($this->input->isRobot())
		{
			return false;
		}
		if (User::$id == User::ANONYMOUS)
		{
			return false;
		}

		$infobox = &$this->getModel('infobox');
		if ($result = $infobox->getInfobox())
		{
			$cookie = explode(',', $this->input->cookie('infobox'));
			if (!in_array($result['infobox_id'], $cookie))
			{
				echo new View('infobox', $result);
			}
		}
	}
}

?>