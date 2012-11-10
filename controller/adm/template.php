<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Template extends Adm
{
	function main()
	{
		return true;
	}

	public function submit()
	{
		$path = $this->get['path'];
		$dirname = dirname($path);
		$basename = basename($path);

		$this->dirname = ltrim(str_replace('\\', '/', $this->filterPath($dirname)) . '/', '/');
		$this->path = $this->dirname . $basename;

		if (!preg_match('#^(template|module|plugin)/.*#i', $this->dirname))
		{
			throw new AcpErrorException('Nieprawidłowa nazwa pliku');
		}

		if (!file_exists($this->path))
		{
			throw new AcpErrorException('Podany szablon nie istnieje!');
		}
		$this->content = file_get_contents($this->path);

		$configObject = new View_Config;
		$config = $configObject->get($this->dirname);

		$this->tplName = $tplName = substr($basename, 0, strpos($basename, '.'));
		$this->pageTitle = $this->pageLayout = $this->pageStylesheet = $this->pageJavascript = array();

		foreach ($config as $tpl => $array)
		{
			if (preg_match('#^' . str_replace('*', '.*', $tpl) . '$#i', $tplName))
			{
				if (isset($array['title']))
				{
					$this->pageTitle[$tpl] = $array['title'];
				}
				if (isset($array['layout']))
				{
					$this->pageLayout[$tpl] = $array['layout'];
				}
				if (isset($array['stylesheet']))
				{
					$this->pageStylesheet[$tpl] = !is_array($array['stylesheet']) ? explode(',', $array['stylesheet']) : $array['stylesheet'];
				}
				if (isset($array['javascript']))
				{
					$this->pageJavascript[$tpl] = !is_array($array['javascript']) ? explode(',', $array['javascript']) : $array['javascript'];
				}
			}
		}
		unset($config);

		if ($this->input->isMethod(Input::POST))
		{
			if (!Auth::get('a_template'))
			{
				throw new AcpErrorException('Brak uprawnień do zapisu szablonów');
			}
			file_put_contents($this->path, $this->post['content'], LOCK_EX);
			@include($this->dirname . 'config.php');

			if (!$this->post->title)
			{
				unset($config[$tplName]['title']);
			}
			else
			{
				$config[$tplName]['title'] = $this->post->title;
			}

			if ($this->post->disableLayout)
			{
				$config[$tplName]['layout'] = false;
			}
			else
			{
				if ($this->post->layout)
				{
					$config[$tplName]['layout'] = $this->post->layout;
				}
				else
				{
					unset($config[$tplName]['layout']);
				}
			}

			if (!$this->post->stylesheet)
			{
				unset($config[$tplName]['stylesheet']);
			}
			else
			{
				$config[$tplName]['stylesheet'] = $this->post->stylesheet;
			}

			if (!$this->post->javascript)
			{
				unset($config[$tplName]['javascript']);
			}
			else
			{
				$config[$tplName]['javascript'] = $this->post->javascript;
			}

			// jezeli tablica jest pusta - usuwamy. nie ma sensu smiecic
			if (empty($config[$tplName]))
			{
				unset($config[$tplName]);
			}

			$content = "<?php\n\n \$config = " . var_export($config, true) . "\n\n?>";
			$this->isBackup = @copy("{$this->dirname}config.php", "{$this->dirname}.config.php.bak");

			$this->isSuccess = @file_put_contents($this->dirname . 'config.php', $content, LOCK_EX);

			return View::SUCCESS;
		}

		return true;
	}

	public function __template()
	{
		$dir = isset($this->get->dir) ? $this->get['dir'] : '';
		$dir = $this->filterPath($dir);

		if (!$dir)
		{
			$loadModules = true;
			$dir = 'template/';
		}
		else
		{
			if ($dir[strlen($dir) -1] != '/')
			{
				$dir .= '/';
			}
		}

		$xhtml = '<ul>';
		foreach ($this->getTplList($dir) as $entry)
		{
			if ($entry['filename'] == 'config.php')
			{
				continue;
			}

			$link = $entry['isDir'] ? Html::img(url('template/adm/img/folderIcon.png')) : Html::img(url('template/adm/img/pageIcon.png'));
			$link .= $entry['isDir'] ? '&nbsp;&nbsp;' . $entry['filename'] : Html::a(url('adm/Template/Submit?path=' . $dir . $entry['filename']), $entry['filename'], array('title' => 'Edytuj szablon'));

			if ($entry['isDir'])
			{
				$xhtml .= '<li><em class="close" title="' . $dir . $entry['filename'] . '"></em>' . $link . '</li>';
			}
			else
			{
				$xhtml .= '<li><em></em>' . $link . '</li>';
			}
		}

		if (isset($loadModules))
		{
			foreach ($this->getModuleList() as $name => $text)
			{
				$link = Html::img(url('template/adm/img/packageIcon.png'));
				$link .= '&nbsp; <span title="' . $text . '">' . Text::limit($text, 20) . '</span>';

				$xhtml .= '<li><em class="close" title="module/' . $name . '/template"></em>' . $link . '</li>';
			}

			foreach ($this->getPluginList() as $name => $text)
			{
				$link = Html::img(url('template/adm/img/pluginIcon.png'));
				$link .= '&nbsp; <span title="' . $text . '">' . Text::limit($text, 20) . '</span>';

				$xhtml .= '<li><em class="close" title="plugin/' . $name . '/template"></em>' . $link . '</li>';
			}
		}

		$xhtml .= '</ul>';
		echo $xhtml;

		exit;
	}

	private function filterPath($path)
	{
		return preg_replace('#\/+#', '/', str_replace('.', '', str_replace('config', '', $path)));
	}

	private function getModuleList()
	{
		$result = array();
		foreach ($this->module->getModules() as $name => $row)
		{
			if ($row['module_type'])
			{
				$result[$name] = $row['module_text'];
			}
		}

		return $result;
	}

	private function getPluginList()
	{
		$plugin = &$this->getModel('plugin');

		$result = array();
		foreach ($plugin->getPlugins() as $name => $row)
		{
			$result[$name] = $row['plugin_text'];
		}

		return $result;
	}

	private function getTplList($dir)
	{
		$result = array();
		$count = 0;

		foreach (scandir($dir) as $file)
		{
			if ($file{0} != '.')
			{
				$suffix = pathinfo($file, PATHINFO_EXTENSION);

				if ((is_dir($dir . $file) && $dir != 'adm') || $suffix == 'php')
				{
					$result[++$count] = array(
						'isDir'			=> is_dir($dir . $file),
						'filename'		=> $file,
						'filesize'		=> filesize($dir . $file)
					);

					$sort['dir'][$count] = is_dir($dir . $file) ? 0 : 1;
					$sort['name'][$count] = $file;
				}
			}
		}
		if ($result)
		{
			array_multisort($sort['dir'], $sort['name'], $result);
		}

		return $result;
	}
}
?>