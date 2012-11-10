<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Access Control List
 */
class Acl
{
	/**
	 * Access Control Objects 
	 */
	public $aco = array();
	/**
	 * Access Request Objects
	 */
	public $aro;

	/**
	 * Dodaje nowego uzytkownika
	 * @param string $caption Nazwa uzytkownika
	 * @param string|null $parent Nazwa rodzica (grupa). Moze to byc tablica rodzicow
	 */
	public function create($caption, $parent = null)
	{
		if ($parent != null)
		{
			if (!is_array($parent))
			{
				$parent = array($parent);
			}

			foreach ($parent as $id)
			{
				if (!isset($this->aro[$id]))
				{
					trigger_error("Group $parent does not exists!", E_USER_ERROR);
				}
				array_push($this->aro[$id]['children'], $caption);
			}
		}

		$this->aro[$caption] = array(
			'caption'	=> $caption,
			'parent'	=> $parent,
			'children'	=> array()
		);	
	}

	/**
	 * Ustaw allow albo deny dla danego obiektu
	 * @param string $role User (ARO)
	 * @param string $object Obiekt (ACO)
	 * @param bool $allow TRUE lub FALSE
	 * @private
	 */
	public function set($role, $object, $allow)
	{
		if (!isset($this->aco[$role]))
		{
			$this->aco[$role] = array($object => $allow);
		}
		else
		{ 
			if (isset($this->aco[$role][$object]))
			{
				$this->aco[$role][$object] = $allow;
			}
			else
			{
				$this->aco[$role] += array($object => $allow);
			}
		}
	}

	/**
	 * Allow 
	 */
	public function allow($role, $object)
	{
		$this->set($role, $object, true);
	}

	/**
	 * Deny
	 */
	function deny($role, $object)
	{ 
		$this->set($role, $object, false);
	}

	/**
	 * Check is allowed
	 * @param string $role ARO
	 * @param string $object ACO
	 * @return bool
	 */
	public function get($role, $object)
	{ 
		$result = null;
		$queue = array($role);

		while ($result === null || count($queue) > 0)
		{
			$role = array_shift($queue);
			// jezeli taki user nie istnieje... pozwalamy!
			if (!isset($this->aro[$role]))
			{
				$result = true;
			}
			// jezeli ustalone jest prawo dla danego usera i roli - zwracamy wartosc
			else if (isset($this->aco[$role][$object]))
			{
				$result = $this->aco[$role][$object];
				if ($result)
				{
					break;
				}
			}
			// sprawdzamy, czy istnieje rodzic. jezeli nie - zwracamy true
			else
			{
				if (!$this->aro[$role]['parent'])
				{ 
					$result = true;
				}
				else
				{
					$role = $this->aro[$role]['parent'];
					$queue = array_merge($queue, $role);
				}
			}
		}
		return ($result);		
	}	
}
?>