<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Interfejs elementu formularza
 * Kazdy element formularza powinien implementowac ten intefejs
 */
interface IElement
{
	function getXhtml();
}

/**
 * Klasa tworzenia formularzy
 */
class Forms
{
	const POST = 'POST';
	const GET = 'GET';
	const PUT = 'PUT';
	const DELETE = 'DELETE';

	const ENCTYPE_MULTIPART  = 'multipart/form-data';

	/**
	 * Akcja formularza (adres URL)
	 */
	protected $action;
	/**
	 * Metoda (np. POST, GET)
	 */
	protected $method;
	/** 
	 * Atrybut enctype
	 */
	protected $enctype;
	/**
	 * Tablica elementow formularz
	 */
	private $elements = array();
	/**
	 * Atrybuty formularza
	 */
	protected $attributes = array();
	/**
	 * Opcjonalna nazwa szablonu ktora bedzie odpowiedzialna za wyswietlenie zawartosci formularza
	 */
	protected $template;
	/**
	 * Tablica bledow walidacji formularza
	 */
	protected $errors = array();
	/**
	 * Wartosc okresla, czy wyswietlac przy kazdym polu, komunikat bledu 
	 */
	protected $enableErrors = true;
	/**
	 * Tablica nazw dekoratorow dla formularza
	 */
	protected $decorators = array();
	/**
	 * Okresla, czy domyslne dekoratory maja byc ladowane, czy tez nie
	 */
	protected $enableDefaultDecorators = true;

