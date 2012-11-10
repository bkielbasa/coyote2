<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Admin extends Adm
{
	function main()
	{
		$this->spaceChar = array('_', '-');

		$email = &$this->getModel('email');
		$this->email = array(0 => '--');

		foreach ($email->fetch()->fetch() as $row)
		{
			$this->email[$row['email_id']] = sprintf('%s [%s]', $row['email_name'], $row['email_description']);
		}

		$richtext = &$this->getModel('richtext');
		$this->richtext = array(0 => '(brak)');

		$query = $richtext->select()->get();
		foreach ($query as $row)
		{
			$this->richtext[$row['richtext_id']] = $row['richtext_name'];
		}

		$this->filter = new Filter_Input;

		if ($this->input->getMethod() == Input::POST)
		{
			if (Auth::get('a_config'))
			{
				$data['validator'] = array(

							'site_email'				=> array(
																	array('string', false),
																	array('email')
														),
							'site_title'				=> array(
																	array('string', false)
														),
							'session_length'			=> array(
																	array('int', false, 300)
														),
							'session_gc'				=> array(
																	array('int', false)
														),
							'site_copyright'			=> array(
																	array('string', true)
														)
				);
				$data['filter'] = array(

							'core_frontController'		=> array('string'),
							'site_host'					=> array('string'),
							'cookie_prefix'				=> array('string'),
							'cookie_host'				=> array('string'),
							'session_length'			=> array('int'),
							'session_gc'				=> array('int'),
							'user_confirm'				=> array('int'),
							'site_keywords'				=> array('strip_tags', 'htmlspecialchars'),
							'site_description'			=> array('strip_tags', 'htmlspecialchars'),
							'email_from'				=> array('strip_tags', 'htmlspecialchars'),
							'site_copyright'			=> array('htmlspecialchars'),
							'page_richtext'				=> array('int')
				);
				$this->filter->setRules($data);

				if ($this->filter->isValid($_POST))
				{
					$xml = simplexml_load_file('config/config.xml');

					$xml->site->email = $this->post->site_email;
					$xml->site->title = $this->post->site_title;
					$xml->site->host = $this->post->site_host;
					$xml->site->keywords = $this->post->site_keywords;
					$xml->site->description = $this->post->site_description;
					$xml->site->copyright = $this->post->site_copyright;
					$xml->email->from = $this->post->email_from;
					$xml->email->confirm = $this->post->email_confirm;
					$xml->email->success = $this->post->email_success;
					$xml->email->password = $this->post->email_password;
					$xml->email->invalid_login = $this->post->email_invalid_login;
					$xml->email->login = $this->post->email_login;
					$xml->email->random = $this->post->email_random;
					$xml->session->length = $this->post->session_length;
					$xml->session->gc = $this->post->session_gc;
					$xml->core->frontController = $this->post->core_frontController;
					$xml->cookie->prefix = $this->post->cookie_prefix;
					$xml->cookie->host = $this->post->cookie_host;
					$xml->user->confirm = $this->post->user_confirm('false');
					$xml->url->lowercase = $this->post->url_lowercase('false');
					$xml->url->ucfirst = $this->post->url_ucfirst('false');
					$xml->url->diacritics = $this->post->url_diacritics('false');
					$xml->url->spaceChar = $this->post->url_spacechar('_');
					$xml->url->remove = ($token = uniqid());
					$xml->page->publish = $this->post->page_publish('false');
					$xml->page->cache = $this->post->page_cache('false');
					$xml->page->richtext = (int)$this->post->page_richtext;
					$xml->attachment->suffix = (string)$this->post->attachment_suffix;
					$xml->attachment->limit = (string)$this->post->attachment_limit;

					if (isset($this->post['shutdown']))
					{
						unset($xml->shutdown);
					}
					else
					{
						$xml->shutdown = htmlspecialchars($this->post->message);
					}
					$xml = $xml->asXml();
					/**
					 * Bardzo brzydkie ominiecie problemu z zapisem CDATA w simplexml.
					 * Mozna by to zrobic inaczej, stosujac DOMDocument
					 */
					$xml = str_replace($token, '<![CDATA[' . $this->post->url_remove . ']]>', $xml);

					@copy('config/config.xml', 'config/config.xml.bak');
					file_put_contents('config/config.xml', $xml, LOCK_EX);

					Box::information('Konfiguracja zapisana!', 'Konfiguracja została zapisana do pliku XML!', '', 'adm/information_box');
					exit;
				}
			}
		}
		return View::MAIN;
	}

	public function adm($id = 0)
	{
		$id = (int)$id;
		$menu = &$this->load->model('adm/adm_menu');

		if ($mode = $this->input->get('mode'))
		{
			if ($mode != 'up' && $mode != 'down')
			{
				throw new AcpErrorException('URL jest nieprawidłowy!');
			}
			$menu->$mode($this->input->get('id'));
		}

		$result = array();
		if ($id)
		{
			if (!$result = $menu->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Menu o tym ID nie istnieje');
			}
		}
		$this->filter = new Filter_Input;

		if ($this->input->isMethod(Input::POST))
		{
			if ($delete = $this->post->delete)
			{
				foreach ($delete as $id)
				{
					$menu->delete("menu_id = $id");
				}

				$this->message = 'Zaznaczone pozycje menu zostały usunięte';
			}
			else
			{
				$data['validator'] = array(

					'text'				=> array(
													array('string', false, 1, 100)
										),
					'controller'		=> array(
													array('string', false, 1, 100)
										),
					'action'			=> array(
													array('string', false, 1, 100)
										)
				);
				$data['filter'] = array(

					'text'				=> array('strip_tags', 'htmlspecialchars'),
					'controller'		=> array('strip_tags', 'htmlspecialchars'),
					'action'			=> array('strip_tags', 'htmlspecialchars')
				);
				$this->filter->setRules($data);

				if ($this->filter->isValid($_POST))
				{
					load_helper('array');
					$data = array_key_pad($this->filter->getValues(), 'menu_');

					if ($id)
					{
						if ((int)$this->post->parent != $result['menu_parent'])
						{
							$sql = 'UPDATE adm_menu SET menu_order = menu_order -1 WHERE menu_parent = ' . $result['menu_parent'] . ' AND menu_order > ' . $result['menu_order'];
							$this->db->query($sql);

							$order = (int)$menu->select('MAX(menu_order)')->where('menu_parent = ' . (int)$this->post->parent)->get()->fetchField('MAX(menu_order)');
							$data += array(
								'menu_order'		=> ++$order,
								'menu_parent'		=> (int)$this->post->parent
							);
						}
						$menu->update($data, "menu_id = $id");
					}
					else
					{
						$data['menu_parent'] = (int)$this->post->parent;
						$menu->insert($data);
					}

					$this->message = 'Menu zostało uaktualnione';
				}
			}
		}

		$this->parent = array(0 => '--');
		$this->menu = array();

		$query = $menu->fetch(null, 'menu_parent ASC, menu_order ASC');
		while ($row = $query->fetchAssoc())
		{
			if (!$row['menu_parent'])
			{
				$this->parent[$row['menu_id']] = $row['menu_text'];
				$this->menu[$row['menu_id']] = $row;

				continue;
			}
			$this->menu[$row['menu_parent']]['subcat'][] = $row;
		}

		return View::getView('adm/adminAdm', $result);
	}

	public function cache()
	{
		$this->adapter = array(
			''			=> 'System plików',
		);

		if (extension_loaded('apc'))
		{
			$this->adapter['apc'] = 'APC';
		}
		if (extension_loaded('eaccelerator'))
		{
			$this->adapter['eaccelerator'] = 'eAccelerator';
		}
		if (extension_loaded('xcache'))
		{
			$this->adapter['xcache'] = 'XCache';
		}

		if ($this->input->getMethod() == Input::POST)
		{
			if (isset($this->post->delete))
			{
				if (isset($this->post['cache']['general']))
				{
					$this->cache->remove();
	
					foreach (scandir('cache/') as $dir)
					{
						if ($dir{0} != '.')
						{
							unlink('cache/' . $dir);
						}
					}
				}
				
				if (isset($this->post['cache']['permission']))
				{
					$this->db->query('UPDATE user SET user_permission = ""');
				}
				
				if (isset($this->post['cache']['text']))
				{
					$this->db->delete('page_cache');
				}

				$this->message = 'Dane z cache zostały usunięte!';
			}


			$xml = simplexml_load_file('config/config.xml');

			$xml->cache->adapter = $this->post->adapter;
			file_put_contents('config/config.xml', $xml->asXml(), LOCK_EX);

			$this->redirect('adm/Admin/Cache');
		}

		return true;
	}
}
?>