<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Status_Controller extends Adm
{
	private function assert($value, $result, $success, $failed)
	{
		$xhtml = '';

		if ($value !== $result)
		{
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
			
			'phpversion'	=>	$this->assert(version_compare(phpversion(), '5.3') > -1, true, PHP_VERSION, PHP_VERSION),
			'framework'		=>	$this->assert(version_compare(phpversion(), '1.2') > -1, true, Core::getVersion(), Core::getVersion()),
			'mysql'			=>	$this->assert(extension_loaded('mysql'), true, 'Dostępna', 'Niedostępna'),
			'json'			=>	$this->assert(extension_loaded('json'), true, 'Dostępna', 'Niedostępna'),
			'mbstring'		=>	$this->assert(extension_loaded('mbstring'), true, 'Dostępna', 'Niedostępna'),
			'mcrypt'		=>	$this->assert(extension_loaded('mcrypt'), true, 'Dostępna', 'Niedostępna'),
			'eaccelerator'	=>	$this->assert(extension_loaded('eaccelerator'), true, 'Dostępna', 'Niedostępna'),
			'xcache'		=>	$this->assert(extension_loaded('xcache'), true, 'Dostępna', 'Niedostępna'),
			'apc'			=>	$this->assert(extension_loaded('apc'), true, 'Dostępna', 'Niedostępna'),			

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
		foreach (array('trigger.xml', 'module.xml', 'region.xml', 'route.xml') as $file)
		{
			$result = true;

			if (!is_writeable('config/' . $file))
			{
				$result = @chmod('config/' . $dir, 0666);
			}
			$this->files[$file] = $this->assert($result, true, 'zapis możliwy', 'zapis niemożliwy');
		}


		return View::getView('adm/status', $assert);

	}
}
?>