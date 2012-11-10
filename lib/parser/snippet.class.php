<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Parser_Snippet implements Parser_Interface
{
	public function parse(&$content, Parser_Config_Interface &$config)
	{
		preg_match_all("#{{Snippet:(.*?)(\?(.*))*}}#i", $content, $matches);
		if (!$matches[0])
		{
			return;
		}
		$cache = &Load::loadClass('cache');
		if (!$snippets = $cache->get('_snippet'))
		{
			$snippets = $this->loadSnippets();
			$cache->put('_snippet', $snippets);
		}

		for ($i = 0, $limit = sizeof($matches[0]); $i < $limit; $i++)
		{
			$snippetName = strtolower($matches[1][$i]);
			if (!isset($snippets[$snippetName]))
			{
				continue;
			}
			$snippetContent = '';

			$className = strtolower($snippets[$snippetName]['class']);
			parse_str($matches[3][$i], $args);

			if ($className)
			{
				if (Load::fileExists('lib/snippet/' . $className . '.class.php'))
				{				
					$className = 'Snippet_' . $className;
					$snippet = new $className($args);

					ob_start();
					$snippet->display();

					$snippetContent = ob_get_contents();
					ob_end_clean();
				}
			}
			else
			{
				ob_start();

				$snippetContent = Text::evalCode($snippets[$snippetName]['content']);
				$className = 'Snippet_' . $snippetName;

				if (class_exists($className, false))
				{
					$snippet = new $className($args);

					if (is_subclass_of($snippet, 'Snippet'))
					{
						$snippet->display();
					}
				}
				
				if (empty($snippetContent))
				{
					$snippetContent = ob_get_contents();
				}
				ob_end_clean();
			}

			$content = str_replace($matches[0][$i], $snippetContent, $content);
		}
	}

	private function loadSnippets()
	{
		$snippet = new Snippet_Model;
		$query = $snippet->select()->get();
		$result = array();

		foreach ($query as $row)
		{
			$result[strtolower($row['snippet_name'])] = array(

				'class'			=> $row['snippet_class'],
				'content'		=> $row['snippet_content']
			);
		}

		return $result;
	}
}
?>