<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Ajax_Controller extends Controller
{
	function __start()
	{
		if (User::$id == User::ANONYMOUS)
		{
			throw new Error(403, 'Brak dostępu');
		}

		if (!$this->input->isAjax())
		{
			exit;
		}
	}

	public function notify()
	{
		$notify = &$this->getModel('notify');
		$query = $notify->header->getHeaders(User::$id);

		$json = array(
			'notifyUnread'		=> User::data('notify_unread'),
			'sessionStart'		=> User::data('session_start')
		);
		$ids = array();

		foreach ($query as $row)
		{
			$json['header'][] = array(
				'message'			=> $row['header_message'],
				'plain'				=> Text::plain($row['header_message']),
				'url'				=> url($row['header_url']),
				'time'				=> User::date($row['header_time']),
				'photo'				=> @$row['user_photo'],
				'user'				=> url('@user?id=' . $row['user_id']),
				'userName'			=> $row['user_name'],
				'read'				=> $row['header_read']
			);

			if (!$row['header_read'])
			{
				$ids[] = $row['header_id'];
			}
		}

		if ($ids)
		{
			$notify->header->update(array('header_read' => time()), 'header_id IN(' . implode(',', $ids) . ')');
		}

		echo json_encode($json);
		exit;
	}

	public function pm()
	{
		$pm = &$this->getModel('pm');
		$query = $pm->fetch('pm_to = ' . User::$id . ' AND pm_folder = 1', 'pm_id DESC', 0, 5);

		$json = array(
			'pmUnread'			=> User::data('pm_unread')
		);

		foreach ($query as $row)
		{
			$message = Text::plain($row['pm_message']);

			$json['pm'][] = array(
				'recipient'			=> $row['u1_name'],
				'photo'				=> $row['u1_photo'],
				'time'				=> User::date($row['pm_time']),
				'url'				=> url('@user?controller=Pm&action=View&id=' . $row['pm_id']) . '#pm' . $row['pm_id'],
				'message'			=> Text::limit($message, 255),
				'header'			=> Text::limit($message, 50),
				'unread'			=> !(bool) $row['pm_read']
			);
		}

		echo json_encode($json);
		exit;
	}

	public function session()
	{
		echo json_encode(array('notify' => User::data('notify_unread'), 'pm' => User::data('pm_unread')));
		exit;
	}
}
?>