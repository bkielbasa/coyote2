<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Decorator_Abstract
{
	const APPEND	=	'APPEND';
	const PREPEND	=	'PREPEND';
	const WRAP		=	'WRAP';

	/**
	 * Pozycja w ktorej zostanie umieszczony dekorator
	 */
	protected $placement = self::PREPEND;
	/**
	 * Separator oddzielajacy tresc generatora od tresci przekazanej do klasy
	 */
	protected $separator;
	/**
	 * Znacznik uzyty przez dekorator - np. strong
	 */
	protected $tag;
	/**
	 * Odwolanie do elementu (np. obiektu klasy Form_Element lub Forms)
	 */
	protected $element;
	/**
	 * Atrybuty dla elementu
	 */
	protected $attributes = array();

	/**
	 * @param mixed $options Tablica opcji dekoratora
	 */
	function __construct($options = null)
	{
		if (is_array($options))
		{
			$this->setOptions($options);
		}
	}

	/**
	 * Ustawia opcje dla dekoratora
	 * @param mixed $options
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $option => $value)
		{
			if (method_exists($this, 'set' . $option))
			{
				$this->{'set' . $option}($value);
			}
		}
	}

	/**
	 * Dodaje nowy atrybut dla dekoratora
	 * @param string $attr Nazwa atrybutu
	 * @param string $value Wartosc atrybutu
	 */
	public function addAttribute($attr, $value)
	{
		$this->attributes[$attr] = $value;
		return $this;
	}

	/**
	 * Dodaje nowy atrybut dla dekoratora
	 * @param string $attr Nazwa atrybutu
	 * @param string $value Wartosc atrybutu
	 */
	public function setAttribute($attr, $value)
	{
		$this->attributes[$attr] = $value;
		return $this;
	}

	/**
	 * Ustawia tablice atrybutow dla elementu
	 */
	public function setAttributes(array $attributes)
	{
		$this->attributes = $attributes;
		return $this;
	}

	/**
	 * Zwraca tablice atrybutow
	 * @return mixed
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * Ustawia pozycje tresci dekoratora
	 * @param string $placement
	 */
	public function setPlacement($placement)
	{
		$this->placement = $placement;
		return $this;
	}

	/**
	 * Zwraca pozycje tresci dekoratora
	 * @return string
	 */
	public function getPlacement()
	{
		return $this->placement;
	}

	/**
	 * Zwraca separator 
	 * @return string
	 */
	public function getSeparator()
	{
		return $this->separator;
	}

	/**
	 * Ustawia nazwe klasy dla dekoratora
	 * @param string $class
	 */
	public function setClass($class)
	{
		$this->setAttribute('class', $class);
		return $this;
	}

	/**
	 * Zwraca nazwe klasy dla dekoratora
	 * @return string
	 */
	public function getClass()
	{
		return $this->getAttribute('class');
	}

	/**
	 * Ustawia atrybut ID dla dekoratora
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->setAttribute('id', $id);
		return $this;
	}

	/**
	 * Zwraca atrybut ID dla dekoratora
	 * @return string
	 */
	public function getId()
	{
		return $this->getAttribute('id');
	}

	/**
	 * Ustawia nazwe tagu sluzacego do generowania tresci
	 * @param string $tag
	 */
	public function setTag($tag)
	{
		$this->tag = $tag;
		return $this;
	}

	/**
	 * Zwraca nazwe znacznika xHTML
	 * @return string
	 */
	public function getTag()
	{
		return $this->tag;
	}

	/**
	 * Ustawia element dekorowany (Form lub Form_Element)
	 * @param object
	 */
	public function setElement($element)
	{
		$this->element = $element;
		return $this;
	}

	/**
	 * Zwraca element dekorowany
	 * @return object
	 */
	public function getElement()
	{
		return $this->element;
	}


}
?>