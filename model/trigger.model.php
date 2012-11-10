<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Trigger_Event_Model extends Model
{
	protected $name = 'trigger_event';
	protected $prefix = 'event_';
	protected $primary = 'event_id';
}

class Trigger_Model extends Model
{
	const SYSTEM = 0;
	const NORMAL = 1;

	protected $name = '`trigger`';
	protected $prefix = 'trigger_';
	protected $primary = 'trigger_id';

	public $event;

	function __construct()
	{
		$this->event = new Trigger_Event_Model;
	}
}
?>