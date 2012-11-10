<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Page_Version_Model extends Model
{
	protected $name = 'page_version';

	public function insert($pageId, $textId)
	{
		if (!$textId)
		{
			return false;
		}

		$query = $this->select()->where("page_id = $pageId AND text_id = $textId")->get();
		if (!count($query))
		{
			$prevTextId = $this->db->select('page_text')->from('page')->where("page_id = $pageId")->fetchField('page_text');

			parent::insert(array(
				'page_id'		=> $pageId,
				'text_id'		=> $textId
				)
			);

			if ($prevTextId)
			{
				$sql = "INSERT INTO page_attachment (text_id, attachment_id)
						 	SELECT $textId, attachment_id
						 	FROM page_attachment
						 	WHERE text_id = $prevTextId
								AND attachment_id IS NOT NULL";
				$this->db->query($sql);

				$query = $this->db->select('page_id')->from('page')->where("page_text = $prevTextId")->get();

				$sql = array();
				foreach ($query->fetchCol() as $id)
				{
					$sql[] = array(
						'page_id'		=> $id,
						'text_id'		=> $textId
					);
				}

				if ($sql)
				{
					$this->db->multiInsert('page_version', $sql);
				}
			}
		}
	}

	public function fetchVersions($pageId)
	{
		$query = $this->db->select()->from('page_version p, page_text t');
		$query->where("p.page_id = $pageId");
		$query->where("t.text_id = p.text_id");
		$query->leftJoin('user', 'user_id = text_user');
		$query->order('p.text_id DESC');

		return $query->fetchAll();
	}
}

class Page_Parser_Model extends Model
{
	protected $name = 'page_parser';

	public function insert($pageId, array $parserIds)
	{
		$this->delete("page_id = $pageId");
		$sql = array();

		foreach ($parserIds as $parserId)
		{
			$sql[] = array(
				'page_id'		=> $pageId,
				'parser_id'		=> (int)$parserId
			);
		}

		if ($sql)
		{
			$this->db->multiInsert($this->name, $sql);
		}
	}

	public function getParsers($pageId = 0)
	{
		if (!$pageId)
		{
			$query = $this->db->select()->from('parser')->order('parser_order')->get();
		}
		else
		{
			$sql = 'SELECT parser.*,
						   IF(page_id IS NOT NULL, 1, 0) AS parser_default
					FROM parser
					LEFT JOIN page_parser ON page_parser.parser_id = parser.parser_id AND page_id = ' . $pageId . '
					ORDER BY parser_order';
			$query = $this->db->query($sql);
		}

		return $query->fetch();

	}
}

class Page_Template_Model extends Model
{
	protected $name = 'page_template';

	public function fetchTemplates(&$content)
	{
		$template_arr = array();

		preg_match_all("#{{Template:(.*?)(\|(.*))*}}#i", $content, $matches);
		if ($matches[0])
		{
			$path = new Path;

			for ($i = 0, $limit = sizeof($matches[0]); $i < $limit; $i++)
			{
				$element = array();
				foreach (explode('/', $matches[1][$i]) as $part)
				{
					$element[] = $path->encode($part);
				}

				$template_arr[] = implode('/', $element);
			}
			$template_arr = array_unique($template_arr);
		}
		return $template_arr;
	}

	public function fetch($textId)
	{
		$sql = 'SELECT LOWER(page_v.location_text) AS location_text,
					   page_v.page_id,
					   page_v.text_content,
					   page_v.text_id
				FROM page_template t, page_v
				WHERE t.text_id = ' . $textId . '
						AND page_v.page_id = t.page_id';
		return $this->db->query($sql);
	}

	public function insert($textId, $template_arr)
	{
		$quote_arr = array_map(array('Text', 'quote'), $template_arr);
		$sql = array();

		$query = $this->db->select('page_id')->from('page_v')->where('location_text IN(' . implode(',', $quote_arr) . ')')->get();
		while ($row = $query->fetchAssoc())
		{
			$sql[] = array(
				'text_id'				=> $textId,
				'page_id'				=> $row['page_id']
			);
		}
		if ($sql)
		{
			$this->db->multiInsert($this->name, $sql);
		}
	}
}

