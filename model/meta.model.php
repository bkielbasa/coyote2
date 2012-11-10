<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Meta_Model extends Model
{
	protected $name = 'meta';
	protected $primary = 'meta_page';

	public function setMeta($pageId, $metaTitle = '', $metaKeywords = '', $metaDescription = '')
	{
		$sql = 'INSERT INTO meta (meta_page, meta_title, meta_description, meta_keywords) VALUES(?, ?, ?, ?) ON DUPLICATE KEY UPDATE meta_title = ?, meta_description = ?, meta_keywords = ?';
		$this->db->query($sql, (int)$pageId, (string)$metaTitle, (string)$metaDescription, (string)$metaKeywords, (string)$metaTitle, (string)$metaDescription, (string)$metaKeywords);
	}
}
?>