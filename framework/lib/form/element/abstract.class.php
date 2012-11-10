<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

abstract class Form_Element_Abstract
{
	/**
	 * Identyfikuje nazwe elementu
	 */
	protected $name;
	/**
	 * Przechowuje wartosc kontrolki
	 */
	protected $value;
	/**
	 * Przechowuje 'surowa', nieprzefiltrowana wartosc elementu
	 */
	protected $unfilteredValue;
	/**
	 * Etykieta elementu
	 */
	protected $label;
	/**
	 * Opis elementu
	 */
	protected $description;
	/**
	 * Okresla, czy pole jest wymagane (TRUE) czy nie (FALSE).
	 */
	protected $required = false;
	/**
	 * Atrybuty elementu formularza
	 */
	protected $attributes = array();
	/**
	 * Tablica filtrow danego elementu
	 */
	protected $filters = array();
	/**
	 * Tablica walidatorow
	 */
	protected $validators = array();
	/**
	 * Tablica bledow walidacji
	 */
	protected $errors = array();
	/**
	 * Okresla, czy komunikaty maja sie wyswietlac, czy nie
	 */
	protected $enableErrors = true;
	/**
	 * Tablica dodatkowych elementow konfiguracji
	 */
	protected $config = array();
	/**
	 * Okresla, czy domyslne dekoratory maja byc ladowane, czy tez nie
	 */
	protected $enableDefaultDecorators = true;
	/**
	 * Tablica dekoratorow
	 */
	protected $decorators = array();
	/**
	 * Kolejnosc wyswietlania elementu
	 */
	protected $order = 0;

	/**
	 * @param string $name Nazwa pola
	 * @param mixed $attributes Tablica atrybutow
	 * @param mixed $options Tablica dodatkowych opcji
	 */
	function __construct($name, $attributes = array(), $options = array())
	{
		$this->setName($name);
		$this->setAttributes($attributes);
		$this->setOptions($options);
	}

	/**
	 * Okresla, czy ladowac domyslne dekoratory elementu
	 * @param bool $flag
	 */
	public function setEnableDefaultDecorators($flag)
	{
		$this->enableDefaultDecorators = $flag;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isDefaultDecoratorsEnabled()
	{
		return $this->enableDefaultDecorators;
	}

	/**
	 * Laduje domyslne dekoratory dla elementu
	 */
	public function loadDefaultDecorators()
	{
		if (!$this->isDefaultDecoratorsEnabled())
		{
			return;
		}

		if (empty($this->decorators))
		{
			$this->addDecorator('label')
				 ->addDecorator('description', array('tag' => 'small'))
				 ->addDecorator('errors', array('tag' => 'ul'))
				 ->addDecorator('tag', array('tag' => 'li', 'placement' => 'WRAP'));
		}
	}

	/**
	 * Dodaje nowy dekorator dla elementu
	 * @param string $name Nazwa dekoratora
	 * @param mixed $args Dodatkowe atrybuty dla klasy
	 */
	public function addDecorator($name, $args = array())
	{
		$this->decorators[$name] = $args;
		return $this;
	}

	/**
	 * Ustawia dekoratory, usuwajac poprzednie wpisy
	 * @param mixed $decorators
	 */
	public function setDecorators(array $decorators)
	{
		$this->decorators = array();

		foreach ($decorators as $name => $args)
		{
			$this->decorators[$name] = $args;
		}
		return $this;
	}

	/**
	 * Zwraca tablice dekoratorow
	 */
	public function getDecorators()
	{
		return $this->decorators;
	}

	/**
	 * Usuwa dany dekorator
	 * @param string $name Nazwa dekoratora
	 */
	public function removeDecorator($name)
	{
		unset($this->decorators[$name]);
		return $this;
	}

	/**
	 * Usuwa dekoratory
	 */
	public function removeDecorators()
	{
		$this->decorators = array();
		return $this;
	}

	/**
	 * Ustawia nazwe elementu
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Zwraca nazwe elementu
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Ustawia etykiete elementu
	 * @param string $label
	 */
	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}

	/**
	 * Zwraca etykiete elementu
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * Ustawia opis elementu
	 * @param string $description Opis elementu
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * Zwraca opis elementu
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * W tej metodzie przekywana jest wartosc elementu pochodzaca od zadania POST/GET.
	 * Ta metoda wywolywana jest przez klase Forms w momencie gdy uzytkownik zada wyslania zawartosci
	 * formularza do serwera
	 * @param string $value
	 */
	public function setUserValue($value)
	{
		$this->unfilteredValue = &$value;
		return $this->setValue($value);
	}

