<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Validate_Groupname extends Validate_Abstract implements IValidate
{
	const GROUP_ALREADY_EXIST	=		2;

	protected $templates = array(

			self::GROUP_ALREADY_EXIST		=> 'Grupa o nazwie "%value%" już istnieje'
	);

	public function isValid($value)
	{
		$this->setValue($value);

		$group = &Core::getInstance()->load->model('group');

		if ($row = $group->getByName($value)->fetchObject())
		{
			$this->setMessage(self::GROUP_ALREADY_EXIST);
		}
		
		return ! $this->isMessages();
	}
}
?>