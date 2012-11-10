<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Parser_Login implements Parser_Interface
{
	private $cache;
	private $logins = array();

	function __construct()
	{
		$this->cache = &Load::loadClass('cache');
		$this->logins = $this->cache->load('logins');
	}

	function __destruct()
	{
		$this->cache->save('logins', $this->logins);
	}

	public function parse(&$content, Parser_Config_Interface &$config)
	{
		$core = &Core::getInstance();

		$patterns = array('~(@([0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ=|#_ ()[\]^-]+)):~', '~(?<!">|" >)(@([0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ=|#_()[\]^-]+))~', '~(@{([0-9a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ=|#_ .()[\]^-]+))}~');
		$tags = Parser::extractTags($content, '(a|code|tt|kbd)');
		$pre = Parser::extractTags($content, 'pre');

		$logins = array();

		foreach ($patterns as $pattern)
		{
			preg_match_all($pattern, $content, $matches);

			if ($matches[2])
			{
				foreach ($matches[2] as $login)
				{
					$logins[] = Text::toLower($login);
				}
			}
		}

		if ($logins && count($logins) < 20)
		{
			$logins = array_unique($logins);

			$diff = array_diff($logins, array_keys((array) $this->logins));
			if ($diff)
			{
				$result = array();

				$rowset = $core->db->select('LOWER(user_name) AS user_name, user_id, user_photo')->from('user')->where('user_name IN(' . implode(',', array_map('Text::quote', $diff)) . ')')->fetchAll();
				foreach ($rowset as $row)
				{
					$result[$row['user_name']] = array('id' => $row['user_id'], 'photo' => $row['user_photo']);
				}
				$this->logins = array_merge((array) $this->logins, (array) $result);

				foreach (array_diff($logins, array_keys($this->logins)) as $login)
				{
					$this->logins[$login] = null;
				}
			}

			$content = preg_replace_callback($patterns, array(&$this, 'setAnchor') , $content);
		}

		Parser::retractTags($content, $pre);
		Parser::retractTags($content, $tags);

		$result = array();
		foreach ($logins as $login)
		{
			if (isset($this->logins[$login]))
			{
				$result[] = $this->logins[$login]['id'];
			}
		}

		return $result;
	}

	private function setAnchor($matches)
	{
		$login = Text::toLower($matches[2]);

		if (isset($this->logins[$login]))
		{
			$value = $matches[0];

			if ($value[1] == '{' && $value[strlen($value) -1] == '}')
			{
				$value = '@' . substr($value, 2, -1);
			}
			$append = '';

			if ($value[strlen($value) -1] == ':')
			{
				$append = ':';
				$value = substr($value, 0, -1);
			}

			$ref = $this->logins[$login];

			if (empty($ref['photo']))
			{
				$ref['photo'] = Url::__('template/img/avatar.jpg');
			}
			else
			{
				$ref['photo'] = Url::__('store/_a/' . $ref['photo']);
			}
			return Html::a(url('@profile?id=' . $ref['id']), $value, array('class' => 'login', 'data-photo' => $ref['photo'], 'data-pm-url' => url('@user?controller=Pm&action=Submit&user=' . $ref['id']), 'data-find-url' => url(Path::connector('forum')) . '?view=user&user=' . $ref['id'] . '#user')) . $append;
		}
		else
		{
			return $matches[0];
		}
	}
}

?>