class Page_Attachment_Model extends Model
{
	protected $name = 'page_attachment';
	protected $reference = array(

				'attachment'		=> array(

								'table'				=> 'attachment',
								'col'				=> 'page_attachment.attachment_id',
								'refCol'			=> 'attachment.attachment_id'
				)

	);


	public function insert($textId, $attachments)
	{
		if (!is_array($attachments))
		{
			$attachments = array($attachments);
		}
		$sql = array();

		foreach ($attachments as $id)
		{
			$sql[] = array(
				'text_id'			=> $textId,
				'attachment_id'		=> $id
			);
		}
		if ($sql)
		{
			$this->db->multiInsert($this->name, $sql);
		}
	}
}

class Page_Group_Model extends Model
{
	protected $name = 'page_group';

	public function getGroups($pageId)
	{
		$group = array();
		$query = $this->select('group_id')->where("page_id = $pageId")->get();

		foreach ($query as $row)
		{
			$group[] = $row['group_id'];
		}

		return $group;
	}

	public function setGroups($pageId, array $groupIds)
	{
		$this->delete("page_id = $pageId");
		$sql = array();

		foreach ($groupIds as $id)
		{
			$sql[] = array(
				'page_id'		=> $pageId,
				'group_id'		=> $id
			);
		}

		if ($sql)
		{
			$this->db->multiInsert('page_group', $sql);
		}
	}
}

class Page_Text_Model extends Model
{
	protected $name = 'page_text';
	protected $primary = 'text_id';
	protected $prefix = 'text_';

	protected $reference = array(

						'user'				=> array(


										'table'				=> 'user',
										'col'				=> 'text_user',
										'refCol'			=> 'user_id'

						)

	);

	public function insert(&$data)
	{
		parent::insert($data);
		return $this->db->nextId();
	}
}

class Page_Model extends Model
{
	protected $name = 'page';
	protected $prefix = 'page_';
	protected $primary = 'page_id';

	public $group;
	public $parser;
	public $attachment;
	public $template;
	public $version;
	public $text;

	function __construct()
	{
		$this->group = new Page_Group_Model;
		$this->parser = new Page_Parser_Model;
		$this->attachment = new Page_Attachment_Model;
		$this->template = new Page_Template_Model;
		$this->version = new Page_Version_Model;
		$this->text = new Page_Text_Model;
	}

	public function filter($pageId = null, $pageTime = null, $pageSubject = null, $pageLocation = null, $pageEditTime = null, $textUserId = null, $textIp = null, $order = null, $count = null, $limit = null)
	{
		$pageIds = array();

		if ($textIp && !$pageId)
		{
			$sql = 'SELECT v.page_id
					FROM page_text t
					INNER JOIN page_version v ON v.text_id = t.text_id
					WHERE t.text_ip LIKE "' . str_replace('*', '%', $textIp) . '"';

			$query = $this->db->query($sql);
			foreach ($query as $row)
			{
				$pageIds[] = $row['page_id'];
			}

			$pageIds = array_unique($pageIds);
		}

		$query = $this->select(($limit !== null || $count !== null ? 'SQL_CALC_FOUND_ROWS' : '') . ' page.page_id, page_subject, location_text, text_ip, user_id, user_name, page_time, page_edit_time');

		$query->innerJoin('location', 'location_page = page.page_id');
		$query->leftJoin('page_text t', 't.text_id = page_text');
		$query->leftJoin('user', 'user_id = t.text_user');

		if ($pageId)
		{
			$query->where('page_id = ?', $pageId);
		}
		else
		{
			if ($pageSubject)
			{
				$query->where('page_subject LIKE "' . str_replace('*', '%', $pageSubject) . '"');
			}
			if ($pageLocation)
			{
				$query->where('location_text LIKE "' . str_replace('*', '%', $pageLocation) . '"');
			}
			if ($pageTime)
			{
				$query->where('page_time > ?', time() - $pageTime);
			}
			if ($pageEditTime)
			{
				$query->where('page_edit_time > ?', time() - $pageEditTime);
			}
			if ($textUserId !== null)
			{
				if (is_int($textUserId))
				{
					$query->where('text_user = ?', $textUserId);
				}
				else
				{
					$query->where('text_user IN(SELECT user_id FROM user AS t1 WHERE t1.user_name LIKE ?)', str_replace('*', '%', $textUserId));
				}
			}
			if ($pageIds)
			{
				$query->where('page_id IN(' . implode(',', $pageIds) . ')');
			}
		}

		if ($order)
		{
			$query->order($order);
		}
		if ($count || $limit)
		{
			$query->limit($count, $limit);
		}

		return $query;
	}

