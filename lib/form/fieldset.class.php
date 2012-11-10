<?php

class Form_Fieldset extends Forms
{
	protected $label;

	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}

	public function getLabel()
	{
		return $this->label;
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
		$element = &parent::createElement($type, $name, $attributes, $options);
		$element->setEnableDefaultDecorators(false);

		return $element;
	}
}
?>