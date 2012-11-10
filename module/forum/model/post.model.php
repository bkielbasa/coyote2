<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Post_Accept_Model extends Model
{
	protected $name = 'post_accept';
	protected $prefix = 'accept_';

	public function getUserAcceptedPosts($userId, $offset = 0, $limit = 20)
	{
		$sql = "SELECT post_id,
					   post_time,
					   page_subject,
					   location_text,
					   accept_time,
					   accept_user,
					   user_name
				FROM post
				INNER JOIN post_accept ON accept_post = post_id
				INNER JOIN topic ON topic_id = accept_topic
				INNER JOIN page ON page_id = topic_page
				INNER JOIN location ON location_page = page_id
				INNER JOIN user ON user_id = accept_user
				WHERE post_user = $userId
				ORDER BY post_id DESC
				LIMIT $offset, $limit";

		return $this->db->query($sql);
	}

	public function getUserTotalAcceptedPosts($userId)
	{
		return (int) $this->db->select('COUNT(*)')->from('post')->where('post_user = ' . $userId)->innerJoin('post_accept', 'accept_post = post_id')->fetchField('COUNT(*)');
	}
}

class Post_Solr_Model extends Model
{
	public function index($postsId)
	{
		if (Config::getItem('forum.solr.host') && class_exists('Solr', true))
		{
			$tag = &$this->getModel('tag');

			try
			{
				if (!is_array($postsId))
				{
					$postsId = array($postsId);
				}

				// proba polaczenia z serwerem solr. jezeli sie nie uda, nie wykonujemy dalszego kodu
				$solr = new Solr(Config::getItem('forum.solr.host'), Config::getItem('forum.solr.port'), Config::getItem('forum.solr.path'));

				$result = $this->db->select('post_id, post_username, post_user, user_name, post_ip, page_subject, post_topic, post_forum, post_time, post_edit_time, post_vote, location_text, text_content, topic_page, topic_first_post_id')
								    ->from('post')
								    ->innerjoin('post_text', 'text_id = post_text')
								    ->leftJoin('user', 'user_id = post_user')
								    ->leftJoin('topic', 'topic_id = post_topic')
								    ->leftJoin('page', 'page_id = topic_page')
								    ->leftJoin('location', 'location_page = page_id')
								    ->in('post_id', $postsId)
									->fetchAll();

				$comments = array();

				$post = &$this->getModel('post');
				foreach ($post->comment->getComments($postsId) as $row)
				{
					$comments[$row['comment_post']][] = $row;
				}

				$pageIds = array();
				foreach ($result as $row)
				{
					$pageIds[] = $row['topic_page'];
				}

				$tags = $tag->getTags($pageIds);

				foreach ($result as $row)
				{
					$groups = $this->db->select('group_id')->from('page_group')->where('page_id = ' . $row['topic_page'])->fetchCol();
					$commentId = $commentUser = $commentText = array();

					if (isset($comments[$row['post_id']]))
					{
						foreach ($comments[$row['post_id']] as $comment)
						{
							$commentId[] = $comment['comment_id'];
							$commentUser[] = $comment['comment_user'];
							$commentText[] = $comment['comment_text'];
						}
					}

					$document = Search_Document::create(array(

						'id'                => $row['post_id'],
						'topic_id'          => $row['post_topic'],
						'forum_id'          => $row['post_forum'],
						'user_id'           => $row['post_user'],
						'user_name'         => $row['post_username'],
						'subject'           => $row['page_subject'],
						'location'          => $row['location_text'],
						'text'              => htmlspecialchars($row['text_content']),
						'ip'                => $row['post_ip'],
						'timestamp'         => max($row['post_time'], $row['post_edit_time']),
						'group'             => $groups,
						'tag'               => isset($tags[$row['topic_page']]) ? $tags[$row['topic_page']] : array(),
						'username'          => $row['post_username'],
						'first_post'        => $row['post_id'] == $row['topic_first_post_id'],
						//'vote'              => $row['post_vote'],

						'comment_id'        => $commentId,
						'comment_user'      => $commentUser,
						'comment_text'      => $commentText
					));

					$solr->addDocument($document);
				}

				$solr->commit();
			}
			catch (Exception $e)
			{
				Log::add('Błąd podczas indeksacji postu: ' . $e->getMessage(), E_ERROR);
			}
		}
	}

