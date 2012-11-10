<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Biblioteka testowa
 */
class Image
{
	const JPG			=	'jpg';
	const JPEG			=	'jpeg';
	const GIF			=	'gif';
	const PNG			=	'png';
	
	private $handle;
	private $width;
	private $height;
	private $imageType;
	
	function __construct($fileName = '')
	{
		if ($fileName)
		{
			$this->open($fileName);
		}
	}
	
	function __destruct()
	{
		if ($this->handle)
		{
			$this->close();
		}
	}
	
	public function setImageType($imageType)
	{
		$this->imageType = $imageType;
		return $this;
	}
	
	public function getImageType()
	{
		return $this->imageType;
	}
	
	public function create($imageType, $width, $height)
	{
		$this->imageType = $imageType;
		$this->handle = imagecreatetruecolor($width, $height);

		$this->width = $width;
		$this->height = $height;
		return $this;
	}

	/**
	 * Otwarcie obrazu
	 * @param string $fileName Sciezka/nazwa pliku
	 */
	public function open($fileName)
	{
		if (!file_exists($fileName))
		{
			throw new Exception("$fileName does not exist");
		}
		
		$this->imageType =  strtolower(end(explode('.', $fileName)));
		list($this->width, $this->height, , ,) = @getimagesize($fileName);

		switch ($this->imageType)
		{
			case 'jpeg':
			case 'jpg':				
				$this->handle = @imagecreatefromjpeg($fileName);  
			break;

			case 'gif':
				$this->handle = @imagecreatefromgif($fileName);
			break;

			case 'png':
				$this->handle = @imagecreatefrompng($fileName);
			break;
			
			default:
				throw new Exception('Unsupported image format');
		}
	}

	public function getHandle()
	{
		return $this->handle;
	}

	/**
	 * Zamyka uchwyt i konczy prace z obrazem
	 */
	public function close()
	{
		@imagedestroy($this->handle);
	}
	
	/**
	 * Zwraca szerokosc obrazu
	 * @return int
	 */
	public function getWidth()
	{
		return $this->width;
	}
	
	/**
	 * Zwraca wysokosc obrazu
	 * @return int
	 */
	public function getHeight()
	{
		return $this->height;
	}
	
	/**
	 * Zmiara rozmiaru obrazu do wartosci podanych w parametrach
	 * @param int $width 
	 * @param int $height
	 */
	public function resize($width, $height)
	{
		$image = @imagecreatetruecolor($width, $height);
		
		if ($this->imageType == 'png')
		{
			$background = imagecolorallocate($image, 0, 0, 0);
			imagecolortransparent($image, $background);
			imagealphablending($image, false);
			imagesavealpha($image, true);
		}
		
		@imagecopyresampled($image, $this->handle, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

		$this->handle = $image;
		$this->width = $width;
		$this->height = $height;
	}
	
	/**
	 * Konwersja do systemu szestnastkowego
	 * @param int $red 
	 * @param int $green
	 * @param int $blue
	 * @return string
	 */
	public function rgb2hex($red, $green, $blue)
	{
		 return sprintf('#%02s%02s%02s', dechex($red), dechex($green), dechex($blue));
	}
	
	/**
	 * Konwersja liczby szestnastkowej okreslajacej kolor, do tablicy liczb RGB
	 * @param string $hex np. #ccc lub #f8f8f8
	 * @return array
	 */
	public function hex2rgb($hex)
	{
		if ($hex{0} == '#')
		{
			$hex = substr($hex, 1);
		}
		if (strlen($hex) == 6)
		{
			list($red, $green, $blue) = array(substr($hex, 0, 2), substr($hex, 2, 2), substr($hex, 4, 2));
		}
		elseif (strlen($hex) == 3)
		{
			list($red, $green, $blue) = array($hex[0] . $hex[0], $hex[1] . $hex[1], $hex[2] . $hex[2]);
		}
		else
		{
			return false;
		}
		
		return array(hexdec($red), hexdec($green), hexdec($blue));
	}
	
	/**
	 * Metoda generuje miniature o podanych rozmiarach
	 * @param int $width Szerokosc w px
	 * @param int $height Wysokosc w px
	 * @param strig $color Kolor tla dla miniatury
	 */
	public function thumbnail($width, $height, $color = '#FFF')
	{
		// jezeli wysokosc jest MNIEJSZA niz szerokosc		
		// obraz poziomy
		if ($this->height < $this->width)
		{
			$ratio = $this->getHeight() / $this->getWidth();
			$cHeight = $width * $ratio;
			
			$this->resize($width, $cHeight);
		}
		// obraz pionowy
		else
		{
			$ratio = $this->getWidth() / $this->getHeight();
			$cWidth = $height * $ratio;
			
			$this->resize($cWidth, $height);			
		}
		
		$output = imagecreatetruecolor($width, $height);
		
		if (!$color && $this->imageType == 'png')
		{
			imagesavealpha($output, true);
			$transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);

			imagefill($output, 0, 0, $transparent);
		}
		else
		{
			list($red, $green, $blue) = $this->hex2rgb($color);
			
			$color = imagecolorallocate($output, $red, $green, $blue);		
			imagefill($output, 0, 0, $color);
		}
				
		imagecopy($output, $this->handle, round(($width - $this->getWidth()) / 2), round(($height - $this->getHeight()) / 2), 0, 0, $this->getWidth(), $this->getHeight());

		$this->handle = $output;		
	}

