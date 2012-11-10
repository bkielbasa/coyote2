<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Solr_Document implements IteratorAggregate
{
	private $field = array();
	private $fieldBoosts = array();
	private $documentBoost = false;

	public static function create(array $data)
	{
		$object = new Solr_Document;

		foreach ($data as $key => $value)
		{
			$object->addField($key, $value);
		}
		return $object;
	}

	public function setBoost($boost = false)
	{
		$boost = (float)$boost;

		if ($boost > 0.0)
		{
			$this->documentBoost = $boost;
		}
	}

	public function getBoost()
	{
		return $this->documentBoost;
	}

	public function addField($name, $value, $boost = false)
	{
		if (!isset($this->field[$name]))
		{
			$this->field[$name] = $value;
		}
		elseif (!is_array($this->field[$name]))
		{
			$this->field[$name] = array($this->field[$name]);
			$this->field[$name][] = $value;
		}
		else
		{
			$this->field[$name][] = $value;			
		}

		if ($this->getFieldBoost($name) === false)
		{
			$this->setFieldBoost($name, $boost);
		}
		elseif ((float)$boost > 0.0)
		{
			$this->fieldBoosts[$name] *= (float)$boost;
		}
	}

	public function setField($name, $value, $boost = false)
	{
		$this->field[$name] = $value;
		$this->setFieldBoost($name, $boost);
	}

	public function getField($name, $index = 0)
	{
		if (isset($this->field[$name]))
		{
			if (is_array($this->field[$name]))
			{
				return $this->field[$name][$index];
			}
			else
			{
				return $this->field[$name];
			}
		}

		return false;
	}

	public function getFields()
	{
		return $this->field;
	}

	public function getIterator() 
	{
		return new ArrayIterator($this->field);
    }

	public function __set($name, $value)
	{
		return $this->addField($name, $value);
	}

	public function __get($name)
	{
		return $this->getField($name);
	}

	public function getFieldNames()
	{
		return array_keys($this->field);
	}

	public function getFieldValues()
	{
		return array_values($this->field);
	}

	public function setFieldBoost($name, $boost = false)
	{
		if ((float)$boost > 0.0)
		{
			$this->fieldBoosts[$name] = $boost;
		}
	}

	public function getFieldBoost($name)
	{
		return isset($this->fieldBoosts[$name]) ? $this->fieldBoosts[$name] : false;
	}
}
?>