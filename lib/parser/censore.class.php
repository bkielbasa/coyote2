<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Parser sluzacy do zamiany niecenzuralnych slow w tekscie
 */
class Parser_Censore implements Parser_Interface
{
	private $words = array();
	private $hasBuild = false;

	private function getWords()
	{
		if (!$this->hasBuild)
		{
			$core = &Core::getInstance();
			$query = $core->db->select()->get('censore');

			foreach ($query as $row)
			{
				$row['censore_text'] = '#(?<![\p{L}\p{N}_])' . str_replace('\*', '\p{L}*', preg_quote($row['censore_text'], '#')) . '(?![\p{L}\p{N}_])#iu';

				$this->words[$row['censore_text']] = $row['censore_replacement'];
			}

			$this->hasBuild = true;
		}

		return (array) $this->words;
	}

	public function parse(&$content, Parser_Config_Interface &$config)
	{
		if (count($this->getWords()))
		{
			$code = Core::getInstance()->parser->extract('code|tt|kbd|samp|a');
			$img = Core::getInstance()->parser->extract('img', Parser::SINGLE);

			$content = preg_replace(array_keys($this->getWords()), array_values($this->getWords()), $content);

			Core::getInstance()->parser->retract($img);
			Core::getInstance()->parser->retract($code);
		}
	}
}

?>