<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Menu_Controller extends Adm
{
	function main()
	{
		$menu = &$this->getModel('menu');

		if ($this->input->isPost())
		{
			$this->post->setFilters('int');

			$delete = $this->post->delete;
			if ($delete)
			{
				$menu->delete('menu_id IN(' . implode(',', $delete) . ')');
			}	
					
			$this->cache->destroy();
			$this->redirect('adm/Menu');
		}

		$this->menu = $menu->fetchAll();
		return true;
	}

	public function submit($id = 0)
	{
		$id = (int) $id;
		$result = array();

		$menu = &$this->getModel('menu');

		if ($id)
		{
			if (!$result = $menu->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Brak menu o tym ID!');
			}
		}
		$this->filter = new Filter_Input;

		if (isset($this->get->id))
		{
			$mode = $this->input->get('mode');

			if ($mode != 'up' && $mode != 'down')
			{
				throw new AcpErrorException('URL jest nieprawidłowy!');
			}
			$menu->item->$mode((int) $this->get->id);
		}

		if ($this->input->isPost())
		{
			$data['validator'] = array(
					'name'			=> array(
												array('string', false, 2, 100)												
									),
					'tag'			=> array(
												array('string', true, 1, 10)
									),
					'separator'		=> array(
												array('string', true, 0, 255)
									),
					'auth'			=> array(
												array('string', true)
									)
			);
			$data['filter'] = array(
					'name'			=> array('htmlspecialchars'),
					'tag'			=> array('strip_tags')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				$this->load->helper('array');
				$data = array_key_pad($this->filter->getValues(), 'menu_');

				$attributes = @array_combine($this->post->attributes['key'], $this->post->attributes['value']);
				unset($attributes['']);
				$data['menu_attributes'] = serialize($attributes);

				if (!$id)
				{
					$menu->insert($data);
					$id = $this->db->nextId();
				}
				else
				{
					$menu->update($data, "menu_id = $id");
				}
				
				$this->cache->destroy();
				$this->redirect('adm/Menu');
			}
		}

		$this->auth = array(0 => '');
		foreach (Auth::getOptions() as $row2)
		{
			$this->auth[$row2['option_text']] = sprintf('[%s] %s', $row2['option_text'], $row2['option_label']);
		}
		
		$this->size = array();
		
		$this->attributes = $this->items = array();
		if ($id)
		{
			if ($result['menu_attributes'])
			{
				$this->attributes = (array) unserialize($result['menu_attributes']);
			}

			$this->items = array();
			
			foreach ($menu->item->getItemsDepth($id) as $row)
			{		
				@$this->size[$row['item_depth']]++;
					
				if ($row['right_id'] - $row['left_id'] > 1)
				{
					$row['isRemoveable'] = false;
				}		
				else 
				{
					$row['isRemoveable'] = true;
				}
				
				$row['item_order'] = $this->size[$row['item_depth']];
				$this->items[] = $row;					
			}
		}

		return View::getView('adm/menuSubmit', (array) $result);
	}

	public function item($id = 0)
	{
		$id = (int) $id;
		$result = array();

		if (!$menuId = (int) $this->input->get('m'))
		{
			throw new AcpErrorException('Brak parametru "m". Nieprawidłowy URL!');
		}
		$menu = &$this->getModel('menu');

		if ($id)
		{
			if (!$result = $menu->item->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Brak pozycji o tym ID!');
			}
		}
		$result = array_merge($result, $menu->find($menuId)->fetchAssoc());

		$group = &$this->load->model('group');
		$this->groups = $group->select('group_id, group_name')->fetchPairs();
		
		$this->parent = array(0 => '--');
		$this->parent += $menu->item->getItemsAsHtml($menuId);

		$this->filter = new Filter_Input;

		if ($this->input->isPost())
		{
			$data['validator'] = array(

					'name'			=> array(
												array('string', true, 0, 500)
									),
					'tag'			=> array(
												array('string', true, 1, 10)
									),
					'auth'			=> array(
												array('string', true)
									),
					'description'	=> array(
												array('string', true, 0, 255)
									),
					'path'			=> array(
												array('string', true, 0, 255)
									)
			);
			$data['filter'] = array(

					'name'			=> array('trim', 'htmlspecialchars'),
					'path'			=> array('trim'),
					'description'	=> array('htmlspecialchars'),
					'enable'		=> array('int'),
					'tag'			=> array('strip_tags'),
					'parent'		=> array('int'),
					'focus'			=> array('trim', 'strip_tags', 'htmlspecialchars')

			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				$this->load->helper('array');
				$data = array_key_pad($this->filter->getValues(), 'item_');

				$data['item_menu'] = $menuId;
				$data['item_enable'] = (int) $data['item_enable'];
				$attributes = $this->post->attributes;

				$attributes = @array_combine($attributes['key'], $attributes['value']);
				unset($attributes['']);
				
				$data['item_attributes'] = serialize($attributes);			

				try
				{
					$this->db->begin();

					if (!$id)
					{
						$id = $menu->item->insert($data);
					}
					else
					{
						unset($data['item_parent']);
						$menu->item->update($data, "item_id = $id");
					}

					$menu->group->delete("item_id = $id");

					if ($group)
					{
						$sql = array();
						
						foreach ($this->post->groups as $groupId)
						{
							$sql[] = array(
								'group_id'		=> $groupId,
								'item_id'		=> $id
							);
						}
						if ($sql)
						{							
							$this->db->multiInsert('menu_group', $sql);
						}
					}
					
					$this->db->commit();
				}
				catch (Exception $e)
				{
					$this->db->rollback();

					Box::information('Błąd', $e->getMessage(), '', 'adm/information_box');
					exit;
				}
				$this->cache->destroy();

				$this->redirect('adm/Menu/Submit/' . $menuId);
			}
		}


		$this->itemGroups = array();
		if ($id)
		{
			$query = $menu->group->fetch("item_id = $id");

			while ($row2 = $query->fetchAssoc())
			{
				$this->itemGroups[] = $row2['group_id'];
			}
		}

		$this->auth = array(0 => '');
		foreach (Auth::getOptions() as $row2)
		{
			$this->auth[$row2['option_text']] = sprintf('[%s] %s', $row2['option_text'], $row2['option_label']);
		}

		$this->attributes = array();
		if ($id)
		{
			if ($result['item_attributes'])
			{
				$this->attributes = (array) unserialize($result['item_attributes']);
			}
		}

		return View::getView('adm/menuItemSubmit', (array) $result);
	}

	public function items($id)
	{
		$id = (int)$id;
		$this->post->setFilters('int');

		if ($delete = $this->post->delete)
		{
			$menu = &$this->load->model('menu');
			$menu->item->delete($delete);
		}
		$this->cache->destroy();

		$this->redirect('adm/Menu/Submit/' . $id);
	}
}
?>