	/**
	 * Metoda generuje miniature o podanych rozmiarach, skaluj�c i przycinaj�c do okre�lonego 
	 * rozmiaru, co pozwala na unikni�cie t�a wok� przeskalowanego obrazu je�li obraz 
	 * wej�ciowy jest wystarczaj�co du�y.
	 * @param int 	$width Szerokosc w px
	 * @param int 	$height Wysokosc w px
	 * @param strig $color Kolor tla dla miniatury
	 */
	public function thumbnailFilled($width, $height, $color = '#FFF')
	{
		if ($this->getWidth() > $this->getHeight()) 
		{
			$cWidth = (int) round($this->getWidth() * $height / $this->getHeight());
			$cHeight = $height;
		} 
		else 
		{
			$cWidth = $width;
			$cHeight = (int) round($this->getHeight() * $width / $this->getWidth());
		}
		
		$this->resize($cWidth, $cHeight);
		
		$offsetX = (int) round(($width - $cWidth) / 2);
		$offsetY = (int) round(($height - $cHeight) / 2);
		
		/* create img with background */
		$output = imagecreatetruecolor($width, $height);
		
		list($red, $green, $blue) = $this->hex2rgb($color);
		
		$color = imagecolorallocate($output, $red, $green, $blue);
		imagefill($output, 0, 0, $color);
		imagecopy($output, $this->handle, $offsetX, $offsetY, 0, 0, $this->getWidth(), $this->getHeight());
		
		$this->handle = $output;

		$this->width = $width;
		$this->height = $height;
	}
	
	/**
	 * Zapis obrazu do pliku
	 * @param $fileName Nazwa (sciezka) pliku
	 */
	public function save($fileName)
	{
		switch ($this->imageType)
		{
			case 'jpg':
			case 'jpeg':	
				 @imagejpeg($this->handle, $fileName, 80);
			break;

			case 'gif':
				@imagegif($this->handle, $fileName);
			break;

			case 'png':
				@imagepng($this->handle, $fileName);
			break;
		}
	}

	/**
	 * Skalowanie otwartego obrazu
	 * Metoda zmienia obraz dokladnie do takich rozmiarow jakie zostaly podane 
	 * w parametrach. Jezeli obraz "wystaje" poza wyznaczone ramy - zostaje przyciety
	 * @param int $width Szerokosc nowego obrazu
	 * @param int $height Wysokosc nowego obrazu
	 * @deprecated
	 */
	public function scale($width, $height, $defaultXOffset = null, $defaultYOffset = null)
	{
		if ($this->height < $this->width)
		{
			$ratio = (double)($this->height / $height);
			$cWidth = round($width * $ratio);

			if ($cWidth > $this->width)
			{
				$ratio = (double)($this->width / $width);
				$cWidth = $this->width;
				$cHeight = round($height * $ratio);
				$xOffset = $defaultXOffset === null ? 0 : $defaultXOffset;
				$yOffset = $defaultYOffset === null ? round(($this->width - $cHeight) / 2) : $defaultYOffset;
			}
			else
			{
				$cHeight = $this->height;
				$xOffset = $defaultXOffset === null ? round(($this->width - $cWidth) / 2) : $defaultXOffset;
				$yOffset = $defaultYOffset === null ? 0 : $defaultYOffset;
			}
		}
		else
		{
			$ratio = (double)($this->width / $width);
			$cHeight = round($height * $ratio);

			if ($cHeight > $this->height)
			{
				$ratio = (double)($this->height / $height);
				$cHeight = $this->height;
				$cWidth = round($width * $ratio);
				$xOffset = $defaultXOffset === null ? round(($this->width - $cWidth) / 2) : $defaultXOffset;
				$yOffset = $defaultYOffset === null ? 0 : $defaultYOffset;
			}
			else
			{
				$cWidth = $this->width;

				$xOffset = $defaultXOffset === null ? 0 : $defaultXOffset;
				$yOffset = $defaultYOffset === null ? round(($this->height - $cHeight) / 2) : $defaultYOffset;
			}
		}

		$thumbnail = imagecreatetruecolor($width, $height);
		imagecopyresampled($thumbnail, $this->handle, 0, 0, $xOffset, $yOffset, $width, $height, $cWidth, $cHeight);
		
		$this->handle = $thumbnail;
		$this->width = $width;
		$this->height = $height;
	}

	/**
	 * @deprecated
	 */
	public function crop($width, $height)
	{
		$thumbnail = imagecreatetruecolor($width, $height);
		imagecopy($thumbnail, $this->handle, 0, 0, 0, 0, $this->width, $this->height);

		$this->handle = $thumbnail;
		$this->width = $width;
		$this->height = $height;
	}
	
	/**
	 * @deprecated
	 */
	public function crop_a($x, $y, $width, $height)
	{
		$thumbnail = imagecreatetruecolor($width, $height);
		imagecopyresampled($thumbnail, $this->handle, 0, 0, $x, $y, $width, $height, $this->width, $this->height);

		$this->handle = $thumbnail;
		$this->width = $width;
		$this->height = $height;
	}
		
	/**
	 * @deprecated
	 */
	public function width()
	{
		return $this->width;
	}

	/**
	 * @deprecated
	 */
	public function height()
	{
		return $this->height;
	}
}
?>