	public function indexByTopic($topicId)
	{
		$postIds = $this->db->select('post_id')->from('post')->where('post_topic = ?', $topicId)->fetchCol('post_id');
		$this->index((array) $postIds);
	}

	public function delete($postsId)
	{
		if (!is_array($postsId))
		{
			$postsId = array($postsId);
		}

		if (Config::getItem('forum.solr.host') && class_exists('Solr', true))
		{
			try
			{
				$solr = new Solr(Config::getItem('forum.solr.host'), Config::getItem('forum.solr.port'), Config::getItem('forum.solr.path'));

				foreach ($postsId as $postId)
				{
					$solr->deleteById($postId);
				}

				$solr->commit();
			}
			catch (Exception $e)
			{
				Log::add('Błąd podczas usuwania postu: ' . $e->getMessage(), E_ERROR);
			}
		}
	}

	public function deleteByTopic($topicId)
	{
		if (Config::getItem('forum.solr.host') && class_exists('Solr', true))
		{
			try
			{
				$solr = new Solr(Config::getItem('forum.solr.host'), Config::getItem('forum.solr.port'), Config::getItem('forum.solr.path'));

				$result = $this->db->select('post_id')->from('post')->where('post_topic = ?', $topicId)->fetchCol();
				foreach ($result as $postId)
				{
					$solr->deleteById($postId);
				}

				$solr->commit();
			}
			catch (Exception $e)
			{
				Log::add('Błąd podczas usuwania wątku: ' . $e->getMessage(), E_ERROR);
			}
		}
	}
}

class Post_Subscribe_Model extends Model
{
	protected $name = 'post_subscribe';

	public function subscribe($postId, $userId)
	{
		$sql = 'INSERT IGNORE INTO post_subscribe (post_id, user_id) VALUES(' . $postId . ', ' . $userId . ')';
		$this->db->query($sql);
	}

	public function unsubscribe($postId, $userId)
	{
		$this->delete('post_id = ' . $postId . ' AND user_id = ' . $userId);
	}

	public function toggle($postId, $userId)
	{
		$query = $this->select('post_id')->where('post_id = ' . $postId . ' AND user_id = ' . $userId)->get();

		if (count($query))
		{
			$this->unsubscribe($postId, $userId);
		}
		else
		{
			$this->subscribe($postId, $userId);
		}
	}

	public function getSubscribers($postId)
	{
		return (array) $this->select('user_id')->where('post_id = ' . $postId)->fetchCol();
	}

	public function isSubscribe($postId, $userId)
	{
		return (bool) count($this->select('post_id')->where('post_id = ' . $postId . ' AND user_id = ' . $userId)->get());
	}
}

class Post_Comment_Model extends Model
{
	protected $name = 'post_comment';
	protected $primary = 'comment_id';

	public function getComments($postIds)
	{
		if (!is_array($postIds))
		{
			$postIds = array($postIds);
		}

		return $this->select('post_comment.*, user_name, user_photo')->in('comment_post', $postIds)->leftJoin('user', 'user_id = comment_user')->get();
	}
}

class Post_Text_Model extends Model
{
	protected $name = 'post_text';
	protected $primary = 'text_id';

	protected $reference = array(

		'user'						=> array(

					'table'					=> 'user',
					'col'					=> 'user_id',
					'refCol'				=> 'text_user'
		)
	);

	public function fetchAll($postId)
	{
		return $this->fetch("text_post = $postId", 'text_id')->fetchAll();
	}

	public function submit($postId, &$content, $host = null)
	{
		if ($host === null)
		{
			$host = array(gethostbyaddr($this->input->getIp()));

			if ($this->input->server('HTTP_X_FORWARDED_FOR'))
			{
				array_push($host, $this->input->server('HTTP_X_FORWARDED_FOR'));
			}

			$host = implode(' ', $host);
		}

		$this->insert(array(
			'text_post'				=> $postId,
			'text_time'				=> time(),
			'text_user'				=> User::$id,
			'text_ip'				=> $this->input->getIp(),
			'text_host'				=> (string) $host,
			'text_browser'			=> $this->input->getUserAgent(),
			'text_content'			=> (string) $content
			)
		);
	}
}