	public function getFoundRows()
	{
		return (int)$this->db->query('SELECT FOUND_ROWS() AS totalItems')->fetchField('totalItems');
	}

	public function isAllowed($pageId, $userId)
	{
		$sql = "SELECT g.user_id
				FROM page_group p
				JOIN auth_group g ON g.group_id = p.group_id AND g.user_id = $userId
				WHERE p.page_id = $pageId";
		$query = $this->db->query($sql);

		return (bool) count($query);
	}

	public function getList($parentId = null)
	{
		$result = array();

		$query = $this->db->select('node.*, content_type, location_children');
		$query->from('page AS node');
		$query->leftJoin('location', 'location_page = node.page_id');
		$query->leftJoin('content', 'content_id = node.page_content');

		if ($parentId == null || !$parentId)
		{
			$query->where("node.page_parent IS NULL");
		}
		else
		{
			$query->where("node.page_parent = $parentId");
		}
		$query = $query->group('node.page_matrix')->get();

		foreach ($query as $row)
		{
			$result[] = $row;
		}

		return $result;
	}

	public function getDepth($pageId)
	{
		$query = $this->db->select('SELECT GET_DEPTH(' . $pageId . ') AS page_depth');
		return $query->fetchField('page_depth');
	}

	public function getHtmlList()
	{
		$result = array();
		foreach ($this->getList() as $row)
		{
			$result[$row['page_id']] = str_repeat('&nbsp;', $row['page_depth'] * 2) . $row['page_subject'];
		}

		return $result;
	}

	/**
	 * Zwraca liste "galezi" danej strony, czyli wszystkich stron potomnych
	 * @param $branchId		Id strony
	 * @return mixed
	 */
	public function getBranchList($branchId)
	{
		return $this->getChildren($branchId, null)->get();
	}

	public function findBySubject($subject)
	{
		$sql = "SELECT node.*, location_children, content_type
				FROM page AS node
				INNER JOIN page AS t2 ON t2.page_subject LIKE '%" . $subject . "%'
				INNER JOIN path ON child_id = t2.page_id
				INNER JOIN location ON location_page = node.page_id
				LEFT JOIN content ON content_id = node.page_content
				WHERE node.page_id = parent_id
				ORDER BY length DESC";
		$query = $this->db->query($sql);

		return $query->fetchAll();
	}

	public function findById($pageId)
	{
		/**
		 * Zapytanie realizuje pobranie wezlow bez kategorii macierzystych oraz
		 * wybranego wezla (dziecko) z rodzicem. Jezeli ktos ma pomysl jak lepiej
		 * zbudowac to zapytanie to bardzo prosze. Spodziewany rezultat:
		 * - Wezel macierzysty 1
		 * - Wezel macierzysty 2
		 * - Wezel macierzysty 3
		 *   - Dziecko 1
		 *     - Dziecko 2
		 *     - Dziecko 3 <-- szukamy tego ID pobiera czesc drzewa
		 * - Wezel macierzysty 3
		 */
		$sql = "SELECT node.*, content_type
				FROM page AS node
				JOIN page AS t2 ON t2.page_id = ?
				JOIN page AS t3 ON t3.page_parent = 0 AND (t3.left_id <= t2.left_id AND t3.right_id >= t2.right_id)
				LEFT JOIN content ON content_id = node.page_content
				WHERE node.page_parent = 0 OR (t2.page_parent != 0 AND node.left_id >= t3.left_id AND node.right_id <= t3.right_id)
				ORDER BY node.left_id
				LIMIT 100";
		$query = $this->db->query($sql, $pageId);

		return $query->fetch();
	}

	public function getById($pageId)
	{
		return $this->db->select()->from('page_v, connector')->where("page_id = $pageId AND connector_id = page_connector")->get();
	}

