<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Validate_News extends Validate_Abstract implements IValidate
{
	const DUPLICATE				=			1;

	protected $templates = array(


			self::DUPLICATE		=>			'URL "%value%" znajduje się już w katalogu nowości'
	);

	public function isValid($value)
	{
		$this->setValue($value);

		$news = new News_Model;
		$query = $news->getByUrl($value);

		if (count($query))
		{
			$this->setError(self::DUPLICATE);
			return false;
		}

		return true;
	}
}
?>