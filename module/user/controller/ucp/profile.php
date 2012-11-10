<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Profile extends Controller
{
	function main()
	{
		$id = (int)$this->router->id;
		if (!$id)
		{
			throw new Error(404, 'Profil o podanym ID nie istnieje!');
		}
		$result = array();

		$user = &$this->getModel('user');
		if (!$result = $user->find($id)->fetchAssoc())
		{
			throw new Error(404, 'Profil o podanym ID nie istnieje!');
		}
		Breadcrumb::add('', 'Profil użytkownika');
		Config::setItem('user.name', $result['user_name']);

		$field = &$this->getModel('field');
		$this->fieldList = $field->select('field_name, field_text')->where('field_module = ' . $this->module->getId('user') . ' AND field_display = 1 AND field_profile = 1')->fetchPairs();

		return View::getView('ucp/profile', $result);
	}
}
?>