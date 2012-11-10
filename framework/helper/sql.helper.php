<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Helper SQL
 */
class Sql
{
	/**
	 * Import instrukcji SQL z pliku do bazy
	 * @param string $path Sciezka do pliku *.sql
	 */
	function import($path)
	{
		$delimiter = ";";

		if (!file_exists($path))
		{
			throw new FileNotFoundException("Podany plik ($path) nie istniej!!");
		}
		$core = &Core::getInstance();

		$lines_arr = file($path); 
		$query = '';

		foreach ($lines_arr as $line)
		{
			$line = rtrim($line);
			// pozbycie się nie potrzebnych linijek;
			// @todo Kod nie jest odporny na komentarze ktore nie sa pierwszymi znakami linii
			if (strncmp($line, '--', 2) == 0)
			{
				continue;
			}
			if (strncmp($line ,'/*', 2) == 0)
			{
				continue;
			}
			
			$line = ' ' . ($line) . "\n";
			
			if (preg_match("/delimiter (.*)$/i", $line, $matches)) 
			{
				$delimiter = $matches[1];

				$line = trim(str_replace($matches[0], '', $line));
				continue;			
			} 
			$query .= $line;
					
			if (preg_match('#' . preg_quote($delimiter) . '$#is', $line))
			{ 
				$query = preg_replace('#' . preg_quote($delimiter) . '$#is', '', $query);

				if ($query) 
				{ 
					$core->db->query($query);
					$query = '';
				}
			}				
		}
	}
}

?>