	/**
	 * Ustawia wartosc dla elementu
	 */
	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * Zwraca wartosc elementu. Jezeli przypisane zostaly filtry, zwrocona wartosc zostaje przefiltrowana
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Zwraca niefiltrowana wartosc
	 * @return string
	 */
	public function getUnfilteredValue()
	{
		return $this->unfilteredValue;
	}

	/**
	 * Ustawia atrybut okreslajacy, czy wartosc danego elementu jest opcjonalna (FALSE) czy wymagana (TRUE)
	 * @param bool $flag
	 */
	public function setRequired($flag)
	{
		$this->required = $flag;
		return $this;
	}

	/**
	 * Zwraca TRUE lub FALSE, w zaleznosci czy element jest wymagany
	 */
	public function isRequired()
	{
		return $this->required;
	}

	/**
	 * Ustawia nowy atrybut xHTML dla elementu
	 * @param string $attr Nazwa atrybuty
	 * @param string $value Wartosc atrybutu
	 */
	public function setAttribute($attr, $value)
	{
		$this->attributes[$attr] = $value;
		return $this;
	}

	/**
	 * Ustawia atrybuty korzystajac z tablicy przekazanej w parametrze metody
	 * @param mixed $attributes
	 */
	public function setAttributes(array $attributes)
	{
		foreach ($attributes as $attr => $value)
		{
			$this->setAttribute($attr, $value);
		}
		return $this;
	}

	/**
	 * Zwraca wartosc atrybutu
	 * @param string $attr Nazwa atrybutu
	 * @param string $default Wartosc domyslna jezeli wartosc dla tego atrybutu nie zostala przypisana
	 */
	public function getAttribute($attr, $default = '')
	{
		if (empty($this->attributes[$attr]))
		{
			return $default ? $default : null;
		}
		return $this->attributes[$attr];
	}

	/**
	 * Zwraca tablice atrybutow
	 */
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

	/**
	 * Atrybuty przekazane w tablicy sa przekazywane do metod
	 * @param mixed $options
	 */
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

	/**
	 * Przypusuje dodatkowa konfiguracje przekazana dla elementu
	 * @param mixed $configs
	 */
	public function setConfig(array $configs)
	{
		foreach ($configs as $key => $value)
		{
			$this->addConfig($key, $value);
		}

		return $this;
	}

	/**
	 * Dodaje nowy, niestandardowy element konfiguracji
	 * @param string $key Klucz
	 * @param string $value Wartosc konfiguracji
	 */
	public function addConfig($key, $value)
	{
		$this->config[$key] = $value;
		return $this;
	}

	/**
	 * Zwraca niestandardowa wartosc konfiguracji
	 * @param string $key
	 * @return string
	 */
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

	/**
	 * Dodaje nowy filtr do kolejki
	 * @param string $filter Nazwa filtra
	 * @param mixed $args Dodatkowe atrybuty dla filtra
	 */
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

	/**
	 * Przypisuje tablice filtrow
	 * @param mixed $filters
	 */
	public function setFilters(array $filters)
	{
		$this->filters = $filters;
		return $this;
	}

	/**
	 * Dodaje walidator do kolejki walidatorow
	 * @param string|object W parametrze mozna przekazac nazwe walidatora lub obiekt klasy
	 * @param mixed Dodatkowe atrybuty dla walidatora
	 */
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

	/**
	 * Ustawia tablice walidatorow
	 * @param mixed $validators
	 */
	public function setValidators($validators)
	{
		$this->validators = $validators;
		return $this;
	}

	/**
	 * Zwraca tablice filtrow dla elementu
	 * @return mixed
	 */
	public function getFilters()
	{
		return $this->filters;
	}

	/**
	 * Zwraca tablice walidatorow
	 */
	public function getValidators()
	{
		$validators = array();

		foreach ($this->validators as $key => $value)
		{
			array_push($validators, $value);
		}
		return $validators;
	}

	/**
	 * Waliduje przekazana wartosc
	 * @param string $value
	 * @return bool
	 */
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

	/**
	 * Zwraca tablice bledow walidacji
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Zwraca TRUE jezeli podczas walidacji wystapily bledy
	 * @return bool
	 */
	public function hasErrors()
	{
		return (bool)sizeof($this->errors);
	}

	/**
	 * @deprecated
	 */
	public function getMessages()
	{
		return $this->getErrors();
	}

	/**
	 * Generuje xHTML dla elementu formularza
	 * @return string
	 */
	public function display()
	{
		/**
		 * Ladowanie domyslnych dekoratorow
		 */
		$this->loadDefaultDecorators();
		$content = $this->getXhtml();

		foreach ($this->getDecorators() as $name => $args)
		{
			if ($name == 'errors' && !$this->isErrorsEnabled())
			{
				continue;
			}
			$className = 'Form_Decorator_' . $name;
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