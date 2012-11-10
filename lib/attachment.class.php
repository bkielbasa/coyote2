<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Attachment extends Context
{
	const STORE			=		'store/_aa/';

	protected $name;
	protected $id;
	protected $fileName;
	protected $fileSize;
	protected $mimeType;
	protected $time;
	protected $user;
	protected $width;
	protected $height;

	function __construct($id = 0)
	{
		if ($id)
		{
			$query = $this->db->select()->from('attachment')->where("attachment_id = $id")->get();

			if (count($query))
			{
				$result = $query->fetchAssoc();

				$this->name = $result['attachment_name'];
				$this->fileName = $result['attachment_file'];
				$this->id = $result['attachment_id'];
				$this->fileSize = $result['attachment_size'];
				$this->mimeType = $result['attachment_mime'];
				$this->user = $result['attachment_user'];
				$this->width = $result['attachment_width'];
				$this->height = $result['attachment_height'];
				$this->time = $result['attachment_time'];
			}
		}
	}

	public function getId()
	{
		return $this->id;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setFileName($fileName)
	{
		$this->fileName = $fileName;
		return $this;
	}

	public function getFileName()
	{
		return $this->fileName;
	}

	public function getMime()
	{
		return $this->mimeType;
	}

	public function getTime()
	{
		return $this->time;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function getWidth()
	{
		return $this->width;
	}

	public function getHeight()
	{
		return $this->height;
	}

	public function getFileSize()
	{
		return $this->fileSize;
	}

	public function getPath()
	{
		return self::STORE . $this->getFileName();
	}

	public function isImage()
	{
		return (bool)$this->width;
	}

	protected function getMimeType($path)
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

	public function insert(array $data = array())
	{
		if ($data)
		{
			foreach ($data as $key => $value)
			{
				$this->$key = $value;
			}
		}

		$this->mimeType = $this->getMimeType(self::STORE . $this->getFileName());
		$this->time = time();

		$this->fileSize = filesize(self::STORE . $this->getFileName());
		$this->width = $this->height = 0;

		$suffix = pathinfo(Text::toLower($this->getName()), PATHINFO_EXTENSION);
		if (in_array($suffix, array('jpg', 'jpeg', 'gif', 'png')))
		{
			$image = &$this->load->library('image');
			$image->open(self::STORE . $this->getFileName());

			$this->width = (int) $image->getWidth();
			$this->height = (int) $image->getHeight();
			$image->close();
		}

		$filter = new Filter_Replace('<>"\'');
		$this->setName($filter->filter($this->getName()));

		$attachment = &$this->load->model('attachment');
		$attachment->insert(array(
			'attachment_name'		=> $this->getName(),
			'attachment_file'		=> $this->getFileName(),
			'attachment_time'		=> $this->time,
			'attachment_user'		=> User::$id,
			'attachment_size'		=> $this->fileSize,
			'attachment_width'		=> $this->getWidth(),
			'attachment_height'		=> $this->getHeight(),
			'attachment_mime'		=> $this->mimeType
			)
		);
		$this->id = $this->db->nextId();
	}

	public function delete($id = 0, $permanently = false)
	{
		if (!$id)
		{
			$id = $this->id;
		}

		$attachment = &$this->load->model('attachment');
		$attachment->delete($id, $permanently);
	}
}
?>