<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa bazowa dla kontrolerow z panelu administracyjnego
 */
class Adm extends Controller
{
	/**
	 * Tablica zwierajaca pozycje menu glownego
	 */
	private static $menu = array();
	/**
	 * Tablica zawierajaca pozycje z podmenu
	 */
	private static $submenu = array();
	/**
	 * ID aktualnie wybranej pozycji menu (przyjmuje tylko ID zakladek rodzicow)
	 */
	private static $currentMenuId = 0;

	function __construct()
	{
		parent::__construct();

		header("cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	}

	private function getLogin()
	{
		if (isset($this->post->loginSubmit))
		{
			if (!$error = Validate::call($this->post->value('userPassword'), new Validate_String(false, 2, 50)))
			{
				return $error;
			}
			$password = hash('sha256', User::data('salt') . $this->post->value('userPassword'));
			if ($password !== User::data('password'))
			{
				Log::add(null, E_ACP_LOGIN_FAILED);
				return 'Podane hasło jest nieprawidłowe';
			}

			$session = &$this->getModel('adm/adm_session');
			$session->insert(array(
					'session_id'			=> User::$sid,
					'session_time'			=> time(),
					'session_user_id'		=> User::$id
				)
			);

			// ustawienie dodatkowego cookies zabezpieczajacego
			$this->output->setCookie('adm', md5(User::data('salt')), 0);

			Log::add(null, E_ACP_LOGIN);
			return true;
		}

		return false;
	}

	/**
	 * Metoda wywolywana na starcie kazdego kontrolera z panelu administracyjnego.
	 * Sprawdza, czy uzytkownik ma dostep do danej opcji z panelu
	 */
	function __start()
	{
		if (!Auth::get('a_'))
		{
			throw new Error(403, 'No access');
		}

		$session = &$this->getModel('adm/adm_session');
		if (!$session->gc() || $this->input->cookie('adm') != md5(User::data('salt')))
		{
			$this->load->library('validate');

			if (($error = $this->getLogin()) !== true)
			{
				$hiddenFields = '';

				foreach ($_POST as $key => $value)
				{
					if (is_array($value))
					{
						foreach ($value as $k => $v)
						{
							$hiddenFields .= Form::hidden($key . '[' . $k . ']', $v);
						}
					}
					else
					{
						$hiddenFields .= Form::hidden($key, $value);
					}

				}
				echo new View('adm/index', array(
					'error'			=> $error,
					'hiddenFields'	=> $hiddenFields
					)
				);

				exit;
			}
			else
			{
				if (sizeof($_POST) <= 3)
				{
					$this->redirect(($this->input->isSecure() ? 'https://' : 'http://') . Config::getItem('site.host', $this->input->getHost()) . $this->input->server('REQUEST_URI'));
				}
			}
		}

		$menu = &$this->load->model('adm/adm_menu');

		$controller = $this->getController();
		$action = $this->getAction();

		list($m_id, $m_parent, $m_auth) = $menu->getId($controller, $action)->fetchArray();

		if (!$m_id)
		{
			list($m_id, $m_parent, $m_auth) = $menu->getId($controller)->fetchArray();
		}
		if (!Auth::get($m_auth))
		{
			throw new AcpErrorException('Brak dostępu');
		}
		self::$currentMenuId = $m_parent ? $m_parent : $m_id;

		foreach ($menu->getMenu()->fetch() as $row)
		{
			if (!Auth::get($row['menu_auth']))
			{
				continue;
			}
			$row['menu_focus'] = false;
			$row['menu_url'] = 'adm/' . ucfirst($row['menu_controller']) . ($row['menu_action'] != 'main' ? '/' . ucfirst($row['menu_action']) : '');

			if ($row['menu_id'] == $m_id || $row['menu_id'] == $m_parent)
			{
				$row['menu_focus'] = true;
			}
			if (!$row['menu_parent'])
			{
				self::$menu[$row['menu_id']] = $row;
			}
			else
			{
				self::$submenu[$row['menu_parent']][] = $row;
			}
		}
	}

	/**
	 * Zwraca tablice z menu rodzicielskimi
	 * @return mixed
	 */
	public static function getMenu()
	{
		return self::$menu;
	}

	/**
	 * Zwraca tablice z pod menu aktualnego rodzica
	 * @return mixed
	 */
	public static function getCurrentSubMenu()
	{
		return isset(self::$submenu[self::$currentMenuId]) ? self::$submenu[self::$currentMenuId] : array();
	}

	/**
	 * Zwraca wszystkie pozycje w menu - potomkach
	 * @return mixed
	 */
	public static function getSubMenus()
	{
		return self::$submenu;
	}

	/**
	 * Zwraca tablice pod menu wybranego rodzica
	 * @param int $parentId ID menu - rodzica
	 * @return mixed
	 */
	public static function getSubMenu($parentId)
	{
		return isset(self::$submenu[$parentId]) ? self::$submenu[$parentId] : array();
	}

	/**
	 * Ustawia tablice menu
	 * @param mixed $menu Tablica menu
	 */
	public static function setMenu($menu)
	{
		self::$menu = $menu;
	}
}
?>