<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Block_Controller extends Adm
{
	public $regions = array('' => '');

	function __construct()
	{
		parent::__construct();

		foreach (Region::getRegions() as $name => $data)
		{ 
			$this->regions[$name] = $data['text'];
		}
	}

	function main()
	{		
		$block = &$this->load->model('block');

		if ($this->input->getMethod() == Input::POST)
		{
			if ($this->post->delete)
			{
				$delete = array_map('intval', $this->post->delete);
				
				UserErrorException::__(Trigger::call('application.onBlockDelete', $delete));
				$block->delete($delete);				
			}

			if ($this->post->block)
			{
				foreach ((array)$this->post->block as $block_id => $region)
				{
					if ($block_id = intval($block_id))
					{
						if (isset($this->regions[$region]))
						{
							$block->updateRegion($block_id, $region);							
						}
					}
				}
				$this->cache->destroy();
			}
			$this->redirect('adm/Block');
		}

		if ($this->get->id)
		{
			$mode = $this->get->mode;
			$block->$mode($this->get->id);
		}

		$this->block = array();
		$this->regionBlock = array();

		foreach ($this->regions as $key => $name)
		{
			$this->regionBlock[$key] = 0;
		}

		$result = $block->fetch(null, 'block_region ASC, block_order ASC')->fetchAll();
		foreach ($result as $row)
		{
			$this->regionBlock[$row['block_region']]++;
			$this->block[] = $row;
		}

		return true;
	}

	public function submit($id = 0)
	{
		$this->caches = array(
			Block::CACHE_NONE				=> 'Nie',
			Block::CACHE_ANONYMOUS			=> 'Tylko dla niezalogowanych',
			Block::CACHE_ALL				=> 'Tak, dla wszystkich'
		);

		$id = (int) $id;
		$result = array();

		$block = &$this->getModel('block');
		if ($id)
		{
			if (!$result = $block->find($id)->fetchAssoc())
			{
				throw new AcpErrorException('Blok o tym ID nie istnieje!');
			}
		}
		
		$group = &$this->getModel('group');
		$this->groups = $group->select('group_id, group_name')->fetchPairs();

		$plugin = &$this->getModel('plugin');
		
		$this->plugins = array(0 => '');		
		$this->plugins += $plugin->select('plugin_id, plugin_text')->fetchPairs();
		
		$trigger = &$this->getModel('trigger');

		$this->triggers = array(0 => '');
		$this->triggers += $trigger->select('trigger_id, trigger_name')->order('trigger_type ASC, trigger_name DESC')->fetchPairs();

		$this->filter = new Filter_Input;

		if ($this->input->getMethod() == Input::POST)
		{
			$data['validator'] = array(
					'name'			=> array(
												array('string', false, 2, 100)
									),
					'region'		=> array(
												array('string', true)
									)

			);
			$data['filter'] = array(
					
					'name'			=> array('htmlspecialchars'),
					'plugin'		=> array('int'),
					'item'			=> array('int'),
					'trigger'		=> array('int'),
					'scope'			=> array('int'),
					'cache'			=> array('int'),
					'header'		=> array('string'),
					'footer'		=> array('string'),
					'pages'			=> array('string'),
					'auth'			=> array('string')
			);
			$this->filter->setRules($data);

			if ($this->filter->isValid($_POST))
			{
				$this->load->helper('array');
				$data = array_key_pad($this->filter->getValues(), 'block_');

				try
				{
					$this->db->begin();

					if (!$data['block_trigger'])
					{
						$data['block_trigger'] = null;
					}
					if (!$data['block_plugin'])
					{
						$data['block_plugin'] = null;
					}
					if (!$data['block_item'])
					{
						$data['block_item'] = null;
					}

					if ($this->post->style)
					{
						if (!$result['block_style'])
						{
							$data['block_style'] = dechex(mt_rand(0, 0x7fffffff));
							$stylesheet = $data['block_style'];
						}
						else
						{
							$stylesheet = $result['block_style'];
						}
					}
					else
					{
						if (!empty($result['block_style']))
						{
							@unlink('store/css/' . $result['block_style'] . '.css');
							$data['block_style'] = '';
						}
					}
					UserErrorException::__(Trigger::call('application.onBlockSubmit', array(&$data)));

					if (!$id)
					{
						$block->insert($data);
						$id = $this->db->nextId();
					}
					else
					{
						$block->update($data, "block_id = $id");
					}

					$block->group->delete("block_id = $id");

					if ($this->post->groups)
					{
						$sql = array();
						
						foreach ($this->post->groups as $groupId)
						{
							$sql[] = array(
								'group_id'		=> $groupId,
								'block_id'		=> $id
							);
						}
						if ($sql)
						{							
							$this->db->multiInsert('block_group', $sql);
						}
					}
					$this->db->commit();

					if (isset($stylesheet))
					{
						@file_put_contents("store/css/$stylesheet.css", $this->post->style, LOCK_EX);
					}
				}
				catch (Exception $e)
				{
					$this->db->rollback();

					Box::information('Błąd', $e->getMessage(), '', 'adm/information_box');
					exit;
				}
				$this->cache->destroy();

				$this->redirect('adm/Block');
			}
		}

		$this->blockGroups = array();
		if ($id)
		{
			$query = $block->group->fetch("block_id = $id");

			while ($row2 = $query->fetchAssoc())
			{
				$this->blockGroups[] = $row2['group_id'];
			}
		}
		$auth = &$this->getModel('auth');

		$this->auth = array(0 => '');
		foreach (Auth::getOptions() as $row2)
		{
			$this->auth[$row2['option_text']] = sprintf('[%s] %s', $row2['option_text'], $row2['option_label']);
		}

		if ($id)
		{			
			if ($result['block_style'])
			{
				$result['block_style'] = file_get_contents('store/css/' . $result['block_style'] . '.css');
			}
		}

		return View::getView('adm/blockSubmit', (array) $result);
	}

	public function __item()
	{
		$pluginId = (int)$this->get->id;

		$item = &$this->getModel('block')->item;
		echo json_encode($item->getItemList($pluginId));

		exit;
	}
}
?>