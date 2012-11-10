<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Walidator dla plikow wysylanych poprzez HTTP POST
 */
class Validate_Upload extends Validate_Abstract implements IValidate
{
	const INVALID_SUFFIX		=		1;
	const INVALID_MIME			=		2;
	const INVALID_SIZE			=		3;

	const INI_INVALID_SIZE		=		4;
	const FORM_INVALID_SIZE		=		5;
	const PARTIAL				=		6;
	const NO_FILE				=		7;
	const NO_TMP_DIR			=		8;
	const CANT_WRITE			=		9;
	const ERR_EXTENSION			=		10;
	const INVALID_WIDTH_HEIGHT	=		11;

	protected $optional = true;
	protected $suffix = array();
	protected $mime = array();
	protected $size;
	protected $width;
	protected $height;

	protected $_suffix;
	protected $_mime;
	protected $_size;
	protected $_width;
	protected $_height;

	protected $fieldName;

	protected $invalid = array(

			'type'						=>		array('text/html', 'text/x-delimtext', 'text/javascript', 'text/x-javascript', 'application/x-shellscript', 'application/x-php', 'text/x-php', 'text/x-php','text/x-python', 'text/x-perl', 'text/x-bash', 'text/x-sh', 'text/x-csh', 'text/x-c++', 'text/x-c')
	);

	protected $templates = array(

			self::INVALID_SUFFIX		=>		'Rozszerzenie "%_suffix%" nie jest ackeptowane',
			self::INVALID_MIME			=>		'Typ "%_mime%" tego pliku nie jest akceptowany',
			self::INVALID_SIZE			=>		'Rozmiar pliku nie moze przekraczać %size% bajtów',
			self::INI_INVALID_SIZE		=>		'Rozmiar pliku przekracza rozmiar zadeklarowany w pliku konfiguracyjnym PHP',
			self::FORM_INVALID_SIZE		=>		'Rozmiar pliku nie moze przekraczać %size% bajtów',
			self::PARTIAL				=>		'Plik został przesłany tylko w części',
			self::NO_FILE				=>		'Plik nie został przesłany',
			self::NO_TMP_DIR			=>		'Nie znaleziono katalogu tymczasowego',
			self::CANT_WRITE			=>		'Plik nie może zostać zapisany na dysku',
			self::ERR_EXTENSION			=>		'Rozszerzenie zwrociło bład podczas wysyłania pliku',
			self::INVALID_WIDTH_HEIGHT	=>		'Wymiary obrazu są nieprawidłowe. Maksymalne wymiary to %width%x%height%'
	);

	protected $vars	= array(

			'size'						=> 'size',
			'width'						=> 'width',
			'height'					=> 'height',
			'_width'					=> '_width',
			'_height'					=> '_height',
			'_suffix'					=> '_suffix',
			'_mime'						=> '_mime'
	);

	function __construct($optional = true, $filesize = 0, $suffix = null, $mime = null, $width = null, $height = null)
	{
		$this->setOptional($optional);
		$this->setFileSize($filesize);
		$this->setSuffix($suffix);
		$this->setMime($mime);
		$this->setWidth($width);
		$this->setHeight($height);
	}

	public function setOptional($optional)
	{
		$this->optional = $optional;
		return $this;
	}

	public function setSuffix($suffix)
	{
		if (!$suffix)
		{
			return $this;
		}
		if (is_string($suffix))
		{
			$suffix = explode(',', $suffix);
		}
		$this->suffix = array_map('strtolower', $suffix);
		return $this;
	}

	public function setMime($mime)
	{
		if (!$mime)
		{
			return $this;
		}
		if (is_string($mime))
		{
			$mime = explode(',', $mime);
		}
		$this->mime = array_map('strtolower', $mime);
		return $this;
	}

	public function setFileSize($size)
	{
		if (is_string($size))
		{
			$type  = trim(substr($size, -2, 1));
			$value = substr($size, 0, -2);

			switch (strtoupper($type))
			{
				case 'G':
					$value *= (1024 * 1024 * 1024);
					break;
				case 'M':
					$value *= (1024 * 1024);
					break;
				case 'K':
					$value *= 1024;
					break;
				default:
					break;
			}
			$this->size = $value;

		}
		else
		{
			$this->size = $size;
		}

		return $this;
	}

	public function setWidth($width)
	{
		$this->width = $width;
		return $this;
	}

	public function setHeight($height)
	{
		$this->height = $height;
		return $this;
	}

	public function setFieldName($fieldName)
	{
		$this->fieldName = $fieldName;
		return $this;
	}

	public function getFieldName()
	{
		return $this->fieldName;
	}

	/**
	 * Walidacja danych
	 * @value Pole w tablicy $_FILES
	 * @return bool
	 */
	public function isValid($value)
	{
		if ($value == null)
		{
			/**
			 * Uzytkownik moze przypisac nazwe pola (klucza) z tablicy $_FILES dzieki metodzie setFieldName()
			 * Jezeli jednak wartosc ta jest pusta, bierzemy pierwsza "z brzegu"
			 */
			if (!$value = $this->getFieldName())
			{
				$value = key($_FILES);
			}
		}
		$content = &$_FILES[$value];

		if (!$content['name'] && $this->optional)
		{
			return true;
		}
		switch ($content['error'])
		{
			case UPLOAD_ERR_INI_SIZE:
				$this->error($value, self::INI_INVALID_SIZE);
			break;

			case UPLOAD_ERR_FORM_SIZE:
				$this->error($value, self::FORM_INVALID_SIZE);
			break;

			case UPLOAD_ERR_PARTIAL:
				$this->error($value, self::PARTIAL);
			break;

			case UPLOAD_ERR_NO_FILE:
				$this->error($value, self::NO_FILE);
			break;

			case UPLOAD_ERR_NO_TMP_DIR:
				$this->error($value, self::NO_TMP_DIR);
			break;

			case UPLOAD_ERR_CANT_WRITE:
				$this->error($value, self::CANT_WRITE);
			break;

			case UPLOAD_ERR_EXTENSION:
				$this->error($value, self::ERR_EXTENSION);
			break;
		}
		$this->_suffix = strtolower(pathinfo($content['name'], PATHINFO_EXTENSION));

		if ($this->suffix && !in_array($this->_suffix, $this->suffix))
		{
			$this->error($value, self::INVALID_SUFFIX);
		}
		if ($this->mime && !in_array($content['type'], $this->mime))
		{
			$this->_mime = $content['type'];
			$this->error($value, self::INVALID_MIME);
		}
		if (in_array($content['type'], $this->invalid['type']))
		{
			$this->_mime = $content['type'];
			$this->error($value, self::INVALID_MIME);
		}
		if ($this->size && $content['size'] > $this->size)
		{
			$this->_size = $content['size'];
			$this->error($value, self::INVALID_SIZE);
		}

		if ($this->width || $this->height)
		{
			/* pobranie rozmiarow obrazka (jezeli plik jest obrazkiem) */
			if ($image = @getimagesize($content['tmp_name']))
			{
				$this->_width = $image[0];
				$this->_height = $image[1];
				if ($image[0] > $this->width || $image[1] > $this->height)
				{
					$this->error($value, self::INVALID_WIDTH_HEIGHT);
				}
			}
		}

		return !$this->isMessages();
	}

	public function error($name, $message)
	{
		$this->setValue($name);
		$this->setMessage($message);
	}
}
?>