class Post_Attachment_Model extends Model
{
	protected $name = 'post_attachment';
	protected $prefix = 'attachment_';
	protected $primary = 'attachment_id';

	private function getMimeType($path)
	{
		$mimeType = '';

		if (function_exists('finfo_open'))
		{
			$info = finfo_open(FILEINFO_MIME_TYPE);
			$mimeType = finfo_file($info, Config::getBasePath() . $path);
			finfo_close($info);
		}
		elseif (function_exists('mime_content_type'))
		{
			$mimeType = mime_content_type(Config::getBasePath() . $path);
		}

		return $mimeType;
	}

	public function insert($postId, array $data)
	{
		$sql = array();
		$image = &$this->load->library('image');

		if (count($data) > 15)
		{
			$data = array_slice($data, 0, 15, true);
		}

		foreach ($data as $id => $fileName)
		{
			$width = $height = 0;

			if (file_exists("tmp/$id"))
			{
				$suffix = pathinfo($id, PATHINFO_EXTENSION);
				if (in_array($suffix, array('jpg', 'jpeg', 'gif', 'png')))
				{
					$image->open("tmp/$id");

					$width =(int) $image->getWidth();
					$height = (int) $image->getHeight();
					$image->close();
				}

				$sql[] = array(
					'attachment_post'			=> $postId,
					'attachment_name'			=> $fileName,
					'attachment_file'			=> $id,
					'attachment_size'			=> filesize("tmp/$id"),
					'attachment_mime'			=> $this->getMimeType("tmp/$id"),
					'attachment_time'			=> time(),
					'attachment_width'			=> $width,
					'attachment_height'			=> $height
				);

				if (!is_writeable('store/forum'))
				{
					@mkdir('store/forum', 0777);
				}
				@rename("tmp/$id", "store/forum/$id");
			}
		}

		if ($sql)
		{
			$this->db->multiInsert($this->name, $sql);
		}
	}

	public function delete($postId, $filesList)
	{
		if (!is_array($filesList))
		{
			$filesList = array($filesList);
		}
		$fileList = array_map('htmlspecialchars', $filesList);
		$query = $this->getByFile($fileList);

		$ids = $items = array();

		foreach ($query->fetchAll() as $row)
		{
			if ($row['attachment_post'] == $postId)
			{
				$ids[] = $row['attachment_file'];
				$items[] = '"' . $row['attachment_file'] . '"';
			}
		}

		foreach (array_intersect($ids, $fileList) as $element)
		{
			@unlink("store/forum/$element");

			foreach (glob("store/forum/*-$element") as $fileName)
			{
				@unlink("store/forum/$fileName");
			}
		}

		foreach (array_diff($fileList, $ids) as $element)
		{
			@unlink("tmp/$element");
		}

		if ($items)
		{
			$this->db->delete($this->name, 'attachment_file IN(' . implode(',', $items) . ')');
		}
	}

	public function recive()
	{
		$json = '';

		try
		{
			$upload = &$this->load->library('upload');
			$upload->setDestination('tmp/');
			$upload->setOverwrite(true);

			Load::loadFile('lib/validate.class.php');
			$validate = new Validate_Upload(false, Config::getItem('attachment.limit', '10MB'), Config::getItem('attachment.suffix', 'jpg,gif,jpeg,png'));
			if (!$validate->isValid('attachment'))
			{
				throw new Exception(implode("\n", $validate->getMessages()));
			}

			if ($upload->recive('attachment'))
			{
				$fileName = uniqid(mt_rand()) . '.' . $upload->getSuffix();
				rename('tmp/' . $upload->getFileName(), 'tmp/' . $fileName);

				if (in_array($upload->getSuffix(), array('jpg', 'jpeg', 'gif', 'png')))
				{
					$image = &$this->load->library('image');
					$image->open("tmp/" . $fileName);

					$width =(int) $image->getWidth();
					$height = (int) $image->getHeight();
					$image->close();

					if (!$width || !$height)
					{
						throw new Exception('Wygląda na to, że załącznik nie jest prawidłowym obrazem');
					}
				}

				if (!$upload->getFileSize())
				{
					throw new Exception('Wygłada na to, że plik, który próbujesz dodać jest pusty');
				}

				$result = array(
					'size'			=> $upload->getFileSize(),
					'suffix'		=> $upload->getSuffix(),
					'name'			=> addslashes($upload->getFileName()),
					'uniqid'		=> $fileName,
					'mime'			=> $this->getMimeType('tmp/' . $fileName),
					'time'			=> User::formatDate(time())
				);
			}

			$json = json_encode($result);
		}
		catch (Exception $e)
		{
			Log::add($e->getMessage(), E_ERROR);

			$json = json_encode(array(
				'error'		=> $e->getMessage()
				)
			);
		}

		return $json;
	}

