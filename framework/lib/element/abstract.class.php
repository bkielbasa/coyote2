<?php
/**
 * @package Coyote-F
 * @version $Id$
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

abstract class Element_Abstract
{
	protected $name;
	protected $value;
	protected $unfilteredValue;
	protected $label;
	protected $description;
	protected $required = false;
	protected $attributes = array();
	protected $filters;
	protected $validators = array();
	protected $errors = array();
	protected $config = array();
	protected $enableDefaultDecorators = true;
	protected $decorators = array();
	/**
	 * Kolejnosc wyswietlania elementu
	 */
	protected $order = 0;

	function __construct($name, $attributes = array(), $options = array())
	{ 	
		$this->setName($name); 
		$this->setAttributes($attributes);
		$this->setOptions($options);

		$this->loadDefaultDecorators();
	}

	public function setEnableDefaultDecorators($flag)
	{
		$this->enableDefaultDecorators = $flag;
		return $this;
	}

	public function isDefaultDecoratorsEnabled()
	{
		return $this->enableDefaultDecorators;
	}

	public function loadDefaultDecorators()
	{
		if (!$this->isDefaultDecoratorsEnabled())
		{
			return;
		}
		$this->addDecorator('label')
			 ->addDecorator('description', array('tag' => 'small'))
			 ->addDecorator('errors', array('tag' => 'ul'))
			 ->addDecorator('tag', array('tag' => 'li', 'placement' => 'WRAP'));
	}

	public function addDecorator($name, $args = array())
	{
		$this->decorators[$name] = $args;
		return $this;
	}

	public function getDecorators()
	{
		return $this->decorators;
	}

	public function removeDecorator($name)
	{
		unset($this->decorators[$name]);
		return $this;
	}

	public function removeDecorators()
	{
		$this->decorators = array();
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}

	public function getLabel()
	{
		return $this->label;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function setUserValue($value)
	{
		$this->unfilteredValue = &$value;
		return $this->setValue($value);
	}

	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function getUnfilteredValue()
	{
		return $this->unfilteredValue;
	}

	public function setRequired($flag)
	{
		$this->required = $flag;
		return $this;
	}

	public function isRequired()
	{
		return $this->required;
	}

	public function setAttribute($attr, $value)
	{
		$this->attributes[$attr] = $value;
		return $this;
	}

	public function setAttributes(array $attributes)
	{
		foreach ($attributes as $attr => $value)
		{
			$this->setAttribute($attr, $value);
		}
		return $this;
	}

	public function getAttribute($attr, $default = '')
	{
		if (empty($this->attributes[$attr]))
		{
			return $default ? $default : null;
		}
		return $this->attributes[$attr];
	}

	public function getAttributes()
	{
		return $this->attributes;
	}

	public function __set($attr, $value)
	{
		$this->setAttribute($attr, $value);
	}

	public function __get($attr)
	{
		return $this->getAttribute($attr);
	}
	
	public function setOptions(array $options)
	{
		foreach ($options as $option => $args)
		{
			$method = 'set' . $option;
			if (method_exists($this, $method))
			{
				$this->$method($args);
			}
		}
	}

	public function setConfig(array $configs)
	{
		foreach ($configs as $key => $value)
		{
			$this->addConfig($key, $value);
		}
		
		return $this;
	}

	public function addConfig($key, $value)
	{
		$this->config[$key] = $value;
		return $this;
	}

	public function getConfig($key)
	{
		if (isset($this->config[$key]))
		{
			return $this->config[$key];
		}
		else
		{
			return null;
		}
	}

	public function addFilter($filter, $args = array())
	{
		if ($args)
		{
			$this->filters[$filter] = $args;
		}
		else
		{
			$this->filters[] = $filter;
		}
		return $this;
	}

	public function setFilters(array $filters)
	{
		$this->filters = $filters;
		return $this;
	}

	public function addValidator($validator, $args = array())
	{
		if ($validator instanceof IValidate)
		{
			array_push($this->validators, $validator);
		}
		else
		{
			$this->validators[$validator] = $args;
		}
		
		return $this;
	}

	public function setValidators($validators)
	{
		$this->validators = $validators;
		return $this;
	}

	public function getFilters()
	{
		return $this->filters;
	}

	public function getValidators()
	{
		$validators = array();

		foreach ($this->validators as $key => $value)
		{
			array_push($validators, $value);
		}
		return $validators;
	}

	public function isValid(&$value)
	{
		if ($value)
		{ 
			$this->unfilteredValue = $value;

			if ($this->filters)
			{
				$filter = &Load::loadClass('filter'); 
				$filter->setFilters($this->filters);

				$value = $filter->filterData($value);
			}

			$this->value = $value;
		}

		if ($this->isRequired())
		{
			$this->addValidator('NotEmpty');
		}

		if ($this->validators)
		{
			$validate = &Load::loadClass('validate'); 
			$validate->setValidators($this->validators);

			if (!$validate->isValid($value))
			{
				$this->errors = $validate->getMessages();
				return false;
			}
		}

		return true;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function hasErrors()
	{
		return (bool)sizeof($this->errors);
	}

	public function getMessages()
	{
		return $this->getErrors();
	}

	public function display()
	{
		$content = $this->getXhtml();

		foreach ($this->getDecorators() as $name => $args)
		{
			$className = 'Element_Decorator_' . $name;
			$decorator = new $className($args);

			$decorator->setElement($this);
			$content = $decorator->display($content);

			unset($decorator);
		}

		return $content;
	}

	public function __toString()
	{
		return $this->display();
	}

	/**
	 * Wywolanie tej metody blokuje wyswietlanie bledow elementu
	 */
	public function disableErrors()
	{
		$this->enableErrors = false;
		return $this;
	}

	/**
	 * Wywolanie tej metody spowoduje wyswietlanie bledow formularza
	 */
	public function enableErrors()
	{
		$this->enableErrors = true;
	}

	/** 
	 * Zwraca TRUE jezeli wlaczone jest wyswietlanie bledow (FALSE w przeciwnym razie)
	 */
	public function isErrorsEnabled()
	{
		return (bool)$this->enableErrors;
	}

	/**
	 * Ustawia numer okreslajacy kolejnosc wyswietlania elementu (przydatny przy sortowaniu)
	 * @param int $order
	 */
	public function setOrder($order)
	{
		$this->order = $order;
		return $this;
	}

	/**
	 * Zwraca atrybut order
	 */
	public function getOrder()
	{
		return $this->order;
	}
}

?>