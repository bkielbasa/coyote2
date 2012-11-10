<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Pm extends Adm
{
	function main()
	{
		$group = &$this->getModel('group');
		$this->group = array();

		$query = $group->select('group_id, group_name')->get();
		foreach ($query as $row)
		{
			$this->group[$row['group_id']] = $row['group_name'];
		}
		$this->filter = new Filter_Input;

		if ($this->input->isMethod(Input::POST))
		{
			$data['validator'] = array(

				'subject'					=> array(
														array('string', false, 3, 100)
											),
				'message'					=> array(
														array('string', false, 1)
											)
			);
			$data['filter'] = array(

				'subject'					=> array('htmlspecialchars'),
				'message'					=> array(new Filter_Xss)
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				$pm = &$this->getModel('pm');
				$pm->submitGroup($this->post->group, $this->post->subject, $this->post->message);

				Log::add(User::data('name') . ' wysłał wiadomość do użytkowników', E_PM_SUBMIT);

				$this->message = 'Wiadomości zostały wysłane';
			}
		}
				
		return true;
	}
}
?>