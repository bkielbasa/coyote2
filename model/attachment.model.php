<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Attachment_Model extends Model
{
	protected $name = 'attachment';
	protected $prefix = 'attachment_';
	protected $primary = 'attachment_id';

	public function fetch($where = null, $order = null, $limit = null, $count = null)
	{
		$query = $this->select('attachment.*, COUNT(text_id) AS text_id')->leftJoin('page_attachment pp', 'pp.attachment_id = attachment.attachment_id');

		if ($where)
		{
			$query->where($where);
		}
		if ($order)
		{
			$query->order('attachment.' . $order);
		}
		if ($limit || $count)
		{
			$query->limit($limit, $count);
		}
		$query = $query->group('attachment.attachment_id')->get();

		return $query;
	}

	/**
	 * @todo Refaktoryzacja!
	 */
	public function delete($ids, $permanently = false)
	{
		if (!is_array($ids))
		{
			$ids = array($ids);
		}

		if (!$permanently)
		{
			foreach ($ids as $index => $id)
			{
				$sql = "SELECT MAX(text_id) AS text_id, 
							   COUNT(text_id) AS revisions 
						FROM page_attachment 
						WHERE attachment_id = $id
						GROUP BY attachment_id";
				$query = $this->db->query($sql);
				list($textRevision, $revisions) = $query->fetchArray();

				if ($textRevision)
				{
					$this->db->update('page_attachment', array('attachment_id' => null), "text_id = $textRevision AND attachment_id = $id");
					--$revisions;

					if ($revisions > 0)
					{
						unset($ids[$index]);
					}
				}
			}
		}

		if (!$ids && !$permanently)
		{
			return false;
		}

		$query = $this->select('attachment_file')->where('attachment_id IN(' . implode(',', $ids) . ')')->get();
		while ($row = $query->fetchAssoc())
		{
			@unlink('store/_aa/' . $row['attachment_file']);

			foreach (glob('store/_aa/*-' . $row['attachment_file']) as $filename)
			{
				@unlink($filename);
			}
		}
		parent::delete('attachment_id IN(' . implode(',', $ids) . ')');
		return true;
	}
}
?>