<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Path_Model extends Model
{
	protected $name = 'path';
	protected $prefix = 'path_';
	protected $primary = 'path_id';

	/**
	 * Usuwa z podanej sciezki niepotrzebne i niebezpieczne znaki 
	 * @param string $path Sciezka 
	 * @return string
	 */
	public static function path($path)
	{
		$path = Filter::call($path, new Filter_Injection);
		/**
		 * @todo Czy to wszystko? Czy trzeba usuanc cos jeszcze?
		 */
		$path = trim(str_replace(array('.'), '', $path), '/');

		return $path;
	}

	/**
	 * Zwraca ID tekstow odpowiadajace podanemu wzorcowi. UWAGA! To zapytanie moze
	 * byc obciazajace, gdy posiadamy wiecej tekstow, ze wzgledu na uzycie zapytania
	 * LIKE
	 * @example getPath('Foo/Bar/*'); 
	 * @param string $path Sciezka dostepu
	 * @return mixed
	 */
	/*public function getPath($path)
	{
		$path = str_replace('*', '%', $path);

		$sql = 'SELECT * 
				FROM location
				WHERE location_text LIKE "' . $path . '"';
		return $this->db->query($sql);
	}*/
	
	public function asArray($pageId)
	{
		if (!$pageId)
		{
			return false;
		}
		
		$query = $this->db->select('page_subject, location_text')
					  ->from('page')
					  ->innerJoin('path', "child_id = $pageId")
					  ->innerJoin('location', 'location_page = page_id')
					  ->where('page_id = parent_id')
					  ->order('`length` DESC');
					  
		return $query->get();		
	}

}
?>