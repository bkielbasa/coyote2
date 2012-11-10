<?php
/**
 * @package Coyote-F
 * @author Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 2005-2010 Zend Technologies USA Inc.
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa usuwajaca znaczniki HTML z tekstu. W oryginale uzyta we frameworku Zend
 * Zmodyfikowana na potrzeby frameworka Coyote
 * Podstawowa roznica jest taka, ze znaczniki HTML NIE sa usuwane, tylko zamieniane
 * na &lt; oraz &gt;
 *
 * Innymi slowy filtr dziala tak jak funkcja htmlspecialchars() umozliwiajaca
 * ustawienia wyjatkow
 */
class Filter_Html implements IFilter
{
	/**
	 * Pole okresla, czy filtr ma usuwac zalaczniki
	 */
	private $commentsAllowed = false;
	/**
	 * Tablica dozwolonych znacznikow HTML
	 */
	private $allowedTags = array();
	/**
	 * Tablica dozwolonych atrybutow
	 */
	private $allowedAttributes = array();

	function __construct($options = array())
	{
		if (array_key_exists('allowedTags', $options))
		{
			$this->setAllowedTags($options['allowedTags']);
		}

		if (array_key_exists('allowedAttributes', $options))
		{
			$this->setAllowedAttributes($options['allowedAttributes']);
		}

		if (array_key_exists('commentsAllowed', $options))
		{
			$this->setIsCommentAllowed($options['commentsAllowed']);
		}
	}

	/**
	 * Okresla, czy usuwane (calkowicie) maja byc komentarze (domyslnie tak)
	 * @param bool $flag
	 */
	public function setIsCommentsAllowed($flag)
	{
		$this->commentsAllowed = (bool) $flag;
		return $this;
	}

	/**
	 * Zwraca TRUE jezeli komentarze nie maja byc calkowicie usuniete
	 * @return bool
	 */
	public function isCommentsAllowed()
	{
		return $this->commentsAllowed;
	}

	/**
	 * Ustawia liste dozwolonych znacznikow	HTML
	 * @example
	 * <code>
	 * $this->setAllowedTags(array('b', 'i', 'pre'));
	 * $this->setAllowedTags(array('b' => 'title', 'i', pre')); // dodatkowo zezwala na atrybut "title" w <b>
	 * </code>
	 * @param $allowedTags array|string
	 */
	public function setAllowedTags($allowedTags)
	{
		$this->allowedTags = array();
		if (!is_array($allowedTags))
		{
			$allowedTags = array($allowedTags);
		}

		foreach ($allowedTags as $index => $element)
		{
			if (is_int($index) && is_string($element))
			{
				$tagName = strtolower($element);
				$this->allowedTags[$tagName] = array();
			}

			else if (is_string($index) && (is_array($element) || is_string($element)))
			{
				$tagName = strtolower($index);

				if (is_string($element))
				{
					$element = array($element);
				}
				$this->allowedTags[$tagName] = array();

				foreach ($element as $attribute)
				{
					if (is_string($attribute))
					{
						$attributeName = strtolower($attribute);
						$this->allowedTags[$tagName][$attributeName] = null;
					}
				}
			}
		}
	}

	/**
	 * Zwraca tablice dozwolonych znacznikow HTML
	 */
	public function getAllowedTags()
	{
		return $this->allowedTags;
	}

	/**
	 * Ustawia tablice dozwolonych atrybutow HTML
	 * @param mixed $allowedAttributes
	 */
	public function setAllowedAttributes($allowedAttributes)
	{
		if (!is_array($allowedAttributes))
		{
			$allowedAttributes = array($allowedAttributes);
		}

		foreach ($allowedAttributes as $attribute)
		{
			if (is_string($attribute))
			{
				$attributeName = strtolower($attribute);
				$this->allowedAttributes[$attributeName] = null;
			}
		}

		return $this;
	}

