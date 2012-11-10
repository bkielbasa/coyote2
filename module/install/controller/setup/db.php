<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Db_Controller extends Install_Controller
{
	function main()
	{
		if ($this->session->install < 3)
		{
			$this->redirect('Base');
		}
		$this->filter = new Filter_Input;

		if ($this->input->isPost())
		{
			$data = array(
					'db_login'		=> array(
											array('string', false)
									),
					'db_password'	=> array(
											array('string', true)
									),
					'db_host'		=> array(
											array('string', false)
									),
					'db_database'	=> array(
											array('string', false)
									),
					'db_port'		=> array(
											array('int', true)
									),
					'charset'		=> array(
											array('string', true)
									)
			);
			$this->filter->setValidators($data);

			if ($this->filter->isValid($_POST))
			{
				try
				{
					$this->load->library('db', 'mysql');

					$this->db->connect($this->post->db_host, $this->post->db_login, $this->post->db_password, $this->post->db_database, $this->post->db_port(false));
					
					if (version_compare('5.0', mysql_get_server_info()) == 1)
					{
						throw new Exception('Wymagana wersja bazy MySQL to 5.0');						
					}

					$query = $this->db->query('SELECT @@sql_mode');
					$sqlMode = $query->fetchField('@@sql_mode');

					if ($sqlMode != '')
					{
						throw new Exception('SQL Mode posiada wartość: ' . $sqlMode . '. Proszę ustawić sql-mode="" w my.cnf');
					}

					$this->session->install = 4;
					$this->session->set($this->filter->getValues());

					$this->redirect('User');					
				}
				catch (SQLCouldNotSelectDbException $e)
				{
					$this->error = $e->getMessage();
				}
				catch (Exception $e)
				{
					$this->error = $e->getMessage();
				}
			}
		}

		return true;
	}
}
?>