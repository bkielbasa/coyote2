<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Form_Poll extends Forms
{
	private $isReady;
	private $enableDelete = false;

	function __construct($action = '', $method = Forms::POST)
	{
		parent::__construct($action, $method);

		$this->setEnableDefaultDecorators(false);
		$this->addDecorator('tag', array('tag' => 'ol', 'placement' => 'WRAP'));
	}

	protected function setIsReady($flag)
	{
		$this->isReady = (bool) $flag;
		return $this;
	}

	protected function isReady()
	{
		return $this->isReady;
	}

	public function setEnableDelete($flag)
	{
		$this->enableDelete = (bool) $flag;
		return $this;
	}

	public function isDeleteEnabled()
	{
		return $this->enableDelete;
	}

	public function renderForm()
	{
		$element = new Form_Element_Text('title');
		$element->setLabel('Tytuł ankiety')
				->addFilter('trim')
				->addFilter('htmlspecialchars')
				->addValidator(new Validate_String(false, 2, 100))
				->setAttribute('maxlength', 100)
				->setOrder(1);

		$this->addElement($element);

		$element = new Form_Element_Textarea('items');
		$element->setLabel('Możliwe odpowiedzi')
				->addFilter('htmlspecialchars')
				->setAttribute('rows', 10)
				->setAttribute('cols', 40)
				->setAttribute('class', 'poll-items')
				->setDescription('Maksymalnie 20 odpowiedzi. Każda w nowej linii. Jeżeli chcesz usunąć pozostaw pustą linię')
				->setOrder(2);

		$this->addElement($element);

		$element = new Form_Element_Text('max');
		$element->setLabel('Możliwe odpowiedzi')
				->setDescription('Podaj liczbę określającą ilość możliwych odpowiedzi')
				->addFilter('int')
				->addValidator(new Validate_Int(false, 1))
				->setAttribute('class', 'poll-max-items')
				->setValue(1)
				->setOrder(3);

		$this->addElement($element);

		$element = new Form_Element_Text('length');
		$element->setLabel('Długość działania')
				->setDescription('Okreś długość działania ankiety (w dniach). 0 oznacza brak terminu ważności')
				->addFilter('int')
				->setValue(0)
				->setOrder(4);

		$this->addElement($element);

		if ($this->isDeleteEnabled())
		{
			$element = new Form_Element_Checkbox('delete');
			$element->setLabel('Usuń ankiete')
					->setOrder(5);

			$this->addElement($element);
		}
		$this->setIsReady(true);
	}

	public function isValid($data = null)
	{
		if (!$this->isReady())
		{
			$this->renderForm();
		}

		return parent::isValid($data);
	}
}
?>