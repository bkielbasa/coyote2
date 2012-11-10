<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Component_Photo extends Component_Abstract
{
	private $element;
	private $photoWidth;
	private $photoHeight;
	private $backgroundColor;

	public function displayLayout(&$fieldData)
	{
		$sizeLimit = array('500KB' => '0.5MB');
		for ($i = 1; $i <= 50; $i++)
		{
			$sizeLimit[$i . 'MB'] = $i . ' MB';
		}
		$element[0] = new Form_Element_Text('suffix');
		$element[0]->setLabel('Akceptowane rozszerzenia');
		$element[0]->setValue(@$fieldData['suffix']);
		$element[0]->addFilter('strip_tags');

		$element[0]->addDecorator('label', array('title' => 'Akceptowane rozszerzenia. Wpisz po przecinku, np.: jpg,gif,png'))
				   ->addDecorator('description', array('tag' => 'small'))
				   ->addDecorator('errors', array('tag' => 'ul'))
				   ->addDecorator('tag', array('tag' => 'li', 'placement' => 'WRAP'));

		$element[1] = new Form_Element_Select('maxSize');
		$element[1]->setLabel('Maksymalny rozmiar zdjęcia');
		$element[1]->addMultiOptions($sizeLimit);
		$element[1]->setValue(@$fieldData['maxSize']);
		
		$element[1]->addDecorator('label', array('title' => 'Maksymalny rozmiar pliku jaki zostanie zaakceptowany przez system'))
				   ->addDecorator('description', array('tag' => 'small'))
				   ->addDecorator('errors', array('tag' => 'ul'))
				   ->addDecorator('tag', array('tag' => 'li', 'placement' => 'WRAP'));	
		
		
		$element[2] = new Form_Element_Text('width');
		$element[2]->setLabel('Szerokość zdjęcia')->setAttribute('size', 5);
		$element[2]->setValue(@$fieldData['width']);
		$element[2]->addFilter('int');
		
		$element[2]->addDecorator('label', array('title' => 'Szerokość w pikselach. Na podstawie tej wielkości obraz będzie skalowany'))
				   ->addDecorator('description', array('tag' => 'small'))
				   ->addDecorator('errors', array('tag' => 'ul'))
				   ->addDecorator('tag', array('tag' => 'li', 'placement' => 'WRAP'));

		$element[3] = new Form_Element_Text('height');
		$element[3]->setLabel('Wysokość zdjęcia')->setAttribute('size', 5);
		$element[3]->setValue(@$fieldData['height']);
		$element[3]->addFilter('int');
		
		$element[3]->addDecorator('label', array('title' => 'Wysokość wyrażony w pikselach. Na podstawie tej wielkości obraz będzie skaloway'))
				   ->addDecorator('description', array('tag' => 'small'))
				   ->addDecorator('errors', array('tag' => 'ul'))
				   ->addDecorator('tag', array('tag' => 'li', 'placement' => 'WRAP'));
		
		$element[4] = new Form_Element_Text('thumbnailWidth');
		$element[4]->setLabel('Szerokość miniaturki')->setAttribute('size', 5);
		$element[4]->setValue(@$fieldData['thumbnailWidth']);
		$element[4]->addFilter('int');
		
		$element[4]->addDecorator('label', array('title' => 'W formularzu, zdjęcie będzie wyświetlane w tej szerokości'))
				   ->addDecorator('description', array('tag' => 'small'))
				   ->addDecorator('errors', array('tag' => 'ul'))
				   ->addDecorator('tag', array('tag' => 'li', 'placement' => 'WRAP'));
		
		$element[5] = new Form_Element_Text('background');
		$element[5]->setLabel('Kolor tla')->setAttribute('size', 7);
		$element[5]->setValue(def(@$fieldData['background'], ''));
		$element[5]->addFilter('htmlspecialchars');
		
		$element[5]->addDecorator('label', array('title' => 'Jeżeli obraz po skalowaniu nie będzie odpowiadał rozmiarom obrazku, obrazek będzie miał tło podane w tym polu'))
				   ->addDecorator('description', array('tag' => 'small'))
				   ->addDecorator('errors', array('tag' => 'ul'))
				   ->addDecorator('tag', array('tag' => 'li', 'placement' => 'WRAP'));
		
		return $element;		
	}

	public function displayForm(&$data)
	{
		$this->element = new Form_Element_Photo($data['field_name']);
		$this->element->setDestination('tmp/');
		$this->element->setOverwrite(true);
		$this->element->addConfig('thumbnailWidth', def($data['thumbnailWidth'], 120));

		$validator = new Validate_Upload(true, $data['maxSize'], $data['suffix']);
		$validator->setFieldName($data['field_name']);

		$this->photoWidth = $data['width'];
		$this->photoHeight = $data['height'];
		$this->backgroundColor = $data['background'];

		$this->element->addValidator($validator);

		return $this->element;
	}

	public function onSubmit($value)
	{	
		if ($this->input->post->{$this->element->getName() . '_delete'})
		{
			@unlink('store/_a/' . $this->element->getValue());
			$value = '';
		}

		if ($this->element->recive($this->element->getName()))
		{
			if ($this->element->getValue())
			{
				@unlink('store/_a/' . $this->element->getValue());
			}

			$value = uniqid() . '.' . $this->element->getSuffix();
			rename('tmp/' . $this->element->getFileName(), 'store/_a/' . $value);

			if ($this->photoWidth || $this->photoHeight)
			{
				$image = new Image;
				$image->open('store/_a/' . $value);

				if ($image->getHandle())
				{
					$image->thumbnail((int) $this->photoWidth, (int) $this->photoHeight, $this->backgroundColor);
					$image->save('store/_a/' . $value);
				}
				$image->close();
			}
		}

		if (!preg_match('#^[a-z0-9\.]+$#', $value))
		{
			$value = '';
		}	

		return $value;
	}
}
?>