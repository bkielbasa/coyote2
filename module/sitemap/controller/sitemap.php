<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Sitemap_Controller extends Controller
{
	function __start()
	{
		if (!is_writeable('cache/'))
		{
			throw new Exception('Katalog /cache nie posiada praw do zapisu!');
		}
		if (!is_writeable('config/route.xml'))
		{
			throw new Exception('Plik route.xml nie posiada praw do zapisu!');
		}

		set_time_limit(0);
	}

	function main()
	{
		$pageId = $this->page->getId();
		$wait = 0;

		while (file_exists('cache/' . $pageId . '.mutex'))
		{
			sleep(1);

			if (++$wait == 120)
			{
				exit('Timeout');
			}
		}

		file_put_contents('cache/' . $pageId . '.mutex', '');

		if (!$this->module->sitemap('sitemapIndex', $pageId))
		{
			$this->createSingleMap();
		}
		else
		{
			$this->createMultipleMaps();
		}
	}

	private function &getData()
	{
		$root = $this->page->getParentId();
		$page = &$this->getModel('page');

		$query = $this->db->select('node.page_id, location_text, node.page_edit_time, node.page_depth')->from('page AS node');
		$query->leftJoin('page_group pp', 'pp.page_id = node.page_id');
		$query->innerJoin('location', 'location_page = node.page_id');

		if ($root)
		{
			$query->innerJoin('path', 'parent_id = ' . $root);
			$query->where("node.page_id = child_id");
		}
		$query->where('page_publish = 1 AND page_delete = 0');
		$query->where('pp.group_id IN(1, 2)');
		$query->group('node.page_id');

		$result = array();
		$host = $this->input->getHost();

		foreach ($query->get() as $row)
		{
			$url = url($row['location_text']);

			if (parse_url($url, PHP_URL_HOST) == $host)
			{
				$result[] = $row;
			}
		}

		return $result;
	}

	private function createSingleMap()
	{
		$pageId = $this->page->getId();

		if (!file_exists("cache/sitemap_$pageId") ||
			time() - ($this->module->sitemap('sitemapCache', $pageId) * 3600) > filemtime("cache/sitemap_$pageId")
		)
		{
			$result = $this->getData();

			$this->putXml('sitemap_' . $pageId, $result);
		}

		$this->readData('sitemap_' . $pageId);
		unlink('cache/' . $pageId . '.mutex');
		exit;
	}

	private function isFilesExists(array $files)
	{
		$result = true;

		foreach ($files as $file)
		{
			if (!file_exists('cache/' . $file))
			{
				$result = false;
				break;
			}
		}

		return $result;
	}

	private function createMultipleMaps()
	{
		$pageId = &$this->page->getId();
		$maps = $this->module->sitemap('sitemapIndex', $pageId);

		$files = array();
		for($i = 1; $i <= $maps; $i++)
		{
			$files[] = "sitemap_{$pageId}_{$i}";
		}

		$lifetime = time() - ($this->module->sitemap('sitemapCache', $pageId) * 3600);

		if (!file_exists("cache/sitemap_$pageId") ||
			$lifetime > filemtime("cache/sitemap_$pageId") ||
				!$this->isFilesExists($files)
		)
		{
			$result = $this->getData();
			$chunks = array_chunk($result, ceil(sizeof($result) / $maps));

			foreach ($chunks as $index => $array)
			{
				$this->putXml('sitemap_' . $pageId . '_' . ($index + 1), $array);
			}
			unset($chunks);

			$fp = fopen('cache/sitemap_' . $pageId, 'w');
			fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
			fwrite($fp, "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n");

			$baseUrl = url($this->page->getLocation());
			$url = parse_url($baseUrl);

			$pathinfo = pathinfo($url['path']);

			$route = &$this->getModel('route');

			for ($i = 1; $i <= $maps; $i++)
			{
				$path = $pathinfo['filename'] . $i . ($pathinfo['extension'] ? '.' . $pathinfo['extension'] : '');

				fwrite($fp, "<sitemap>\n");
				fwrite($fp, "<loc>" . (str_replace($this->page->getPath(), $path, $baseUrl)) . "</loc>\n");
				fwrite($fp, "<lastmod>" . date('Y-m-d') . "</lastmod>\n");
				fwrite($fp, "</sitemap>\n");

				if (!$route->isExists("page_{$pageId}_{$i}"))
				{
					$route->insert(array(
						'name'				=> "page_{$pageId}_{$i}",
						'url'				=> $pathinfo['filename'] . $i,
						'controller'		=> 'sitemap',
						'action'			=> 'view',
						'page'				=> $pageId,
						'suffix'			=> $pathinfo['extension']
						)
					);
				}
			}

			fwrite($fp, "</sitemapindex>\n");
			fclose($fp);
		}

		$this->readData('sitemap_' . $pageId);
		unlink('cache/' . $pageId . '.mutex');
		exit;
	}

	public function view()
	{
		$pageId = $this->page->getId();

		$fileName = pathinfo($this->input->getPath(), PATHINFO_FILENAME);
		$id = (int) substr($fileName, strlen($fileName) -1, 1);

		$this->readData("sitemap_{$pageId}_{$id}");
		exit;
	}

	private function putXml($fileName, &$data)
	{
		$fp = fopen('cache/' . $fileName, 'w');
		fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
		fwrite($fp, "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n");

		foreach ($data as $row)
		{
			if (!$row['location_text'])
			{
				$row['location_text'] = Url::base();
			}

			$url = url($row['location_text']);

			fwrite($fp, "<url>\n");
			fwrite($fp, "<loc>" . htmlspecialchars($url, ENT_COMPAT, 'UTF-8') . "</loc>\n");

			if (($priority = (1.0 - ($row['page_depth'] / 10))) > 0.1)
			{
				fwrite($fp, '<priority>' . str_replace(',', '.', (string) sprintf('%.1f', $priority)) . "</priority>\n");
			}
			fwrite($fp, '<lastmod>' . date('Y-m-d', $row['page_edit_time']) . "</lastmod>\n");
			fwrite($fp, "</url>\n");
		}

		fwrite($fp, "</urlset>\n");
		fclose($fp);
	}

	private function setHeader()
	{
		$this->output->setContentType('text/xml');
	}

	private function readData($fileName)
	{
		if (!$fp = fopen("cache/$fileName", 'r'))
		{
			Log::add("cache/$fileName not found", E_ERROR);
			throw new Exception("File not found: cache/$fileName");
		}
		$this->setHeader();

		while (!feof($fp))
		{
			echo fread($fp, 4098);
			flush();
		}
		fclose($fp);
	}
}
?>