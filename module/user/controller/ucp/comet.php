<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

ignore_user_abort(false);
set_time_limit(320);

class Comet_Controller extends Controller
{
	const DELAY 		=		5;
	const TIMEOUT		=		300;

	public function main()
	{
		if (User::$id > User::ANONYMOUS)
		{
			$userId = User::$id;

			$userNotifyUnread = User::data('notify_unread');
			$userPmUnread = User::data('pm_unread');
		}
		elseif (isset($this->get->sid))
		{
			list($userId, $userNotifyUnread, $userPmUnread) = $this->db->select('user_id, user_notify_unread, user_pm_unread')->from('session')->join('user', 'user_id = session_user_id')->where('session_id = ?', $this->get->sid)->fetchArray();
		}
		else
		{
			exit;
		}

		$this->output->setHttpHeader('Expires',  'Mon, 26 Jul 1997 05:00:00 GMT');
 		$this->output->setHttpHeader('Last-Modified', gmdate("D, d M Y H:i:s") . ' GMT');
 		$this->output->setHttpHeader('Cache-Control',  'no-store, no-cache, must-revalidate');
 		$this->output->setHttpHeader('Cache-Control',  'post-check=0, pre-check=0');
 		$this->output->setHttpHeader('Pragma',  'no-cache');
 		$this->output->setHttpHeader('Access-Control-Allow-Origin', '*');

		$timer = 0;
		echo str_repeat(' ', 4096);

		while (!connection_aborted() && !isset($isOutput) && $timer < self::TIMEOUT)
		{
			sleep(self::DELAY);
			$timer += self::DELAY;

			list($notifyUnread, $pmUnread) = $this->db->select('user_notify_unread, user_pm_unread')->from('user')->where('user_id = ?', $userId)->fetchArray();

			if ($notifyUnread > $userNotifyUnread || $pmUnread > $userPmUnread)
			{
				echo json_encode(array(
					'notify'		=> $notifyUnread,
					'pm'			=> $pmUnread
				));

				$isOutput = true;
			}

			echo "\n";

			ob_flush();
			flush();
		}

		exit;
	}
}
?>