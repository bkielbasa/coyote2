<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Notify_Controller extends Controller
{
	function __start()
	{
		if (User::$id == User::ANONYMOUS)
		{
			$this->redirect(Path::connector('login') . '?redirect=' . url('@user'));
		}
	}

	public function main()
	{
		Breadcrumb::add(url('@user'), 'Panel użytkownika');
		Breadcrumb::add(url('@user?controller=Notify'), 'Powiadomienia');

		$notify = &$this->getModel('notify');

		$this->notifications = $notify->fetchAll();
		$this->notifiers = $notify->user->getNotifiers(User::$id);

		if ($this->input->isPost())
		{
			$notifies = array();

			foreach ($this->post->notify as $id => $settings)
			{
				$_notify = null;

				foreach ($settings as $setting)
				{
					$_notify |= $setting;
				}

				$notifies[$id] = $_notify;
			}

			if ($notifies)
			{
				$notify->user->setUsers(User::$id, $notifies);
			}

			$this->redirect('@user?controller=Notify');
		}

		$totalItems = $notify->header->select('COUNT(*)')->where('header_recipient = ?', User::$id)->fetchField('COUNT(*)');

		$query = $notify->header->getHeaders(User::$id, (int) $this->get['start'], 25);
		$this->headers = array();

		$ids = array();

		foreach ($query as $row)
		{
			$date = Time::format($row['header_time'], '%d %B %Y');
			$this->headers[$date][] = $row;

			if (!$row['header_read'])
			{
				$ids[] = $row['header_id'];
			}
		}

		if ($ids)
		{
			$notify->header->update(array('header_read' => time()), 'header_id IN(' . implode(',', $ids) . ')');
		}
		$this->pagination = new Pagination('', $totalItems, 25, (int) $this->get['start']);

		return true;
	}

	public function feed()
	{
		$notify =&$this->getModel('notify');
		$query = $notify->header->getHeaders(User::$id, 0, 25);

		$atom = new Feed_Atom;
		$atom->setTitle('Powiadomienia użytkownika ' . User::data('name'));
		$atom->setLink(url('@user?controller=Notify&action=Feed'));
		$atom->setId(Feed_Atom::getUuid('urn:uuid:', User::$id));

		foreach ($query as $row)
		{
			$element = $atom->createElement();

			$element->setTitle(Text::plain($row['header_message']));
			$element->setLink(url($row['header_url']));
			$element->setId(Feed_Atom::getUuid('urn:uuid:', $row['header_id']));
			$element->setUpdated($row['header_time']);

			$element->setContent($row['header_message']);
		}

		$this->output->setHttpHeader('Last-Modified', date(DATE_RFC1123, $atom->getUpdated()));
		echo $atom;

		exit;

	}
}
?>