	public function paste(&$data)
	{
		try
		{
			if (strlen($data) > 2997152)
			{
				throw new Exception('Rozmiar pliku jest zbyt duży');
			}
			$fileName = uniqid(mt_rand()) . '.png';
			file_put_contents('tmp/' . $fileName, file_get_contents('data://' . substr($data, 7)));

			$json = json_encode(array(
				'size'			=> filesize('tmp/' . $fileName),
				'suffix'		=> 'png',
				'name'			=> addslashes($fileName),
				'uniqid'		=> $fileName,
				'mime'			=> File::mime(Config::getBasePath() . 'tmp/' . $fileName),
				'time'			=> User::formatDate(time())
			));
		}
		catch (Exception $e)
		{
			Log::add($e->getMessage(), E_ERROR);

			$json = json_encode(array(
				'error'		=> $e->getMessage()
				)
			);
		}

		return $json;
	}

	public function getAsJson($postId)
	{
		$query = $this->getByPost($postId);
		$result = array();

		foreach ($query as $row)
		{
			$result[] = array(
				'name'				=> $row['attachment_name'],
				'mime'				=> $row['attachment_mime'],
				'time'				=> Text::fileSize($row['attachment_size']),
				'size'				=> User::formatDate($row['attachment_time'])
			);
		}

		return json_encode($result);
	}

	public function getAttachments($postIds)
	{
		if (!is_array($postIds))
		{
			$postIds = array($postIds);
		}

		$query = $this->select()->in('attachment_post', (array) $postIds)->get();
		$result = array();

		foreach ($query as $row)
		{
			$result[$row['attachment_post']][] = $row;
		}

		return $result;
	}
}

class Post_Vote_Model extends Model
{
	const UP			=		1;
	const DOWN			=		-1;

	protected $name = 'post_vote';

	public function update($postId, $value)
	{
		$query = $this->select('vote_value')->where("vote_post = $postId AND vote_user = " . User::$id)->get();
		/**
		 * Jezeli ten warunek jest prawidziwy, to oznacza, to, ze user oddal juz
		 * glos na ten post...
		 */
		if (count($query))
		{
			$currValue = $query->fetchField('vote_value');

			/**
			 * Jezeli user ponownie oddaje IDENTYCZNY glos na dany post,
			 * oznacza to, ze chce cofnac te operacje
			 */
			if ($currValue == $value)
			{
				$this->delete("vote_post = $postId AND vote_user = " . User::$id);
			}
			else
			{
				parent::update(array('vote_value' => $value, 'vote_time' => time(), 'vote_ip' => User::$ip), "vote_post = $postId AND vote_user = " . User::$id);
			}
		}
		else
		{
			$this->insert(array(
				'vote_post'			=> $postId,
				'vote_user'			=> User::$id,
				'vote_value'		=> $value,
				'vote_time'         => time(),
				'vote_ip'           => User::$ip
				)
			);
		}

		return (int) $this->db->select('post_vote')->from('post')->where("post_id = $postId")->fetchField('post_vote');
	}

	public function getUserRecievedVotes($userId = null, $offset = 0, $limit = 20)
	{
		if (!$userId)
		{
			$userId = User::$id;
		}

		$sql = "SELECT p.post_id,
					   p.post_time,
					   pp.page_subject,
					   l.location_text,
					   v.vote_value AS value,
					   v.vote_time
				FROM post p
				INNER JOIN post_vote v ON v.vote_post = p.post_id
				INNER JOIN topic t ON t.topic_id = p.post_topic
				INNER JOIN page pp ON pp.page_id = t.topic_page
				INNER JOIN location l ON l.location_page = pp.page_id
				WHERE p.post_user = $userId
				ORDER BY p.post_id DESC
				LIMIT $offset, $limit";

		return $this->db->query($sql);
	}