	/**
	 * Zwraca tablice dozwolonych atrybutow HTML
	 */
	public function getAllowedAttributes()
	{
		return $this->allowedAttributes;
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public function filter($value)
	{
		$value = (string) $value;

		if (!$this->isCommentsAllowed())
		{
			while (strpos($value, '<!--') !== false)
			{
				$pos   = strrpos($value, '<!--');
				$start = substr($value, 0, $pos);
				$value = substr($value, $pos);
				$value = preg_replace('/<(?:!(?:--[\s\S]*?--\s*)?(>))/us', '', $value);
				$value = $start . $value;
			}
		}

		$dataFiltered = '';
		preg_match_all('/([^<]*)(<?[^><]*>?)/', (string) $value, $matches);

		foreach	($matches[1] as $index => $preTag)
		{
			if (strlen($preTag))
			{
				$preTag	= str_replace('>', '&gt;', $preTag);
			}
			$tag = $matches[2][$index];

			if (strlen($tag))
			{
				$tagFiltered = $this->filterTag($tag);
			}
			else
			{
				$tagFiltered = '';
			}

			$dataFiltered .= $preTag . $tagFiltered;
		}

		return $dataFiltered;
	}

	protected function filterTag($tag)
	{
		$isMatch = preg_match('~(</?)(\w*)((/(?!>)|[^/>])*)(/?>)~', $tag, $matches);

		if (!$isMatch)
		{
			return str_replace(array('<', '>'),	array('&lt;', '&gt;'), $tag);
		}

		$tagStart	   = $matches[1];
		$tagName	   = strtolower($matches[2]);
		$tagAttributes = $matches[3];
		$tagEnd		   = trim($matches[5]);

		if (!isset($this->allowedTags[$tagName]))
		{
			return str_replace(array('<', '>'), array('&lt;', '&gt;'), $tag);
		}
		$tagAttributes = trim($tagAttributes);
		$filterAttributes = !array_key_exists('*', $this->allowedTags[$tagName]) && !array_key_exists('*', $this->allowedAttributes);

		if (strlen($tagAttributes) && $filterAttributes)
		{
			preg_match_all('/(\w+)\s*=\s*(?:(")(.*?)"|(\')(.*?)\')/s', $tagAttributes, $matches);

			$tagAttributes = '';

			foreach	($matches[1] as	$index => $attributeName)
			{
				$attributeName		= strtolower($attributeName);
				$attributeDelimiter	= empty($matches[2][$index]) ? $matches[4][$index] : $matches[2][$index];
				$attributeValue		= empty($matches[3][$index]) ? $matches[5][$index] : $matches[3][$index];

				if (!array_key_exists($attributeName, $this->allowedTags[$tagName])
					&& !array_key_exists($attributeName, $this->allowedAttributes))
				{
					continue;
				}
				$attributeValue = html_entity_decode($attributeValue, ENT_COMPAT, 'UTF-8');

				if (preg_match('#[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', $attributeValue))
				{
					continue;
				}
				if (preg_match('#[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', $attributeValue))
				{
					continue;
				}
				if (preg_match('#[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', $attributeValue))
				{
					continue;
				}
				if (preg_match('#.*?expression[\x00-\x20]*\([^>]*+>#is', $attributeValue))
				{
					continue;
				}
				if (preg_match('#.*?behaviour[\x00-\x20]*\([^>]*+>#is', $attributeValue))
				{
					continue;
				}
				if (preg_match('#.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#ius', $attributeValue))
				{
					continue;
				}
				if (preg_match('#^data:.*#is', $attributeValue))
				{
					continue;
				}

				$tagAttributes .= " $attributeName=" . $attributeDelimiter
								. $attributeValue .	$attributeDelimiter;
			}
		}

//		usuniecie fragmentu kodu ktory powoduje bledna interpretacje linkow <url=http://foo.com/>foo.com</url> => <url=http://foo.com	/>foo.com</url>
//		if (strpos($tagEnd, '/') !== false)
//		{
//			$tagEnd = " $tagEnd";
//		}

		return $tagStart . $tagName . $tagAttributes . $tagEnd;
	}
}
?>