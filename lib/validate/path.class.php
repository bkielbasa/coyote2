<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Validate_Path extends Validate_Abstract implements IValidate
{
	const INVALID_PATH		=		1;

	protected $templates = array(

			self::INVALID_PATH			=> 'Strona o ścieżce "%value%" już istnieje'
	);
	protected $pageId = null;
	protected $parent = null;

	function __construct($pageId = null, $parent = null)
	{
		$this->setPageId($pageId);
		$this->setParent($parent);
	}

	public function setParent($parent)
	{
		$this->parent = (int)$parent;
		return $this;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function setPageId($pageId)
	{
		$this->pageId = $pageId;
		return $this;
	}

	public function getPageId()
	{
		return $this->pageId;
	}

	public function isValid($value)
	{
		$this->setValue($value);
		$core = &Core::getInstance();

		if (!$this->getParent())
		{
			$parent = (int)$core->input->post('page_parent');
			if ($parent)
			{
				$this->setParent($parent);
			}
		}

		$path = '';
		if ($this->getParent())
		{
			$path = $core->db->query('SELECT GET_LOCATION(?) AS path', $this->getParent())->fetchField('path') . '/';
		}

		$path .= $value;
		$query = $core->db->select('location_page')->from('location')->where("location_text = '$path'")->get();

		if (count($query))
		{
			$id = $query->fetchField('location_page');

			if ($id != $this->getPageId())
			{
				$this->setMessage(self::INVALID_PATH);
			}
		}

		return ! $this->isMessages();
	}
}
?>