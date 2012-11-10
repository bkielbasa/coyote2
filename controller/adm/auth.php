<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Auth_Controller extends Adm
{
	function main()
	{
		$group = &$this->getModel('group');
		$totalItems = $group->count();

		$this->group = $group->fetch(null, null, (int)$this->get['start'], 50)->fetch();
		$this->pagination = new Pagination('', $totalItems, 50, (int)$this->get['start']);

		return true;
	}

	public function submit($id = 0)
	{
		if (!Auth::get('a_auth'))
		{
			throw new AcpErrorException('Nie masz uprawnień do edycji tej strony');
		}
		$id = (int)$id;

		$group = &$this->load->model('group');

		if ($group_name = $this->input->get('name'))
		{
			$id = @$group->getByName($group_name)->fetchObject()->group_id;
		}
		if (!$id)
		{
			throw new AcpErrorException('Taka grupa nie istnieje!');
		}
		if (!$group_arr = $group->find($id)->fetchObject())
		{
			throw new AcpErrorException('Taka grupa nie istnieje!');
		}
		$auth = &$this->load->model('auth');
			
		if ($this->input->getMethod() == Input::POST)
		{
			$data = array_map('intval', $this->input->post->data);
			$auth->setGroupData($id, $data);
			$this->db->update('user', array('user_permission' => ''));

			$this->cache->destroy();

			Box::information('Uprawnienia zmienione', 'Uprawnienia grupy zostały zmienione', '', 'adm/information_box');
			exit;
		}

		echo $this->load->view('adm/authSubmit', array(
				'group_id'		=> $id,
				'group_arr'		=> $group_arr,
				'data'			=> $auth->getGroupData($id)->fetch()
			)
		);
	}

	public function adm()
	{
		$menu = &$this->load->model('adm/adm_menu');

		if ($this->input->getMethod() == Input::POST)
		{
			$auth = $this->input->post->auth;
			foreach ($auth as $m_id => $opt)
			{
				$menu->update(array('menu_auth' => $opt), "menu_id = $m_id");
			}
			$this->redirect('adm/Auth/Adm');
		}
		$menu_arr = array();

		$q = $menu->fetch(null, 'menu_parent');
		while ($row = $q->fetchAssoc())
		{
			if (!$row['menu_parent'])
			{
				$menu_arr[$row['menu_id']]['row'] = $row;
				continue;
			}
			$menu_arr[$row['menu_parent']]['subcat'][] = $row;
		}

		$q = $this->db->select('option_text, option_label')->from('auth_option')->get();
		while ($row = $q->fetchAssoc())
		{
			$acl[$row['option_text']] = sprintf('[%s] %s', $row['option_text'], $row['option_label']);
		}

		echo $this->load->view('adm/authAdm', array(
			'menu'		=> $menu_arr,

			'acl'		=> $acl
			)
		);
	}

	public function acl()
	{
		$auth = &$this->load->model('auth');
		$acl = $auth->getOptions();

		if ($this->input->getMethod() == Input::POST)
		{
			$label = array_map('htmlspecialchars', $this->input->post->label);

			if (isset($_POST['delete']))
			{
				$delete = array();

				foreach ((array)$_POST['delete'] as $k => $v)
				{
					$delete[] = $k;
					unset($label[$k]);
				}

				if ($delete)
				{
					$auth->delete('option_id IN(' . implode(',', $delete) . ')');
				}
			}

			if ($label)
			{
				$text = array_map('htmlspecialchars', $this->input->post->text);

				foreach ($label as $k => $v)
				{
					if (!$k = intval($k))
					{
						continue;
					}
					if (!$label[$k] || !$text[$k])
					{
						continue;
					}

					$auth->setOption($text[$k], (bool)$this->input->post->default[$k], $label[$k]);
				}
				$this->cache->destroy('_acl');
			}
			$this->redirect('adm/Auth/ACL');
		}

		echo $this->load->view('adm/authACL', array('option' => $acl));
	}
}
?>