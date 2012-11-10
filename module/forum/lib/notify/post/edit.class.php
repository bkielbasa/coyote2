<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Notify_Post_Edit extends Notify_Abstract implements Notify_Interface
{
	private $postId;
	private $subject;
	/**
	 * Nowa nazwa (tytul) watku
	 */
	private $newSubject;
	private $content;
	private $enableDiff;
	private $oldContent;
	private $newContent;
	
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
	
	public function setNewSubject($subject)
	{
		$this->newSubject = $subject;
		return $this;
	}
	
	public function getNewSubject()
	{
		return $this->newSubject;
	}
	
	public function setEnableDiff($flag)
	{
		$this->enableDiff = (bool) $flag;
		return $this;
	}
	
	public function isDiffEnabled()
	{
		return $this->enableDiff;
	}
	
	public function setOldContent($content)
	{
		$this->oldContent = $content;
		return $this;
	}
	
	public function getOldContent()
	{
		return $this->oldContent;
	}
	
	public function setNewContent($content)
	{
		$this->newContent = $content;
		return $this;
	}
	
	public function getNewContent()
	{
		return $this->newContent;
	}
	
	public function getContent()
	{
		if ($this->isDiffEnabled() && $this->getOldContent() && $this->getNewContent())
		{
			Load::loadFile('lib/diff/Diff.php');
			Load::loadFile('lib/diff/Diff/Renderer.php');
			Load::loadFile('lib/diff/Diff/Renderer/unified.php');
			Load::loadFile('lib/diff/Diff/Renderer/inline.php');		
	
			$diff = new Text_Diff('auto', array(explode("\n", $this->getOldContent()), explode("\n", $this->getNewContent())));

			if ($this->getEmailFormat() == self::HTML)
			{
				$renderer = new Text_Diff_Renderer_inline();
				$output = $renderer->render($diff);		

				$this->content = '<pre>' . wordwrap($output, 85) . '</pre>';
			}
			else
			{
				$renderer = new Text_Diff_Renderer_unified;
				$output = $renderer->render($diff);
				
				$this->content = $output;
			}
		}

		return $this->content;
	}	
}
?>