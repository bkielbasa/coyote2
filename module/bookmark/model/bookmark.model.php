<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Bookmark_User_Model extends Model
{
	protected $name = 'bookmark_user';

	public function fetch($userId, $limit = null, $count = null)
	{
		$sql = 'SELECT * 
				FROM bookmark_user
				JOIN bookmark USING(bookmark_id)
				JOIN page_v ON page_id = bookmark_page
				WHERE bookmark_user = ' . $userId;
		if ($limit || $count)
		{
			$sql .= " LIMIT $limit, $count";
		}
		return $this->db->query($sql);
	}	
}

class Bookmark_Rank_Model extends Model
{
	protected $name = 'bookmark_rank';

	protected $reference = array(

				'user'				=> array(

									'table'			=> 'user',
									'col'			=> 'user_id',
									'refCol'		=> 'digg_user'
				)
	);

	public function setRank($bookmarkId, $value)
	{
		$query = $this->select('rank_value')->where("rank_bookmark = $bookmarkId AND rank_user = " . User::$id)->get();

		if (!count($query))
		{
			$this->insert(array(
				'rank_bookmark'		=> $bookmarkId,
				'rank_time'			=> time(),
				'rank_user'			=> User::$id,
				'rank_value'		=> $value
				)
			);
		}
		else
		{
			$currRank = $query->fetchField('rank_value');

			if ($value == $currRank)
			{
				$this->delete("rank_bookmark = $bookmarkId AND rank_user = " . User::$id);
			}
			else
			{
				$this->update(array('rank_value' => $value), "rank_bookmark = $bookmarkId AND rank_user = " . User::$id);
			}
		}

		$query = $this->db->select('bookmark_rank')->from('bookmark')->where("bookmark_id = $bookmarkId")->get();
		return $query->fetchField('bookmark_rank');
	}

}

class Bookmark_Model extends Model
{
	protected $name = 'bookmark';
	protected $primary = 'bookmark_id';
	protected $prefix = 'bookmark_';

	public $user;
	public $rank;

	function __construct()
	{
		$this->user = new Bookmark_User_Model;
		$this->rank = new Bookmark_Rank_Model;
	}

	public function fetch($where = null, $order = null, $limit = null, $count = null, $having = null)
	{
		$query = $this->db->select('SQL_CALC_FOUND_ROWS bookmark.*, page_v.*')->from('bookmark, page_v');

		if ($this->module->isPluginEnabled('comment'))
		{
			$query->select('COUNT(comment_id) AS bookmark_comment')->leftJoin('comment', 'comment_page = bookmark_page');
		}
		$query->select('
			(
				SELECT MAX(rank_time)
				FROM bookmark_rank
				WHERE rank_bookmark = bookmark_id
			) AS rank_time
		');

		if ($where)
		{
			$query->where($where);
		}
		if ($order)
		{
			$query->order($order);
		}
		if ($limit || $count)
		{
			$query->limit($limit, $count);
		}

		$query->where('page_id = bookmark_page');
		$query->group('bookmark.bookmark_id');

		if ($having)
		{
			$query->having($having);
		}

		return $query->get();
	}

	public function getFoundRows()
	{
		return (int)$this->db->query('SELECT FOUND_ROWS() AS totalItems')->fetchField('totalItems');
	}
}
?>