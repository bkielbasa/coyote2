<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
 
class Location_Model extends Model
{
	protected $name = 'location';
	protected $primary = 'location_page';
	
	/**
	 * Zwraca ID strony na podstawie sciezki 
	 * @param string $locationText	Sciezka - np. /Foo/Bar
	 * @return int
	 */
	public function getPageId($locationText)
	{
		return $this->select('location_page')->where('location_text = ?', $locationText)->fetchField('location_page');
	}
}
?>