	public function getUserTotalRecievedVotes($userId = null)
	{
		if (!$userId)
		{
			$userId = User::$id;
		}

		$sql = "SELECT COUNT(*) FROM post p INNER JOIN post_vote v ON v.vote_post = p.post_id WHERE p.post_user = $userId";
		return $this->db->query($sql)->fetchField('COUNT(*)');
	}
}

class Post_Model extends Model
{
	protected $name = 'post';
	protected $prefix = 'post_';
	protected $primary = 'post_id';

	protected $reference = array(

			'user'					=> array(

							'table'					=> 'user',
							'col'					=> 'user_id',
							'refCol'				=> 'post_user'
			)
	);

	public $attachment;
	public $vote;
	public $comment;
	public $text;
	public $subscribe;
	public $solr;
	public $accept;

	function __construct()
	{
		$this->attachment = new Post_Attachment_Model;
		$this->vote = new Post_Vote_Model;
		$this->comment = new Post_Comment_Model;
		$this->text = new Post_Text_Model;
		$this->subscribe = new Post_Subscribe_Model;
		$this->solr = new Post_Solr_Model;
		$this->accept = new Post_Accept_Model;
	}

	public function submit($forumId, $topicId, $content, $postUsername = '', $postEnableSmilies = false, $postEnableHtml = false)
	{
		$time = time();

		$host = array(gethostbyaddr($this->input->getIp()));

		if ($this->input->server('HTTP_X_FORWARDED_FOR'))
		{
			array_push($host, $this->input->server('HTTP_X_FORWARDED_FOR'));
		}

		$this->db->insert('post', array(
			'post_forum'			=> $forumId,
			'post_topic'			=> $topicId,
			'post_user'				=> User::$id,
			'post_time'				=> $time,
			'post_username'			=> (string) $postUsername,
			'post_enable_smilies'	=> (bool) $postEnableSmilies,
			'post_enable_html'		=> (bool) $postEnableHtml,
			'post_ip'				=> $this->input->getIp(),
			'post_host'				=> implode(', ', $host),
			'post_browser'			=> $this->input->getUserAgent(),
			)
		);
		$postId = $this->db->nextId();
		$this->text->submit($postId, $content, implode(', ', $host));

		$this->solr->index($postId);

		return $postId;
	}

	/**
	 * Metoda powoduje uaktualnienie tresci postu
	 *
	 * @param $postId               ID istniejacego juz postu
	 * @param $content              Nowa tresc postu
	 * @param string $postUsername  Nowa nazwa uzytkownika anonimowego
	 * @param bool $postEnableSmilies   Ustawia wyswietlanie emotek w poscie
	 * @param bool $postEnableHtml      Wlacza lub wylacza parsowanie HTML w postach
	 * @param int $postEditCount        Ilosc edycji postu
	 * @param bool $logToHistory        Domyslnie nowa tresc jest zapisana w historii wraz z poprzednimi edycjami postu
	 */
	public function update($postId, $content, $postUsername = '', $postEnableSmilies = false, $postEnableHtml = false, $postEditCount = 0, $logToHistory = true)
	{
		if (is_array($postId))
		{
			parent::update($postId, $content); // jezeli pierwszym parametrem jest tablica, to znaczy, ze ktos chce wykonac update na tabeli, nie podajac pozostalych parametrow
		}
		else
		{
			parent::update(array(
					'post_edit_time'		=> time(),
					'post_edit_user'		=> User::$id,
					'post_edit_count'		=> $postEditCount,
					'post_username'			=> $postUsername,
					'post_enable_smilies'	=> (bool) $postEnableSmilies,
					'post_enable_html'		=> (bool) $postEnableHtml
				),

				"post_id = $postId"
			);

			if ($logToHistory)
			{
				$this->text->submit($postId, $content);
			}

			$this->solr->index($postId);
		}
	}

