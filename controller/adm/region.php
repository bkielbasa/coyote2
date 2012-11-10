<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Region_Controller extends Adm
{
	public $cacheConfig = array(
			Region::NO_CACHE			=> 'Nie',
			Region::ANONYMOUS_CACHE		=> 'Tylko dla niezalogowanych',
			Region::ALL_CACHE			=> 'Tak'
	);

	function main()
	{
		if ($this->input->getMethod() == Input::POST)
		{
			$xml = simplexml_load_file('config/region.xml');
			$cacheData = $this->post->cache;

			foreach ($xml->region as $row)
			{
				$row->cache = $cacheData[(string)$row->name];
			}

			if ($this->post['name'])
			{
				foreach ($this->post['name'] as $index => $name)
				{
					if ($name = htmlspecialchars(trim($name)))
					{
						$node = $xml->addChild('region');
						$node->addChild('name', $name);
						$node->addChild('text', htmlspecialchars($this->post['text'][$index]));
						$node->addChild('cache', (int)$this->post['cache'][$index]);
					}

					unset($cacheData[$index]);
				}
			}

			if ($delete = $this->post->delete)
			{
				foreach ($delete as $name)
				{
					for ($i = 0; $i < sizeof($xml->region); $i++)
					{
						if ($xml->region[$i]->name == $name)
						{
							unset($xml->region[$i]);
						}			
					}
				}
			}			
		
			file_put_contents('config/region.xml', $xml->asXml(), LOCK_EX);
			$this->cache->destroy();

			$this->redirect('adm/Region');
		}

		return View::MAIN;
	}
}
?>