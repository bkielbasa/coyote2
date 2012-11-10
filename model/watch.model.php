<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Watch_Model extends Model
{
	protected $name = 'watch';

	public function watch($pageId, $moduleId, $pluginId = null)
	{
		$query = $this->select()->where('page_id = ' . $pageId . ' AND user_id = ' . User::$id);

		if ($moduleId !== null)
		{
			$query->where("watch_module = $moduleId");
		}
		if ($pluginId !== null)
		{
			$query->where("watch_plugin = $pluginId");
		}
		$query = $query->get();

		if (count($query))
		{
			$sql = 'page_id = ' . $pageId . ' AND user_id = ' . User::$id;

			if ($moduleId !== null)
			{
				$sql .= " AND watch_module = $moduleId";
			}
			if ($pluginId !== null)
			{
				$sql .= " AND watch_plugin = $pluginId";
			}

			$this->delete($sql);
			return false;
		}
		else
		{
			$this->insert(array(
				'page_id'			=> $pageId,
				'user_id'			=> User::$id,
				'watch_time'		=> time(),
				'watch_module'		=> $moduleId,
				'watch_plugin'		=> $pluginId
				)
			);

			return true;
		}
	}

	public function isWatched($pageId, $moduleId = null, $pluginId = null)
	{
		if (User::$id == User::ANONYMOUS)
		{
			return false;
		}
		$query = $this->select('user_id')->where("page_id = $pageId AND user_id = " . User::$id);

		if ($moduleId !== null)
		{
			$query->where("watch_module = $moduleId");
		}
		if ($pluginId !== null)
		{
			$query->where("watch_plugin = $pluginId");
		}

		return (bool) count($query->get());
	}

	public function getUsers($pageId, $moduleId, $pluginId = null)
	{
		$query = $this->db->select('w.user_id')
						  ->from('watch w')
						  ->innerJoin('page_group p', 'p.page_id = w.page_id')
						  ->innerJoin('auth_group g', 'g.group_id = p.group_id AND g.user_id = w.user_id')
						  ->where("w.page_id = $pageId")
						  ->where("w.watch_module = $moduleId");

		if ($pluginId !== null)
		{
			$query->where("w.watch_plugin = $pluginId");
		}
		$query->where('w.user_id != ' . User::$id);

		return $query->get()->fetchCol();
	}

	public function fetch($where = null, $order = null, $limit = null, $count = null)
	{
		$query = $this->select('page.page_id, page_subject, location_text, watch_time, location_text');

		if ($where)
		{
			$query->where($where);
		}
		if ($order)
		{
			$query->order($order);
		}
		if ($limit || $count)
		{
			$query->limit($limit, $count);
		}
		$query->innerJoin('page', 'page.page_id = watch.page_id');
		$query->leftJoin('location', 'location_page = page.page_id');

		return $query->get();
	}
}
?>