	public function fetch($where = null, $order = null, $limit = null, $count = null)
	{
		$query = $this->db->select('post.post_id,
									post_user,
									post_username,
									post_time,
									post_edit_time,
									post_edit_user,
									post_edit_count,
									post_enable_smilies,
									post_enable_html,
									post_vote,
									text_content AS post_text,
									post_ip,
									post_host,
									post_browser,
									u1.*,
									u2.user_name AS edit_user_name,
									group_name'
									)->from('post');

		$query->innerJoin('post_text', 'text_id = post_text');

		$query->leftJoin('user u1', 'u1.user_id = post_user');
		$query->leftJoin('user u2', 'u2.user_id = post_edit_user');
		$query->leftJoin('`group`', 'group_id = u1.user_group');

		if (User::$id > User::ANONYMOUS)
		{
			$query->select('v.vote_value AS value, ps.user_id AS subscribe');
			$query->leftJoin('post_vote v', 'v.vote_post = post.post_id AND v.vote_user = ' . User::$id);
			$query->leftJoin('post_subscribe ps', 'ps.post_id = post.post_id AND ps.user_id = ' . User::$id);
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

		return $query;
	}

	public function getPost($postId, $topicId = null)
	{
		$query = $this->select('post_id,
								post_user,
								post_username,
								post_time,
								post_topic,
								post_forum,
								-- post_edit_time,
								-- post_edit_user,
								post_edit_count,
								post_enable_smilies,
								post_enable_html,
								post_vote,
								text_content AS post_text,
								text_user AS post_edit_user,
								text_time AS post_edit_time,
								post_ip,
								post_host,
								post_browser'
								);

		$query->innerJoin('post_text', 'text_id = post_text');
		$query->where('post_id = ?', $postId);

		if ($topicId != null)
		{
			$query->where('post_topic = ?', $topicId);
		}

		return $query;
	}

	public function getTopicPosts($topicId, $topicFirstPostId, $order, $limit, $count)
	{
		$result = array();

		$result[] = $this->fetch("post.post_id = $topicFirstPostId")->fetchAssoc();
		$result = array_merge($result, $this->fetch("post_topic = $topicId AND post.post_id != $topicFirstPostId", $order, $limit, $count)->fetchAll());

		return $result;
	}

	public function count($topicId)
	{
		return (int)$this->select('COUNT(*)')->where("post_topic = $topicId")->get()->fetchField('COUNT(*)');
	}

	public function getPage($topicId, $postId, $limit = 10)
	{
		$sql = "SELECT COUNT(post_id)
				FROM post
				WHERE post_topic = $topicId
					AND post_id < $postId
						AND $postId IN(

							SELECT post_id
							FROM post
							WHERE post_topic = $topicId
						)";
		$page = max(0, floor(($this->db->query($sql)->fetchField('COUNT(post_id)') -1) / $limit) * $limit);

		return $page;
	}

	public function isUnread($forumId, $markTime = null)
	{
		$unread = false;

		if (User::$id > User::ANONYMOUS)
		{
			$sql = 'SELECT COUNT(*)
					FROM topic t
					LEFT JOIN topic_marking m ON (m.topic_id = t.topic_id AND m.user_id = ' . User::$id . ")
					WHERE t.topic_forum = $forumId
							" . ($markTime != null ? "AND t.topic_last_post_time > $markTime" : '') .
							" AND m.topic_id IS NULL";

			$unread = (bool) $this->db->query($sql)->fetchField('COUNT(*)');
		}
		else
		{
			$tracking = unserialize($this->input->cookie('tracking'));

			$query = $this->db->select('topic_id')->where("topic_forum = $forumId")->from('topic');
			if ($markTime != null)
			{
				$query->where("topic_last_post_time > $markTime");
			}

			foreach ($query->fetchAll() as $row)
			{
				if (!isset($tracking['t'][base_convert($row['topic_id'], 10, 36)]))
				{
					$unread = true;
					break;
				}
			}
		}

		return $unread;
	}

	public function isFlood($limit = 10)
	{
		$query = $this->db->select('post_id')->from('post')->where('post_time > ' . (time() - $limit));

		if (User::$id > User::ANONYMOUS)
		{
			$query->where('post_user = ' . User::$id);
		}
		else
		{
			$query->where('post_ip = ?', $this->input->getIp());
		}

		return (bool) count($query->get());
	}

	public function getPreviousPost($postId)
	{
		$query = $this->db->select('p2.*')
					  ->from('post p1')
					  ->where("p1.post_id = $postId")
					  ->innerJoin('post p2', 'p2.post_topic = p1.post_topic AND p2.post_id < p1.post_id')
					  ->order('p2.post_id DESC')
					  ->limit(1);

		return $query->fetchAssoc();
	}

	public function merge($postId, $toId)
	{
		$toContent = $this->text->select('text_content')->where('text_post = ' . $toId)->order('text_id DESC')->limit(1)->fetchField('text_content');
		$postContent = $this->text->select('text_content')->where('text_post = ' . $postId)->order('text_id DESC')->limit(1)->fetchField('text_content');

		$content = ($toContent . "\n\n" . $postContent);
		$this->text->submit($toId, $content);

		$this->db->update('post_attachment', array('attachment_post' => $toId), "attachment_post = $postId");
		$this->db->update('post_comment', array('comment_post' => $toId), "comment_post = $postId");
		/**
		 * @todo Jezeli user oddal glos na post ktory jest laczony oraz ten, do ktorego
		 * jest przylaczany, to w tabeli post_vote zajdzie sie zdublowany wpis
		 */
		$this->db->update('post_vote', array('vote_post' => $toId), "vote_post = $postId");

		$this->solr->delete($postId); // usuniecie zindeksowanych postow z solr-a
		$this->db->delete('post', 'post_id = ' . $postId);

		$this->solr->index($toId);
	}

	public function delete($ids)
	{
		if (!is_array($ids))
		{
			$ids = array($ids);
		}

		$query = $this->db->select('attachment_file')->in('attachment_post', $ids)->get('post_attachment');
		foreach ($query as $row)
		{
			@unlink('store/forum/' . $row['attachment_file']);
		}

		$this->solr->delete($ids); // usuniecie zindeksowanych postow z solr-a
		$this->db->delete('post', 'post_id IN(' . implode(',', $ids) . ')');
	}

	/**
	 * Zwraca TRUE jezeli user napisal jakikolwiek post w temacie oznaczonym przez $topicId.
	 * @param int $topicId
	 * @param bool
	 * @todo Lepsza (jasniejsza) nazwa metody?
	 */
	public function hasUserPost($topicId)
	{
		$query = $this->db->select('post_id')
						  ->from('post')
						  ->where('post_topic = ?', $topicId)
						  ->where('post_user = ?', User::$id)
						  ->limit(1)
						  ->get();

		return (bool) count($query);
	}

	/**
	 * Metoda zwraca informacje o cytowanych postach. W parametrze nalezy przekazac tresc postu z ktorego pobrane zostana ID cytowanych postow.
	 * Mozliwe jest rowniez przekazanie tablicy ID cytowanych postow.
	 * W odpowiedzi dostaniemy tablice z informacja o autorze postu, wraz ze sciezka do watku oraz data napisania postu
	 *
	 * @param string|array          Tresc postu
	 * @return array                Informacje na temat autora posta czy daty napisania
	 */
	public function getQuotedPost(&$data)
	{
		$postIds = array();

		if (is_string($data))
		{
			// sprawdzenie, czy w poscie znajduje sie cytat z innego postu
			if (preg_match_all("#<quote=\"(\d+)\"\>#is", $data, $matches))
			{
				for ($i = 0; $i < count($matches[0]); $i++)
				{
					$postIds[] = (int) $matches[1][$i];
				}
			}
		}
		elseif (is_array($data))
		{
			$postIds = $data;
		}

		$postIds = array_map('intval', array_unique($postIds));

		$result = array();
		if ($postIds)
		{
			$query = $this->db->select('post_id, post_time, IF(post_user > 0, u1.user_name, post_username) AS post_user, location_text')
							  ->from('post')
							  ->leftJoin('user u1', 'u1.user_id = post_user')
							  ->innerJoin('topic', 'topic_id = post_topic')
							  ->innerJoin('location', 'location_page = topic_page')
							  ->in('post_id', $postIds)
							  ->get();

			foreach ($query as $row)
			{
				$result[$row['post_id']] = $row;
			}
		}

		return $result;
	}
}
?>