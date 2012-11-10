<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Phpinfo_Controller extends Adm
{
	function main()
	{
		ob_start();
		phpinfo();

		preg_match ('%<style type="text/css">(.*?)</style>.*?(<body>.*</body>)%s', ob_get_clean(), $matches);
		$this->phpinfo = $matches[2];

		return true;
	}
}
?>