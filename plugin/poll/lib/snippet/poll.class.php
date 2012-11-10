<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Snippet_Poll extends Snippet
{
	public $pollId = 0;

	public function display(IView $instance = null)
	{
		if (!$this->module->isPluginEnabled('poll'))
		{
			return false;
		}
		
		if (!$this->pollId)
		{
			$query = $this->db->select('poll_id')->from('poll')->where('poll_enable = 1')->order('RAND()')->limit(1)->get();

			if (count($query))
			{
				$this->pollId = $query->fetchField('poll_id');
			}
		}

		if ($this->pollId)
		{
			$poll = new Poll;
			$poll->setItem($this->pollId);

			echo $poll->display();
		}
	}
}
?>