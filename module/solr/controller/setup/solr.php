<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Solr_Controller extends Controller
{
	public function install()
	{
		$xml = simplexml_load_file('module/solr/solr.xml');
		
		$setup = &Load::loadClass('setup');
		$id = $setup->addModule((string) $xml->name, (string) $xml->text, (string) $xml->version);
		
		$connector = &$xml->setup->connector;
		$setup->importConnector($id, (string) $connector->name, (string) $connector->text, (string) $connector->class, (string) $connector->controller, (string) $connector->action, (string) $connector->folder); 
		
		$search = new Search_Model;
		$search->insert(array(
			'search_name'		=> 'Solr',
			'search_class'		=> 'solr',
			'search_enable'		=> 0,
			'search_default'	=> 0
			)
		);
		
		Box::information('Moduł zainstalowany', "Moduł <i>solr</i> został poprawnie zainstalowany", url('adm/Module'), 'adm/information_box');
	}
	
	public function uninstall()
	{
		$setup = &Load::loadClass('setup');
		$setup->deleteModule('solr');
		
		$search = new Search_Model;
		$search->delete('search_class = "solr"');

		Box::information('Moduł odinstalowany', "Moduł <i>solr</i> został poprawnie odinstalowany", url('adm/Module'), 'adm/information_box');
	}
}
?>