	/**
	 * @param string $action Akcja
	 * @param string $method 
	 */
	function __construct($action = '', $method = self::GET)
	{
		$this->setAction($action);
		$this->setMethod($method);
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
	 * Ladowanie domyslnych dekoratorow
	 */
	public function loadDefaultDecorators()
	{
		if (!$this->isDefaultDecoratorsEnabled())
		{
			return;
		}

		if (empty($this->decorators))
		{
			$this->addDecorator('tag', array('tag' => 'ol'))
				 ->addDecorator('fieldset')
				 ->addDecorator('form');
		}
	}

	/**
	 * Dodanie dekoratora do kolejki
	 * @param string $name Nazwa dekoratora
	 * @param mixed $args Tablica argumentow ktore zostana przekazane do obiektu klasy dekoratora
	 */
	public function addDecorator($name, $args = array())
	{
		$this->decorators[$name] = $args;
		return $this;
	}
	
	/**
	 * Zwraca tablice dekoratorow
	 * @return mixed
	 */
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

	/**
	 * Ustawia akcje dla formularza
	 * @param string 
	 */
	public function setAction($action)
	{
		$this->action = $action;
		return $this;
	}

	/**
	 * Zwraca akcje dla formularza
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Ustawia metode obslugi formularza
	 * @param string $method
	 */
	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * Zwraca metode obslugi
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Ustawia atrybut enctype
	 * @param string $enctype
	 */
	public function setEnctype($enctype)
	{
		$this->enctype = $enctype;
		return $this;
	}

	/**
	 * Zwraca wartosc atrybutu enctype
	 * @return string
	 */
	public function getEnctype()
	{
		return $this->enctype;
	}

	/**
	 * Dodaje element do formularza
	 * @param object $element Obiekt klasy Form_Element
	 * @return object 
	 */
	public function addElement(IElement $element)
	{
		return ($this->elements[$element->getName()] = $element);
	}

	public function addElements($elements)
	{
		if (is_array($elements))
		{
			foreach ($elements as $element)
			{
				$this->addElement($element);
			}
		}
		elseif ($elements instanceof Forms)
		{
			foreach ($elements->getElements() as $element)
			{
				$this->addElement($element);
			}
		}

		return $this;
	}

	/**
	 * Tworzy element i zwraca referencje do obiektu klasy
	 * @param string $type Typ elementu (np. text, password - tworzy element klasy Form_Element_Text, Form_Element_Password)
	 * @param string|null $name Nazwa pola formularza
	 * @param mixed $attributes Atrybuty dla elementu xHTML
	 * @param mixed $options Dodatkowe opcje przekazywane do elementu
	 * @return object
	 */
	public function &createElement($type, $name = null, $attributes = array(), $options = array())
	{
		if (!is_string($type))
		{
			throw new Exception('Type parameter must be string!');
		}
		$class = 'Form_Element_' . $type;

		if (!class_exists($class, true))
		{
			throw new Exception("Class $class does not exists!");
		}

		$this->elements[$name] = new $class($name, $attributes, $options);
		return $this->elements[$name];
	}

	/**
	 * Usuwa element z formularza
	 * @param string $name Nazwa elementu
	 */
	public function removeElement($name)
	{
		unset($this->elements[$name]);
		return $this;
	}

	/**
	 * Usuwa wszystkie elementy formularza
	 */
	public function removeElements()
	{
		$this->elements = array();
	}

	/**
	 * Zwraca tablice zawierajaca elementy formularza
	 * @return mixed
	 */
	public function getElements()
	{
		return $this->elements;
	}

	/**
	 * Zwraca obiekt (element formularza) lub NULL jezeli taki element nie istnieje
	 * @param string $element Nazwa elementu
	 * @return object|null
	 */
	public function getElement($element)
	{
		if (isset($this->elements[$element]))
		{
			return $this->elements[$element];
		}
		return null;
	}

	/**
	 * Zwraca wartosc danego elementu formularza
	 * @param string $name Nazwa elementu
	 * @return string|int
	 */
	public function getValue($element)
	{
		if (isset($this->elements[$element]))
		{
			return $this->getElement($element)->getValue();
		}

		return null;
	}

	/**
	 * Zwraca wartosci elementow formularza w postaci tablicy
	 * @return mixed
	 */
	public function getValues()
	{
		$values = array();

		foreach ($this->getElements() as $name => $element)
		{
			if ($name != '')
			{
				$values[$name] = $element->getValue();
			}
		}

		return $values;
	}

	/**
	 * Zwraca wartosci pol formularza.
	 * UWAGA! Wartosci nie sa filtrowane
	 * @return mixed
	 */
	public function getUnfilteredValues()
	{
		$values = array();

		foreach ($this->getElements() as $name => $element)
		{
			if ($name != '')
			{
				$values[$name] = $element->getUnfilteredValue();
			}
		}

		return $values;
	}

	/**
	 * Zwraca wartosc danego elementu formularza
	 * UWAGA! Wartosc nie sa filtrowana
	 * @param string $name Nazwa elementu
	 * @return string|int
	 */
	public function getUnfilteredValue($name)
	{
		return $this->getElement($name)->getUnfilteredValue();
	}

	/**
	 * Ustawia domyslne wartosci dla formularza
	 * @param mixed $default Tablica asocjacyjna w postaci nazwa pola => wartosc pola
	 */
	public function setDefaults(array $defaults)
	{
		foreach ($this->getElements() as $name => $element)
		{
			if (array_key_exists($name, $defaults))
			{
				$this->setDefault($name, $defaults[$name]);
			}
		}
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function setUserData()
	{
		$this->setUserValues();
	}

	/**
	 * Ustawia nowe pola formularza przeslane w naglowku POST/GET przez uzytkownika
	 */
	public function setUserValues()
	{
		$method = strtolower($this->getMethod());
		$input = &Load::loadClass('input');

		if (!isset($input->$method))
		{
			return false;
		}

		foreach ($this->getElements() as $name => $element)
		{
			$element->setUserValue($input->$method($name));
		}
	}

	/**
	 * Ustawia domyslna wartosc danego elementu formularza
	 * @param string $name Nazwa pola
	 * @param string $value Wartosc dla pola
	 */
	public function setDefault($name, $value)
	{
		if (isset($this->elements[$name]))
		{
			$this->elements[$name]->setValue($value);
		}
	}

	/**
	 * Ustawia atrybut xHTML dla formularza
	 * @param string $attr Atrybut dla formularza
	 * @param string $value Wartosc
	 */
	public function setAttribute($attr, $value)
	{
		$this->attributes[$attr] = $value;
		return $this;
	}

	/**
	 * Ustawia atrybuty dla formualarza
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
	 * Zwraca wartosc podanego atrybutu
	 * @param string $attr Nazwa atrybutu
	 * @param string $default Wartosc domyslna, jezeli dany atrybut nie istnieje
	 * @return string 
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
	 * Ustawia nazwe szablonu
	 * @param string $template Nazwa szablonu z katalogu /template/form/
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
		return $this;
	}

	/**
	 * Zwraca nazwe ustawionego szablonu
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/**
	 * Ustawia obiekt widoku
	 * @param object $view Obiekt klasy View
	 */
	public function setView(View $view)
	{
		$this->view = $view;
	}

	/**
	 * Zwraca przekazany uprzednio obiekt klasy View
	 */
	public function getView()
	{
		return $this->view;
	}

	/**
	 * Generuje kod formularza
	 * @param string|object|null $template W parametrze mozna przekazac obiekt klasy View, nazwe szablonu lub pozostawic puste
	 *
	 * Jezeli parametr $template jest pusty, formularz zostanie wygenerowany na podstawie 
	 * dekoratorow
	 */
	public function display($template = null)
	{
		$this->setAttribute('method', $this->getMethod());
		if ($this->getEnctype())
		{
			$this->setAttribute('enctype', $this->getEnctype());
		}

		$order = array();
		foreach ($this->getElements() as $element)
		{
			$order['order'][$element->getName()] = $element->getOrder();
			$order['key'][$element->getName()] = @$key++;
		}

		if ($order)
		{
			array_multisort($order['order'], SORT_ASC, $order['key'], SORT_ASC, $this->elements);
		}

		/**
		 * Jezeli przekazano pusta wartosc, sprawdzanie, czy wczesniej, przed wywolaniem
		 * tej metody, user zadeklarowal szablon lub widok dla tego formularza
		 */
		if ($template === null)
		{
			if ($this->getTemplate())
			{
				$template = $this->getTemplate();
			}
			elseif ($this->getView())
			{
				$template = $this->getView();
			}
		}

		/**
		 * Warunek zostanie spelniony jezeli user przekazal w parametrze nazwe szablonu (string)
		 * lub obiekt implementujacy interfejs IView. Oznacza to, ze formularz ma zostac wygenerowany
		 * w widoku
		 */
		if ($template != null)
		{			
			if ($template instanceof View)
			{
				// dodatkowe czynnosci jezeli przekazano obiekt 				
			}
			elseif (is_string($template))
			{
				// utworzenie nowej instancji widoku na podstawie nazwy szablonu
				$template = new View('form/' . $template);
			}

			// usuniecie zadeklarowanych wczesniej - dekoratorow. user sam chce miec wplyw
			// na wyglad formularza
			foreach ($this->getElements() as $element)
			{
				$element->removeDecorators();
			}

			$template->form = $this;
			return $template->display(false);
		}
		else
		{
			/**
			 * Ladowanie domyslnych dekoratorow
			 */
			$this->loadDefaultDecorators();
			/**
			 * Generowanie kodu kazdego elementu z osobna przy uzyciu dekoratorow
			 */
			$xhtml = '';
			foreach ($this->getElements() as $element)
			{
				$xhtml .= $element;
			}

			/**
			 * Na koncu pobranie dekoratorow formularza. Nalezy "ubrac" dotychczas
			 * wygenerowany kod xHTML w znacznk <form>
			 */
			foreach ($this->getDecorators() as $name => $args)
			{
				$className = 'Form_Decorator_' . $name;
				$decorator = new $className($args);

				$decorator->setElement($this);
				$xhtml = $decorator->display($xhtml);

				unset($decorator);
			}

			return $xhtml;
		}
	}

	public function __toString()
	{
		return (string) $this->display();
	}

	/**
	 * Przeprowadza proces walidacji formularza na podstawie przekazanych filtrow/walidatorow
	 * @param mixed|null $data Dane do walidacji (moze byc wartosc pusta, wowczas system automatycznie odczyta wartosci
	 */
	public function isValid($data = null)
	{
		if ($data === null)
		{			
			if ($this->getMethod() != self::GET && $this->getMethod() != self::POST)
			{
				return false;
			}

			$input = &Load::loadClass('input');
			if (!$input->isMethod(self::POST))
			{
				return false;
			}
			$method = strtoupper($this->getMethod());
			$data = &$GLOBALS['_' . $method];

			/**
			 * Jezeli user zada walidacji formularza i przekazano dane w naglowku POST/GET
			 * ustawiamy w polach nowe wartosci - te wpisane przez uzytkownika
			 */
			$this->setUserValues();
		}

		foreach ($this->getElements() as $name => $element)
		{ 
			if (isset($data[$name]))
			{
				$value = &$data[$name];
			}
			else
			{ 
				$value = null;
			}

			if (!$this->isErrorsEnabled())
			{
				$element->disableErrors();
			}

			/**
			 * Do walidacji przekazujemy referencje. Dzieki temu mozna wykonac
			 * rowniez filtracje danych
			 */
			if (!$element->isValid($value))
			{ 
				$this->errors[$name] = $element->getErrors();
			}
			unset($value);
		}

		return !$this->hasErrors();
	}

	/**
	 * Wywolanie tej metody blokuje wyswietlanie bledow formularza
	 * @deprecated
	 */
	public function disableMessages()
	{
		return $this->disableErrors();
	}

	/**
	 * Wywolanie tej metody blokuje wyswietlanie bledow formularza
	 */
	public function disableErrors()
	{
		$this->enableErrors = false;
		return $this;
	}

	/**
	 * Wywolanie tej metody spowoduje wyswietlanie bledow formularza
	 * @deprecated
	 */
	public function enableMessages()
	{
		return $this->enableErrors();
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
	 * @deprecated
	 */
	public function isMessagesEnabled()
	{
		return $this->isErrorsEnabled();
	}

	/** 
	 * Zwraca TRUE jezeli wlaczone jest wyswietlanie bledow (FALSE w przeciwnym razie)
	 */
	public function isErrorsEnabled()
	{
		return (bool)$this->enableErrors;
	}

	/** 
	 * Zwraca TRUE jezeli sa jakiekolwiek bledy w walidacji formularza
	 * @deprecated
	 * @return bool
	 */
	public function isMessages()
	{
		return $this->hasErrors();
	}

	/** 
	 * Zwraca TRUE jezeli sa jakiekolwiek bledy w walidacji formularza
	 * @param string|null $field Opcjonalna nazwa pola formularza
	 * @return bool
	 */
	public function hasErrors($field = null)
	{
		if (null !== $field)
		{
			return isset($this->errors[$field]);
		}
		else
		{
			return (bool)$this->errors;
		}
	}

	/**
	 * @deprecated
	 * @return array
	 */
	public function getMessages($name = null)
	{
		return $this->getErrors($name);
	}

	/**
	 * Zwraca tablice komunikatow bledow. Kluczem tablicy jest nazwa pola
	 * Mozliwe jest rowniez zwrocenie komunikatow zwiazanych jedynie z okreslonym polem.
	 * Wowczas nalezy podac w parametrze nazwe tego pola
	 * @param string|null $field Opcjonalna nazwa pola formularza
	 * @return array
	 */
	public function getErrors($field = null)
	{
		if (null !== $field)
		{
			if (isset($this->errors[$field]))
			{
				return $this->errors[$field];
			}
			else
			{
				return array();
			}
		}

		return $this->errors;
	}
}
?>