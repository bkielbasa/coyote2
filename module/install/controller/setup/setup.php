<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Setup_Controller extends Install_Controller
{
	function __start()
	{
		parent::__start();

		if ($this->session->install < 5)
		{
			$this->redirect('User');
		}
	}

	function main()
	{
		$this->load->helper('system');
		return true;
	}

	public function setup()
	{
		try
		{
			$this->load->library('db', 'mysql');
			$this->db->connect($this->session->db_host, $this->session->db_login, $this->session->db_password, $this->session->db_database, isset($this->session->db_port) ? $this->session->db_port : false);
			if ($this->session->charset)
			{
				$this->db->setCharset($this->session->charset);
			}
			$this->db->query('SET FOREIGN_KEY_CHECKS=0;');

			if (!isset($this->session->schemaInstalled))
			{
				Sql::import(Config::getBasePath() . 'module/install/schema.sql');
				$this->session->schemaInstalled = true;
			}

			if (version_compare('5.1', mysql_get_server_info()) !== 1)
			{
				Sql::import(Config::getBasePath() . 'module/install/schema-5-1.sql');
			}

			$this->db->begin();

			if (!isset($this->session->dataInstalled))
			{
				Sql::import(Config::getBasePath() . 'module/install/data.sql');
				$this->session->dataInstalled = true;
			}

			$user = &$this->load->model('user');
			$this->db->query('ALTER TABLE `user` AUTO_INCREMENT = 0');

			$data = array(
				'user_id'			=> 0,
				'user_name'			=> 'Anonim',
				'user_group'		=> 1
			);
			$user->insert($data);
			$userId = $this->db->nextId();

			$password = hash('sha256', $this->session->password);
			// dodanie admina
			$data = array(
				'user_name'			=> $this->session->name,
				'user_password'		=> $password,
				'user_email'		=> $this->session->email,
				'user_confirm'		=> ($this->session->email ? 1 : 0)
			);

			$userId = $user->insert($data);

			$group = &$this->load->model('group');
			// pobranie ID grupy anonymous
			$groupId = $group->select('group_id')->where("group_name = 'ANONYMOUS'")->get()->fetchField('group_id');

			// dodanie anonimowego uzytkownika do grupy anonymous
			$this->db->insert('auth_group', array(
				'user_id'			=> 0,
				'group_id'			=> $groupId
				)
			);

			$data = array(
				'group_name'		=> 'ADMIN',
				'group_leader'		=> $userId,
				'group_display'		=> 0,
				'group_open'		=> 0,
				'group_type'		=> Group_Model::SPECIAL
			);

			$groupId = $group->insert($data);
			// uprawnienia dla admina: moze wszystko
			$this->db->update('auth_data', array('data_value' => 1), "data_group = $groupId");

			// dodanie uzytkownika do grupy USER
			$this->db->insert('auth_group', array(
				'user_id'			=> $userId,
				'group_id'			=> 2
				)
			);

			if (!isset($this->session->pageInstalled))
			{
				Sql::import(Config::getBasePath() . 'module/install/page.sql');
				$this->session->pageInstalled = true;
			}

			Log::add(null, 'Instalacja systemu');

			$this->db->query('SET FOREIGN_KEY_CHECKS=1;');
			$this->db->commit();
		}
		catch (Exception $e)
		{
			$this->db->rollback();

			$this->output->setStatusCode(500);
			echo $e->getMessage();
		}
	}

	public function success()
	{
		$xml = simplexml_load_file('config/db.xml.default');

		$xml->databases->default->host  = $this->session->db_host;
		$xml->databases->default->user = $this->session->db_login;
		$xml->databases->default->password = $this->session->db_password;
		$xml->databases->default->dbname = $this->session->db_database;
		$xml->databases->default->charset = $this->session->charset;

		if (isset($this->session->db_port))
		{
			$xml->databases->default->port = $this->session->db_port;
		}

		if ($this->post->port)
		{
			$xml->databases->default->port = $this->session->db_port;
		}
		$xml = $xml->asXml();
		file_put_contents('config/db.xml', $xml);

		$xml = simplexml_load_file('config/module.xml');
		$xml->module = 'user';
		file_put_contents('config/module.xml', $xml->asXml());

		copy('config/autoload.xml.default', 'config/autoload.xml');
		copy('config/route.xml.copy', 'config/route.xml');
		copy('config/trigger.xml.copy', 'config/trigger.xml');

		$xml = simplexml_load_file('config/config.xml');

		$xml->site->title = $this->session->site_title;
		$xml->core->frontController = $this->session->core_frontController;
		$xml->site->host = $this->session->site_host;
		$xml->cookie->host = $this->session->cookie_host;
		$xml->cookie->prefix = $this->session->cookie_prefix;
		$xml->cache->prefix = uniqid();
		$xml->install = 'true';
		$xml->scheduler = md5(uniqid());

		$xml = $xml->asXml();
		file_put_contents('config/config.xml', $xml);

		if ($this->session->name)
		{
			$this->load->library('db', 'mysql');
			$this->db->connect($this->session->db_host, $this->session->db_login, $this->session->db_password, $this->session->db_database, isset($this->session->db_port) ? $this->session->db_port : false);

			if ($this->session->charset)
			{
				$this->db->setCharset($this->session->charset);
			}

			$user = new User_Model;
			$result = $user->getByName($this->session->name)->fetchAssoc();

			$sessiondata = array(
				'user_id'	=> $result['user_id'],
				'key'		=> md5($result['user_password'])
			);
			// zapamietane usera. ustawienie ciastka
			$this->output->setCookie('data', serialize($sessiondata), strtotime('+1 year'), '', $this->session->cookie_host);
		}

		$this->session->destroy();

		return true;
	}
}
?>