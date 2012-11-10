<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Requirement_Controller extends Install_Controller
{
	public $systemTest = true;

	private function assert($value, $result, $success, $failed)
	{
		$xhtml = '';

		if ($value !== $result)
		{
			$this->systemTest = false;
			$xhtml .= '<span class="failed">' . $failed . '</span>';
		}
		else
		{
			$xhtml .= '<span class="success">' . $success . '</span>';
		}

		return $xhtml;
	}

	function main()
	{
		$assert = array(
			
			'phpversion'	=>	$this->assert(version_compare(phpversion(), '5.2') > -1, true, PHP_VERSION, PHP_VERSION),
			'mysql'			=>	$this->assert(extension_loaded('mysql'), true, 'Dostępna', 'Niedostępna'),
			'json'			=>	$this->assert(extension_loaded('json'), true, 'Dostępna', 'Niedostępna'),
			'mbstring'		=>	$this->assert(extension_loaded('mbstring'), true, 'Dostępna', 'Niedostępna'),

			'register_globals'	=> $this->assert((bool)ini_get('register_globals'), false, 'Wyłączone', 'Włączone'),
			'magic_quotes_gpc'	=> $this->assert((bool)ini_get('magic_quotes_gpc'), false, 'Wyłaczone', 'Właczone')
		);

		$this->folders = array();
		foreach (array('store/', 'store/_a', 'store/_aa', 'store/css', 'cache/', 'log/', 'config/', 'tmp/') as $dir)
		{
			$result = true;

			if (!file_exists($dir))
			{
				$result = @mkdir($dir, 0777);
			}
			else
			{
				if (!is_writable($dir))
				{
					$result = @chmod($dir, 0777);
				}
			}
			$this->folders[trim($dir, '/')] = $this->assert($result, true, 'zapis możliwy', 'zapis niemożliwy');			
		}

		$this->files = array();
		foreach (array('module.xml', 'region.xml', 'route.xml', 'config.xml') as $file)
		{
			$result = true;

			if (!is_writeable('config/' . $file))
			{
				$result = @chmod('config/' . $dir, 0666);
			}
			$this->files[$file] = $this->assert($result, true, 'zapis możliwy', 'zapis niemożliwy');
		}

		$this->readable = array();
		foreach (array('db.xml.default', 'route.xml.default', 'autoload.xml.default', 'route.xml.copy') as $file)
		{
			$result = is_readable("config/$file");
			$this->readable[$file] = $this->assert($result, true, 'odczyt możliwy', 'plik nie istnieje lub nie posiada praw do odczytu!');
		}

		if ($this->input->isPost())
		{
			$this->session->install = 2;
			$this->redirect('Base');
		}

		return View::getView('setup/requirement', $assert);
	}
}
?>