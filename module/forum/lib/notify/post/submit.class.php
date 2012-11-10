<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Notify_Post_Submit extends Notify_Abstract implements Notify_Interface
{
	private $postId;
	private $subject;
	private $content;
	private $userName;
	private $enableSmilies;
	private $isAttachments;

	/**
	 * Jezeli post przejdzie proces parsowania, wartosc tego pola zmieniana jest
	 * na TRUE, aby uniknac ponownego parsowania
	 */
	private $isRender;

	public function setPostId($postId)
	{
		$this->postId = (int) $postId;
		return $this;
	}

	public function getPostId()
	{
		return $this->postId;
	}

	public function setSubject($subject)
	{
		$this->subject = (string) $subject;
		return $this;
	}

	public function getSubject()
	{
		return $this->subject;
	}

	public function setContent($content)
	{
		$this->content = (string) $content;
		return $this;
	}

	public function getContent()
	{
		if ($this->getEmailFormat() == self::HTML && !$this->isRender)
		{
			Forum::loadParsers(false, false);

			/**
			 * Pobranie i wyswietlenie zalacznikow tekscie maili
			 */
			if ($this->isAttachments())
			{
				$wikiAttachments = array();

				$query = $this->db->select()->where('attachment_post = ?', $this->getPostId())->get('post_attachment');
				foreach ($query as $row)
				{
					$wikiAttachments[Text::toLower($row['attachment_name'])] = $row;
				}

				$this->parser->setOption('wiki.attachment', $wikiAttachments);
			}

			$post = &$this->getModel('post');
			// pobranie informacji o cytowanych postach i przekazanie do parsera
			$this->parser->setOption('quote.postId', $post->getQuotedPost($this->content));

			$this->content = $this->parser->parse($this->content);
			$this->isRender = true;
		}

		return $this->content;
	}

	public function setUserName($userName)
	{
		$this->userName = (string) $userName;
		return $this;
	}

	public function getUserName()
	{
		return $this->userName;
	}

	public function setEnableSmilies($flag)
	{
		$this->enableSmilies = $flag;
		return $this;
	}

	public function isSmiliesEnabled()
	{
		return $this->enableSmilies;
	}

	public function setIsAttachments($flag)
	{
		$this->isAttachments = (bool) $flag;
		return $this;
	}

	public function isAttachments()
	{
		return $this->isAttachments;
	}
}
?>