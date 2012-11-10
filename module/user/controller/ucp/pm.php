<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Pm_Controller extends Controller
{
	function __start()
	{
		if (User::$id == User::ANONYMOUS)
		{
			$this->redirect(Path::connector('login') . '?redirect=' . url('@user?controller=Pm'));
		}
	}

	public function getMessagesCount()
	{
		$pm = &$this->getModel('pm');

		$result = array(
			Pm_Model::INBOX => $pm->select('COUNT(*)')->where('pm_to = ' . User::$id . ' AND pm_folder = ' . Pm_Model::INBOX)->fetchField('COUNT(*)')
		);
		return $result;
	}

	function main()
	{
		Breadcrumb::add(url('@user'), 'Panel użytkownika');
		Breadcrumb::add('', 'Wiadomości prywatne');

		$pm = &$this->getModel('pm');
		$this->folder = Pm_Model::INBOX;

		if ($this->input->isPost())
		{
			$this->post->setFilters('int');
			if ($delete = $this->post->delete)
			{
				$pm->deleteThread($this->post->delete);

				$this->session->message = 'Zaznaczone wiadomości zostały usunięte';
				$this->redirect('@user?controller=Pm');
			}
		}

		$totalItems = $pm->getUserMessagesCount(User::$id);
		$this->pm = $pm->getUserMessages(User::$id, (int)$this->get->start, 25);

		$this->unreadCount = array();

		if (User::data('pm_unread'))
		{
			$this->unreadCount = $pm->getUnreadMessagesCount(User::$id);
		}

		$this->pagination = new Pagination('', $totalItems, 25, (int)$this->get->start);
		$this->count = $this->getMessagesCount();

		return true;
	}

	public function submit()
	{
		$userId = (int)$this->get['user'];
		$this->subject = $this->text = $this->to = '';

		Breadcrumb::add(url('@user'), 'Panel użytkownika');
		Breadcrumb::add(url('@user?controller=Pm'), 'Wiadomości prywatne');
		Breadcrumb::add('', 'Napisz wiadomość');

		$pm = &$this->getModel('pm');
		$this->filter = new Filter_Input;

		if ($userId)
		{
			$user = &$this->getModel('user');
			if (!$this->to = $user->find($userId)->fetchField('user_name'))
			{
				$this->to = '';
			}
		}
		else
		{
			$this->to = $this->post['to'];
		}

		if ($this->input->isPost())
		{
			Load::loadFile('lib/validate.class.php', false);

			$validateUser = new Validate_User;
			$validateUser->setEnableAnonymous(false);
			$validateUser->addDisallow(User::data('name'));

			$data['validator'] = array(

				'to'					=> array(
													array('notempty'),
													$validateUser
										),
				'text'                  => array(


													array('notempty')
				)
			);
			$data['filter'] = array(

				'subject'				=> array('trim', 'htmlspecialchars'),
				'to'					=> array('trim', 'strip_tags', new Filter_Replace('<>"\'')),
				'text'                  => array('trim')
			);
			$this->filter->setRules($data);

			$values = array('text' => $this->post->text, 'to' => $this->to);
			if ($this->filter->isValid($values))
			{
				$user = &$this->getModel('user');
				$result = $user->getByName($this->to)->fetchAssoc();

				$id = $pm->submit($result['user_id'], (string) $this->post->subject, (string) $this->post->text, $this->post->trunk);
				$this->session->message = 'Wiadomość została wysłana';

				$this->redirect(url('@user?controller=Pm&action=View&id=' . $id) . '#pm' . $id);
			}
		}

		$this->count = $this->getMessagesCount();
		$this->folder = -1;

		return true;
	}

	public function view()
	{
		Breadcrumb::add(url('@user'), 'Panel użytkownika');
		Breadcrumb::add(url('@user?controller=Pm'), 'Wiadomości prywatne');

		$id = (int)$this->get['id'];
		$pm = &$this->getModel('pm');

		$this->pmId = $id;

		if (!$result = $pm->find($id)->fetchAssoc())
		{
			throw new Error(404, 'Brak wiadomości o tym ID!');
		}

		if ($result['pm_folder'] == Pm_Model::INBOX)
		{
			if ($result['pm_to'] != User::$id)
			{
				throw new Error(403);
			}

			$nextId = $result['pm_from'];
		}
		elseif ($result['pm_folder'] == Pm_Model::SENTBOX)
		{
			if ($result['pm_from'] != User::$id)
			{
				throw new Error(403);
			}

			$nextId = $result['pm_to'];
		}

		$this->count = $this->getMessagesCount();
		$this->folder = $result['pm_folder'];

		$parser = &$this->getLibrary('parser');

		if (class_exists('Parser_Forum'))
		{
			$parser->addParser(new Parser_Forum);
		}
		$parser->addParser(new Parser_Quote);
		$parser->addParser(new Parser_Html);
		$parser->addParser(new Parser_Wiki);
		$parser->addParser(new Parser_Url);
		$parser->addParser(new Parser_Censore);
		$parser->addParser(new Parser_Highlight);
		$parser->addParser(new Parser_Br);

		$parser->setOption('wiki.disableTemplate', true);
		$parser->setOption('wiki.disableTypography', true);
		$parser->setOption('tex.url', 'http://4programmers.net/cgi-bin/mimetex2.cgi');

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
				'tex',

				'span' => array('style'),
				'p',
				'br'
		);
		$parser->setOption('html.allowTags', $allowedTags);
		$notify = &$this->getModel('notify');

		$result = $pm->getConversation(User::$id, $this->nextId = $nextId);

		foreach ($result as $index => $row)
		{
			if (!$row['pm_read'] && $row['pm_folder'] == Pm_Model::INBOX)
			{
				$row['folding'] = false;

				$pm->update(array('pm_read' => time()), 'pm_text = ' . $row['pm_text'] . ' AND pm_from = ' . $row['pm_from'] . ' AND pm_to = ' . $row['pm_to']);
				User::$data['user_pm_unread'] = User::data('pm_unread') - 1;

				/*
				 * Jezeli uzytkownik ma jakies nieprzeczytane powiadomienia, to sprawdzamy,
				 * czy dany URL nie pasuje przypadkiem do powiadomienia ktore jest nieprzeczytane
				 */
				if (User::data('notify_unread'))
				{
					$notify->header->setReadByUrl('*User/Pm/View?id=' . $id . '#pm' . $id . '*');
				}
			}
			elseif ($row['pm_id'] == $id)
			{
				$row['folding'] = false;
			}
			else
			{
				$row['folding'] = true;
			}

			$row['pm_snippet'] = Text::limitHtml(Text::plain($row['pm_message'], false), 50);
			$row['pm_message'] = $parser->parse($row['pm_message']);
			$result[$index] = $row;
		}

		return View::getView('ucp/pmView', array('messages' => $result));
	}

	public function trash()
	{
		$id = (int)$this->get['id'];
		$pm = &$this->getModel('pm');

		if (!$result = $pm->find($id)->fetchAssoc())
		{
			throw new Error(404, 'Brak wiadomości o tym ID!');
		}
		$pm->delete($id, $result['pm_folder']);
		$this->session->message = 'Wiadomość została usunięta';

		$this->redirect('@user?controller=Pm');
	}

	public function __username()
	{
		$userName = htmlspecialchars(trim($this->get['q']));
		$html = '';

		if ($userName)
		{
			$user = &$this->getModel('user');
			$query = $user->select('user_name')->where('user_id > ' . User::ANONYMOUS)->like('user_name', $userName . '%')->limit(10)->get();

			foreach ($query as $row)
			{
				$html .= Html::tag('li', true, array(), preg_replace("~^($userName)~i", '<b>\\1</b>', $row['user_name']));
			}
		}

		echo $html;
		exit;
	}
}
?>