<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Post_Controller extends Page_Controller
{
	private $postId;
	public $postData;

	public function __start()
	{
		$this->postId = (int) $this->router->id;

		$post = &$this->getModel('post');
		if (!$result = $post->find($this->postId)->fetchAssoc())
		{
			throw new UserErrorException('Post o podanym ID nie istnieje');
		}

		$this->postData = $result;

		$topic = &$this->getModel('topic');
		$pageId = $topic->select('topic_page')->where('topic_id = ' . $result['post_topic'])->fetchField('topic_page');

		$this->page = Page::load((int) $pageId);
		parent::__start();

		$forum = &$this->getModel('forum');
		$isEditable = $forum->getAuth('f_edit', $result['post_forum']);

		if (User::$id == User::ANONYMOUS || (User::$id != $result['post_user']
			&& !$isEditable))
		{
			throw new UserErrorException('Brak uprawnień do tej strony');
		}

		foreach ($this->getParents() as $row)
		{
			Breadcrumb::add(url($row['location_text']), $row['page_subject']);
		}
		Breadcrumb::add(url($this->page->getLocation()), $this->page->getSubject());
	}

	public function version()
	{
		Breadcrumb::add(url('@forum/Post/Version/' . $this->postId), 'Historia edycji postu');

		$this->getLibrary('parser');

		/*
		 * Domyslne dzialanie to usuniecie zbednych znacznikow HTML.
		 * Jest to podstawowe dzialanie zwiazane z kwestia bezpieczenstwa.
		 *
		 * Ten parser zawsze musi byc wywolywany jako pierwszy, aby pozostawic
		 * do dalszej obrobki tylko te znaczniki HTML ktore sa dozwolone do uzycia
		 */
		$this->parser->addParser(new Parser_Html);

		/*
		 * Parsowanie skladni Wiki
		 */
		$this->parser->addParser(new Parser_Wiki);

		/*
		 * Parser adresow URL. Glownie sluzy do tego, aby wyszukiwac ciagi mogace byc
		 * adresami URL i zamieniac je na "klikalne". Musi byc umieszczony ZA parserem
		 * Wiki poniewaz taka skladnia (''http://foo.com'') nie powinna zamieniac
		 * tekstu na znacznik <a>
		 *
		 * Parser ten nie dokonuje zmiany w wewnatrz znacznikow <code>, <tt>, <kbd>, <samp>
		 */
		$this->parser->addParser(new Parser_Url);

		/*
		 * Parser cenzurowania moze generowac kod HTML. Stad musi byc umiesczony ZA parserem HTML,
		 * ktory moglby taki kod HTML - usunac. Jak mozna zauwazyc, ze parser Censore jest umiesczony
		 * ZA parserami Wiki oraz Url z uwagi na to, ze tamte parsery moga wyprodukowac tagi
		 * <a>, <code> czy <kbd>, a wewnatrz tych znacznikow nie dokonujemy cenzury
		 */
		$this->parser->addParser(new Parser_Censore);

		/*
		 * Kolorowanie skladni. Ten parser koloruje skladnie pomiedzy
		 * znacznikami <code>
		 */
		$this->parser->addParser(new Parser_Highlight);
		$this->parser->addParser(new Parser_Forum);

		$this->parser->setOption('wiki.disableTemplate', true);
		$this->parser->setOption('wiki.disableTypography', true);
		$this->parser->setOption('wiki.disableHeadline', true);
		$this->parser->setOption('tex.url', 'http://4programmers.net/cgi-bin/mimetex2.cgi');

		$allowedTags = array(

				'a' => 'href',
				'b',
				'i',
				'u',
				'del',
				'strong',
				'tt',
				'dfn',
				'ins',
				'pre',
				'blockquote',
				'hr',
				'sub',
				'sup',
				'font' => array('size', 'color'), // deprecated
				'ort',
				'wiki' => 'href',
				'image',
				'img' => array('src', 'alt'),
				'email',
				'url' => '*',
				'quote' => '*',
				'code' => '*',
				'nobr',
				'plain',
				'tex'
		);
		$this->parser->setOption('html.allowTags', $allowedTags);

		Load::loadFile('lib/diff/Diff.php');
		Load::loadFile('lib/diff/Diff/Renderer.php');
		Load::loadFile('lib/diff/Diff/Renderer/unified.php');
		Load::loadFile('lib/diff/Diff/Renderer/inline.php');

		$post = &$this->getModel('post');
		$this->version = array();

		$result = array();

		foreach ($post->text->fetchAll($this->postId) as $row)
		{
			if (!isset($this->get->diff))
			{
				$row['text_content'] = $this->parser->parse($row['text_content']);
			}
			$result[] = $row;
		}

		$result = array_reverse($result);
		$count = count($result); // ilosc wersji danego postu

		$userIds = array();

		foreach ($result as $key => $row)
		{
			if ($row['text_user'] > User::ANONYMOUS)
			{
				$userIds[] = $row['text_user'];
			}

			$row['revision'] = $count - $key;

			if (isset($this->get->diff))
			{
				if (isset($result[$key + 1]))
				{
					$diff = new Text_Diff('auto', array(explode("\n", $result[$key + 1]['text_content']), explode("\n", $result[$key]['text_content'])));

					$renderer = new Text_Diff_Renderer_inline();
					$row['content'] = htmlspecialchars_decode($renderer->render($diff));
				}
			}

			$this->version[] = $row;
		}

		$this->onlineUsers = array();

		if ($userIds)
		{
			$this->onlineUsers = $this->db->select('session_user_id, session_ip')->from('session')->in('session_user_id', array_unique($userIds))->fetchPairs();
		}

		$this->parser->removeParsers();
		$this->parser->addParser(new Parser_Br);

		foreach ($this->version as $key => $row)
		{
			$row['content'] = $this->parser->parse($row['content']);
			$row['text_content'] = $this->parser->parse($row['text_content']);

			$this->version[$key] = $row;
		}

		$forum = &$this->getModel('forum');
		$this->isEditable = $forum->getAuth('f_edit', $this->page->getForumId());

		$view = new View('postVersion');
		$this->output->setTitle(sprintf('Historia edycji postu w wątku %s :: %s', ($this->page->getTitle() ? $this->page->getTitle() : $this->page->getSubject()), Config::getItem('site.title')));

		return $view;
	}

	public function source()
	{
		$textId = (int) $this->get->id;
		$post = &$this->getModel('post');

		$result = $post->text->find($textId)->fetchAssoc();

		if (!$result)
		{
			throw new UserErrorException('Wersja o tym ID nie istnieje!');
		}

		if ($result['text_post'] != $this->postId)
		{
			throw new UserErrorException('Nieprawidłowa wersja artykułu');
		}

		$this->output->setContentType('text/plain; charset=utf-8');
		echo $result['text_content'];

		exit;
	}

	public function revert()
	{
		$textId = (int) $this->get->id;
		$post = &$this->getModel('post');

		$result = $post->text->find($textId)->fetchAssoc();

		if (!$result)
		{
			throw new UserErrorException('Wersja o tym ID nie istnieje!');
		}

		if ($result['text_post'] != $this->postId)
		{
			throw new UserErrorException('Nieprawidłowa wersja artykułu');
		}

		if ($this->postData['post_text'] == $textId)
		{
			throw new UserErrorException('Dana wersja tekstu jest już aktualną wersją w tym poście');
		}

		$forum = &$this->getModel('forum');
		$isEditable = $forum->getAuth('f_edit', $this->postData['post_forum']);

		if (!$isEditable)
		{
			throw new UserErrorException('Brak uprawnień do wykonania tej operacji');
		}

		$this->db->update('post', array(
			'post_edit_time'		=> time(),
			'post_edit_user'		=> User::$id,
			'post_edit_count'		=> ++$this->postData['post_edit_count']
			),

			'post_id = ' . $this->postId
		);

		$this->session->message = 'Wersja postu została przywrócona';

		$post->text->submit($this->postId, $result['text_content']);
		$this->redirect(url('@forum/Post/Version/' . $this->postId));
	}
}
?>