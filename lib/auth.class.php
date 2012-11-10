<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Auth
{
	private static $acl;
	private static $options;

	public static function getOptions()
	{
		return self::$options;
	}

	public static function load()
	{
		$core = &Core::getInstance();
		self::$acl = &Load::loadClass('acl');

		if (!$core->cache->exists('_acl'))
		{
			$auth = $core->load->model('auth');
			self::$options = $auth->getOptions();

			$core->cache->put('_acl', self::$options);
		}
		else
		{
			self::$options = $core->cache->get('_acl');
		}

		$permission = User::data('permission') ? unserialize(base64_decode(User::data('permission'))) : array();

		if (!$permission)
		{
			if (!isset($auth))
			{
				$auth = &$core->load->model('auth');
			}

			foreach ($auth->user(User::$id) as $row)
			{
				$k = self::$options[$row['data_option']]['option_text'];
				$permission[$k] = $row['data_value'];
			}
			$auth->save($permission, User::$id);
		}

		self::$acl->create(User::data('name'), null);
		foreach ($permission as $k => $v)
		{
			self::$acl->set(User::data('name'), $k, (bool)$v);
		}
	}

	public static function get($option)
	{
		if (!self::$acl)
		{
			self::$acl = &Load::loadClass('acl');
		}

		return self::$acl->get(User::data('name'), $option);
	}

	public static function allow($role, $object)
	{
		self::$acl->allow($role, $object);
	}

	public static function deny($role, $object)
	{
		self::$acl->deny($role, $object);
	}
}

?>