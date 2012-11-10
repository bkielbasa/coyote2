<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Poll_Item_Model extends Model
{
	protected $name = 'poll_item';
	protected $primary = 'item_id';
	protected $prefix = 'item_';

	public function getItems($pollId)
	{
		$query = $this->select('item_id, item_text')->where("item_poll = $pollId")->get();
		return $query->fetchPairs();
	}
}

class Poll_Vote_Model extends Model
{
	protected $name = 'poll_vote';

	/**
	 * Metoda sprawdza, czy dany uzytkownik oddal glos w danej ankiecie
	 * @param int	$pollId	ID ankiety
	 * @param int	$userId	ID usera
	 * @param int	$userIp	Opcjonalne IP uzytkownika (jezeli uzytkownik jest anonimem)
	 */
	public function hasVoted($pollId, $userId, $userIp = null)
	{
		$query = $this->db->select('vote_poll')->from('poll_vote')->where("vote_poll = $pollId");

		if ($userId != User::ANONYMOUS)
		{
			if ($userIp !== null)
			{
				$query->where("(vote_user = $userId OR vote_ip = ?)", $userIp);
			}
			else
			{
				$query->where("vote_user = $userId");
			}
		}
		elseif ($userIp !== null)
		{
			$query->where('vote_ip = ?', $userIp);
		}

		return count($query->get());
	}

	/**
	 * Metoda przypisuje glosy w ankiecie. Dzieki temu mozemy zapobiedz ponownemu glosowaniu
	 * @param	int	$pollId	ID ankiety
	 * @param	int	$userId	ID usera
	 * @param	array	$itemsId	Tablica z ID glosow
	 */
	public function setVote($pollId, $userId, $itemsId)
	{
		foreach ($itemsId as $itemId)
		{
			parent::insert(array(
				'vote_poll'		=> $pollId,
				'vote_user'		=> $userId,
				'vote_ip'		=> $this->input->getIp(),
				'vote_item'		=> $itemId
				)
			);
		}
	}
}

class Poll_Model extends Model
{
	protected $name = 'poll';
	protected $primary = 'poll_id';
	protected $prefix = 'poll_';

	protected $reference = array(

			'user'		=>		array(

						'table'			=> 'user',
						'col'			=> 'user_id',
						'refCol'		=> 'poll_user'

			)

	);

	public $item;
	public $vote;

	function __construct()
	{
		$this->item = new Poll_Item_Model;
		$this->vote = new Poll_Vote_Model;
	}

	public function submit($id, $title, $start, $length, $maxItems, $isEnabled, array $items = array())
	{
		$currItems = array();
		$items = array_slice($items, 0, 20, true);

		$data = array(
			'poll_title'		=> $title,
			'poll_start'		=> $start,
			'poll_length'		=> ($length * Time::DAY),
			'poll_max_item'		=> $maxItems,
			'poll_enable'		=> $isEnabled
		);

		if (!$id)
		{
			$data['poll_user'] = User::$id;
		}
		else
		{
			unset($data['poll_start']); // nie zmieniamy daty rozpoczecia
			$this->update($data, "poll_id = $id");
		}

		if (!$id)
		{
			$tmp = $items;
			$items = array();

			foreach ($tmp as $value)
			{
				if (strlen(trim($value)))
				{
					$items[] = $value;
				}
			}

			if ($items)
			{
				$this->insert($data);
				$id = $this->db->nextId();

				foreach ($items as $itemId => $value)
				{
					$this->item->insert(array(
						'item_id'		=> $itemId,
						'item_poll'		=> $id,
						'item_text'		=> $value
						)
					);
				}
			}
		}
		else
		{
			$currItems = $this->item->getItems($id);

			// do dodania
			foreach ((array) array_diff_key($items, $currItems) as $itemId => $value)
			{
				if (strlen(trim($value)))
				{
					$this->item->insert(array(
						'item_id'		=> $itemId,
						'item_poll'		=> $id,
						'item_text'		=> $value
						)
					);
				}
			}
			$delete = array();

			// do aktualizacji
			foreach ((array) array_diff_assoc($items, $currItems) as $itemId => $value)
			{
				if (strlen(trim($value)))
				{
					$this->item->update(array('item_text' => $value), "item_id = $itemId AND item_poll = $id");
				}
				else
				{
					$delete[] = $itemId;
				}
			}

			// do usuniecia
			foreach ((array) array_diff_key($currItems, $items) as $itemId => $value)
			{
				$this->item->delete("item_id = $itemId AND item_poll = $id");
			}

			foreach ($delete as $itemId)
			{
				$this->item->delete("item_id = $itemId AND item_poll = $id");

				$sql = "UPDATE poll_item SET item_id = item_id -1 WHERE item_id > $itemId";
				$this->db->query($sql);
			}
		}


		return $id;
	}

	public function get($pollId)
	{
		$result = array();
		$query = $this->select()->where("poll_id = $pollId")->get();

		if (count($query))
		{
			$result = $query->fetchAssoc();
			$result['items'] = $this->item->select('item_id, item_text')->where("item_poll = $pollId")->get()->fetchPairs();
		}

		return $result;
	}
}
?>