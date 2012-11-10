<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class News_Vote_Model extends Model
{
	protected $name = 'news_vote';
	protected $prefix = 'vote_';
	
	public function setVote($newsId, $value)
	{
		if ($value != -1 && $value != 1)
		{
			$value = 1;
		}

		$data = array(
			'vote_news'			=> $newsId,
			'vote_time'			=> time(),
			'vote_user'			=> User::$id,
			'vote_value'		=> $value
		);
		$query = $this->select('vote_value')->where("vote_news = $newsId AND vote_user = " . User::$id)->get();

		if (!count($query))
		{
			$this->insert($data);
		}
		else
		{
			$currRank = $query->fetchField('vote_value');

			if ($value == $currRank)
			{
				$this->delete("vote_news = $newsId AND vote_user = " . User::$id);
			}
			else
			{
				$this->delete("vote_news = $newsId AND vote_user = " . User::$id);
				$this->insert($data);
			}
		}

		$query = $this->db->select('news_rate')->from('news')->where("news_id = $newsId")->get();
		return $query->fetchField('news_rate');
	}
	
	public function getRecentTime($newsId)
	{
		$query = $this->select('MAX(vote_time)')
					  ->where("vote_news = $newsId")
					  ->get();
					  
		return $query->fetchField('MAX(vote_time)');
	}
}

class News_Model extends Model
{
	protected $name = 'news';
	protected $primary = 'news_id';
	protected $prefix = 'news_';
	
	public $vote;
	
	function __construct()
	{
		$this->vote = new News_Vote_Model;
	}
	
	public function fetch($where = null, $order = null, $limit = null, $count = null, $having = null)
	{
		$query = $this->db->select('news.*, page_subject, location_text, text_content')->from('news');
		$query->innerJoin('page', 'page_id = news_page');
		$query->innerJoin('location', 'location_page = page_id');
		$query->leftJoin('page_text', 'text_id = page_text');

		if ($this->module->isPluginEnabled('comment'))
		{
			$query->select('(
			
				SELECT COUNT(comment_id)
				FROM `comment`
				WHERE comment_page = news_page
				) AS news_comment
			');
		}		

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

		if ($having)
		{
			$query->having($having);
		}

		return $query;
	}
	
	public function getTopNews($host, $limit, $count)
	{
		$time = time();
		
		$query = $this->fetch(null, 'news_sponsored = 1 DESC, score DESC', $limit, $count);
		$query->select('
			@vote := (
				SELECT MAX(vote_time)
				FROM news_vote
				WHERE vote_news = news_id AND vote_value = 1
			) AS vote,
			
			news_score - ((' . time() . ' - page_edit_time) * 0.00001) + IF(@vote IS NULL, 0, (1 - (((' . time() . ' - @vote) * 0.0001) * 0.001))) AS score
		');
		
		if ($host)
		{
			$query->where('news_host = ?', $host);
		}
		
		$query->where('page_publish = 1 AND page_delete = 0');
		
		return $query->get();
	}
	
	private function getConditionFromMode($mode)
	{
		$where = null;
		
		switch ($mode)
		{
			case 24:
	
				$where = 'page_time > ' . (time() - 86400);
				break;
	
			case 7:
	
				$where = 'page_time > ' . (time() - 604800);
				break;
	
			case 30:
	
				$where = 'page_time > ' . (time() - 2592000);
				break;
	
			case 365:
	
				$where = 'page_time > ' . (time() - 31536000);
				break;
		}

		return $where;
	}
	
	public function getNews($host, $mode, $limit, $count)
	{
		$where = $this->getConditionFromMode($mode);
		
		$query = $this->fetch($where, 'news_rate DESC', $limit, $count);
		$query->select('
			(
				SELECT MAX(vote_time)
				FROM news_vote
				WHERE vote_news = news_id AND vote_value = 1
			) AS vote
		');
		
		if ($host)
		{
			$query->where('news_host = ?', $host);
		}
		$query->where('page_publish = 1 AND page_delete = 0');
		
		return $query->get()->fetchAll();	
	}
	
	public function getRecentNews($limit = 40)
	{
		$query = $this->select()->innerJoin('page', 'page_id = news_page')
								->innerJoin('location', 'location_page = page_id')
								->where('page_publish = 1')
								->order('page_edit_time DESC')
								->limit($limit)
								->get();
								
		return $query;
	}
	
	public function getFoundRows($host, $mode)
	{
		$query = $this->select('COUNT(*)');
		
		if ($host)
		{
			$query->where('news_host = ?', $this->db->quote($host));
		}
		if ($mode)
		{
			$query->where($this->getConditionFromMode($mode));
		}		
		
		$query->innerJoin('page', 'page_id = news_page');
		$query->where('page_publish = 1 AND page_delete = 0');
		$query = $query->get();
		
		return $query->fetchField('COUNT(*)');
	}
	
	public function getNextId()
	{
		$sql = 'SHOW TABLE STATUS LIKE ?';
		$query = $this->db->query($sql, $this->name);

		return $query->fetchField('Auto_increment');
	}
}
?>