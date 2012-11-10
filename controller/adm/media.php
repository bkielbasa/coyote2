<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Media_Controller extends Adm
{
	function main()
	{
		$this->dir = isset($this->get->dir) ? $this->get['dir'] : '';
		$this->dir = $this->filterPath($this->dir);

		$this->part = array();

		$part = '';
		foreach (explode('/', $this->dir) as $element)
		{
			if (trim($element))
			{
				$part .= '/' . $element;
				$this->part[] = Html::a(url('adm/Media?dir=' . $part), $element);
			}
		}
		$this->part = implode('<span>&nbsp;</span>', $this->part);

		$this->media = $this->getFolderList($this->dir);

		if ($this->dir != '' && $this->dir != '/')
		{
			$this->rootDir = '/';
			$path = explode('/', $this->dir);

			if (isset($path[sizeof($path) -2]))
			{
				$this->backDir = $path[sizeof($path) -2];
			}
		}

		return true;
	}

	private function filterPath($path)
	{
		return preg_replace('#\/+#', '/', str_replace('.', '', $path));
	}

	private function getFolderList($dir)
	{
		$dir = Config::getBasePath() . $dir . '/';
		$result = array();
		$count = 0;

		foreach (scandir($dir) as $file)
		{
			if ($file{0} != '.')
			{
				$suffix = pathinfo($file, PATHINFO_EXTENSION);

				$result[++$count] = array(
					'isDir'			=> is_dir($dir . $file),
					'filemtime'		=> filemtime($dir . $file),
					'filename'		=> $file,
					'filesize'		=> filesize($dir . $file),
					'fileperms'		=> substr(sprintf('%o', fileperms($dir . $file)), -4),
					'suffix'		=> $suffix
				);

				$sort['dir'][$count] = is_dir($dir . $file) ? 0 : 1;
				$sort['name'][$count] = $file;
			}			
		}
		if (isset($sort))
		{
			array_multisort($sort['dir'], $sort['name'], $result);
		}

		return $result;
	}

	function isBinary($path) 
	{ 
		if (file_exists($path)) 
		{ 
			if (!is_file($path))
			{
				return false; 
			}

			$fp  = fopen($path, "r"); 
			$data = fread($fp, 512); 
			fclose($fp); 
			clearstatcache(); 

			return (false || substr_count($data, "^ -~") / 512 > 0.3 || substr_count($data, "\x00") > 0); 
		}
	   
		return false; 
	} 

	public function upload()
	{
		if (!Auth::get('a_media'))
		{
			throw new AcpErrorException('Brak uprawnień do wyświetlenia tej strony!');
		}
		$dir = isset($this->get->dir) ? $this->get['dir'] : '';
		$dir = $this->filterPath($dir);
		$dir = ltrim($dir, '/');

		if (!$dir)
		{
			$dir = Config::getBasePath();
		}

		if (!file_exists($dir))
		{
			throw new AcpErrorException('Katalog o podanej nazwie nie istnieje!');
		}		
		if (!is_writeable($dir))
		{
			throw new AcpErrorException('Podany katalog nie posiada praw zapisu!');
		}
		
		$upload = &$this->getLibrary('upload');
		$upload->setDestination($dir);

		try
		{
			$upload->setOverwrite((bool)$this->post->overwrite);

			if (!$upload->recive('Filedata'))
			{
				throw new Exception('Nie można umieścić pliku na serwerze!');
			}
		}
		catch (Exception $e)
		{
			Box::information('Błąd', $e->getMessage(), '', 'adm/information_box');
			exit;
		}

		$this->redirect('adm/Media?dir=' .  str_replace(Config::getBasePath(), '', $dir));
	}

	public function delete()
	{
		if (!Auth::get('a_media'))
		{
			throw new AcpErrorException('Brak uprawnień do wyświetlenia tej strony!');
		}

		$path = $this->get['path'];
		$dirname = dirname($path);
		$basename = basename($path);

		$this->dirname = ltrim(str_replace('\\', '/', $this->filterPath($dirname)) . '/', '/');
		$path = $this->dirname . $basename;

		if ($path == 'config/db.xml')
		{
			throw new AcpErrorException('Usunięcie pliku nie jest możliwe');
		}

		if (!file_exists($path))
		{
			throw new AcpErrorException('Podany plik nie istnieje!');
		}

		if (Box::confirm('Usuwanie pliku', "UWAGA! Czy na pewno chcesz usunąć plik <strong>$path</strong>", '', '', 'adm/confirmation_box'))
		{
			@unlink($path);
			Box::information('Plik został usunięty', 'Plik został bezpowrotnie usunięty', url('adm/Media'), 'adm/information_box');
		}
		else
		{
			$this->redirect('adm/Media?dir=' . $this->dir);
		}
	}

	public function submit()
	{
		if (!Auth::get('a_media'))
		{
			throw new AcpErrorException('Nie masz uprawnień do podglądu tego pliku');
		}
		$path = $this->get['path'];
		$dirname = dirname($path);
		$basename = basename($path);

		$this->dirname = ltrim(str_replace('\\', '/', $this->filterPath($dirname)) . '/', '/');
		$this->path = $path = $this->dirname . $basename;

		if ($this->path == 'config/db.xml')
		{
			throw new AcpErrorException('Podgląd pliku niemożliwy');
		}

		$this->mediaSuffix = pathinfo($basename, PATHINFO_EXTENSION);

		if (in_array($this->mediaSuffix, array('jpg', 'jpeg', 'gif', 'png')))
		{
			if ($this->input->isMethod(Input::POST))
			{
				rename($this->path, $this->dirname . $this->post['name']);
				$this->redirect('adm/Media?dir=' . $this->dirname);
			}

			return 'View';	
		}
		elseif ($this->isBinary($path))
		{
			$this->redirect($path);
		}
		else
		{
			$this->content = '';

			if ($this->input->isMethod(Input::POST))
			{
				if (@file_put_contents($this->dirname . $this->post['name'], $this->post['content']))
				{
					$this->message = 'Plik został poprawnie zapisany';
				}
				else
				{
					$this->error = 'Plik nie został zapisany. Nazwa pliku jest nieprawidłowa lub nie posiadasz praw do zapisu pliku/katalogu';
				}
			}
			
			if (file_exists($path))
			{
				$this->content = file_get_contents($path);
			}
		}
		
		$this->anchor = array();
		$base = '';
		
		foreach (explode('/', $this->path) as $part)
		{
			$base .= '/' . $part;			
			
			if (is_dir(Config::getBasePath() . $base))
			{
				$this->anchor[] = Html::a(url('adm/Media?dir=' . $base), $part);
			}
			else
			{
				$this->anchor[] = Html::a(url('adm/Media/Submit?path=' . $base), $part);
			}
		}

		return View::SUBMIT;
	}
}
?>