	public function getByPath($path)
	{
		$depth = 0;
		if (strpos($path, '/') !== false)
		{
			$nodes = explode('/', $path);

			$depth = sizeof($nodes);
			$subject = end($nodes);
		}
		else
		{
			$subject = $path;
		}

		$query = $this->db->select('location_text,
									location_children,
									page.*,
									page_text.*,
									meta.*,
									connector.*,
									content_type,
									cache_time,
					   				cache_content
					   			   ');
		$query->from('page, location');

		$query->innerJoin('connector', 'connector_id = page_connector');
		$query->leftJoin('page_text', 'text_id = page_text');
		$query->leftJoin('content', 'content_id = page_content');
		$query->leftJoin('meta', 'meta_page = page_id');
		$query->leftJoin('page_cache', 'cache_page = page_id');

		$query->where('page_path = ?', $subject);
		$query->where('(location_page = page.page_id AND location_text = ?)', $path);

		return $query->get();
	}

	public function getByBranch($branch)
	{
		$sql = 'SELECT location.location_text,
					   page.*,
					   page_text.*,
					   meta.*,
					   connector.*,
					   content_type,
					   cache_time,
					   cache_content
				FROM (location, page, text, connector)
				LEFT JOIN content ON content_id = page_content
				LEFT JOIN meta ON meta.meta_page = page.page_id
				LEFT JOIN page_cache ON cache_page = page_id
				WHERE page.page_path = ?
					AND path.path_page = page.page_id
							AND text_id = page_text
								AND connector_id = page_connector';
		return $this->db->query($sql, $branch);
	}

	/**
	 * Zwraca liste stron - rodzicow danej podstrony w formie tablicy z informacjami
	 * o stronach. Sortowanie wedlug parametru left_id, stad na pierwszym miejscu w
	 * tablicy bedzie element root
	 * @param int $pageId	ID strony
	 * @return array Tablica stron
	 */
	public function getParents($pageId)
	{
		$query = $this->db->select()
						  ->from('page')
						  ->innerJoin('path', "child_id = $pageId AND `length` > 0")
						  ->leftJoin('location', 'location_page = page_id')
						  ->where("page_id = parent_id")
						  ->order('`length` DESC');
		return $query;
	}

	public function getChildren($pageId, $limit = null)
	{
		$query = $this->db->select()
						  ->from('page')
						  ->innerJoin('path', "parent_id = $pageId AND `length` > 0")
						  ->leftJoin('location', 'location_page = page_id')
						  ->where("page_id = child_id")
						  ->order('page_matrix');

		if ($limit !== null)
		{
			$query->limit($limit);
		}

		return $query;
	}

	public function copy($pageId, $parentId)
	{
		$query = $this->db->select('t1.*')
						->from('page t1')
						->innerJoin('path', "parent_id = $pageId")
						->where('t1.page_id = child_id')
						->order('t1.page_matrix')
						->get();

		$result = $query->fetch();

		foreach ($result as $row)
		{
			$newParentId = isset($parent[$row['page_parent']]) ? $parent[$row['page_parent']] : $parentId;
			if (!$newParentId)
			{
				$newParentId = null;
			}

			try
			{
				/*
				 * Tworzenie nowego wpisu w tabeli `page` to newralgiczna operacja
				 * Na czas tworzenia nowej galezi w drzewie stron, blokujemy zapis
				 * do tabel `page` oraz `path`
				 */
				$this->db->lock('page WRITE', 'location WRITE');

				$data = array(
					'page_subject'			=> $row['page_subject'],
					'page_module'			=> $row['page_module'],
					'page_parent'			=> $newParentId,
					'page_title'			=> $row['page_title'],
					'page_publish'			=> $row['page_publish'],
					'page_cache'			=> $row['page_cache'],
					'page_content'			=> $row['page_content'],
					'page_richtext'			=> $row['page_richtext'],
					'page_published'		=> $row['page_published'], // moze byc null
					'page_unpublished'		=> $row['page_unpublished'], // moze byc null
					'page_template'			=> $row['page_template'],
					'page_connector'		=> $row['page_connector'],
					'page_path'				=> $row['page_path'],
					'page_time'				=> $row['page_time'],
					'page_edit_time'		=> time()
				);

				$this->db->insert('page', $data);

				$id = $this->db->nextId();
				$parent[$row['page_id']] = $id;

				$this->db->unlock();
			}
			catch (Exception $e)
			{
				$this->db->unlock();

				throw new Exception($e->getMessage());
			}


			$sql = "INSERT INTO module_config (config_module, config_field, config_page, config_value)
						SELECT config_module, config_field, $id, config_value
						FROM module_config
						WHERE config_page = $row[page_id]";
			$this->db->query($sql);

			$sql = "INSERT INTO page_version (page_id, text_id)
						SELECT $id, text_id
						FROM page_version
						WHERE page_id = $row[page_id]";
			$this->db->query($sql);

			$sql = "INSERT INTO watch (page_id, user_id)
						SELECT $id, user_id
						FROM watch
						WHERE page_id = $row[page_id]";
			$this->db->query($sql);

			$sql = "INSERT INTO page_group (page_id, group_id)
						SELECT $id, group_id
						FROM page_group
						WHERE page_id = $row[page_id]";
			$this->db->query($sql);

			$sql = "INSERT INTO page_parser (page_id, parser_id)
						SELECT $id, parser_id
						FROM page_parser
						WHERE page_id = $row[page_id]";
			$this->db->query($sql);

		}

		return $parent[$pageId];
	}

	public function move($id, $parentId)
	{
		try
		{
			$this->db->lock('page WRITE, path WRITE, node WRITE, accessor WRITE, broken WRITE, redirect WRITE, page_cache WRITE, page_template WRITE');
			$result = $this->db->query('CALL PAGE_MOVE(?, ?)', (int) $id, !$parentId ? null : $parentId);

			$this->db->unlock();
		}
		catch (Exception $e)
		{
			$this->db->unlock();
			$result = false;
		}

		return $result;
	}

	public function getCategoriesByText($textId)
	{
		if (!$textId)
		{
			return false;
		}
		$sql = "SELECT page_parent
				FROM (page)
				WHERE page_text = $textId AND page_parent IS NOT NULL";

		$query = $this->db->query($sql);
		$parents = $query->fetchCol();

		if ($parents)
		{
			$query = $this->db->select()
							  ->from('page')
							  ->innerJoin('location', 'location_page = page_id')
							  ->in('page_id', $parents)
							  ->get();
		}

		return $query;
	}

	public function getCategories($pageId)
	{
		$query = $this->db->select('page_text')->from('page')->where("page_id = $pageId");
		return $this->getCategoriesByText($query->fetchField('page_id'));
	}

	public function setDelete($pageId)
	{
		$sql = "UPDATE page t1
				INNER JOIN path ON parent_id = $pageId
				SET t1.page_delete = 1
				WHERE t1.page_id = child_id";
		$this->db->query($sql);
	}

	public function setRestore($pageId)
	{
		$sql = "UPDATE page t1
				INNER JOIN path ON parent_id = $pageId
				SET t1.page_delete = 0
				WHERE t1.page_id = child_id";
		$this->db->query($sql);
	}

	public function delete($pageId)
	{
		$sql = "SELECT attachment_id
				FROM page_attachment
				WHERE text_id IN(

					SELECT text_id
					FROM page_version
					WHERE page_id = $pageId
				)";
		$query = $this->db->query($sql);
		$attachmentIds = array();

		foreach ($query as $row)
		{
			$attachmentIds[] = $row['attachment_id'];
		}

		if ($attachmentIds)
		{
			$attachment = &$this->load->model('attachment');
			$attachment->delete(array_unique($attachmentIds), false);
		}

		parent::delete('page_id = ' . $pageId);
	}

	/**
	 * Sprawdza, czy pod dana sciezka znajduje sie jakas strona
	 * Jezeli tak, zwraca jego ID
	 * @return int
	 */
	/*public function isPath($path)
	{
		return $this->load->model('path')->getByText($path)->fetchField('path_page');
	}*/

	public function hasMoved($path)
	{
		$query = $this->db->select('location_text')->from('redirect, location')->where('redirect_path = ? AND location_page = redirect_page', $path);
		if (!count($query))
		{
			return false;
		}
		else
		{
			return $query->fetchField('location_text');
		}
	}
}
?>