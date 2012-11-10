<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Whois extends Adm
{
	function main()
	{
		$ip = $this->get->ip;

		if (($ip2long = ip2long($ip)) === false)
		{
			die('Nieprawidłowy adres IP');
		}

		$host = gethostbyaddr($ip);

		$server = 'whois.arin.net';
		$buffer = '';

		$socket = fsockopen($server, 43, $errorCode, $error, 20);
		if (!$socket)
		{
			throw new Exception('Brak połączenia z serwerem WHOIS');
		}
		else
		{
			fputs($socket, 'n ' . $ip . "\n");
			while (!feof($socket))
			{
				$buffer .= fgets($socket, 10204);
			}
		}
		fclose($socket);
		$server = '';

		if (stripos($buffer, 'RIPE') != false)
		{
			$server = 'whois.ripe.net';
		}
		elseif (strpos($buffer, 'whois.apnic.net') !== false)
		{
			$server = 'whois.apnic.net';
		}
		elseif (strpos($buffer, 'nic.ad.jp') !== false)
		{
			$server = 'whois.nic.ad.jp';
		}
		elseif (strpos($buffer, 'whois.registro.br') !== false)
		{
			$server = 'whois.registro.br';
		}
		$buffer = '';

		if ($server)
		{
			$socket = fsockopen($server, 43, $num, $error, 10);
			if (!$socket)
			{
				throw new Exception('Brak połączenia z serwerem WHOIS');
			}

			fputs($socket, $ip . "\n");
			while (!feof($socket))
			{
				$buffer .= fgets($socket, 10204);
			}

			fclose($socket);

		}
		$result = array();
		$buffer = preg_replace('~#.*~m', '', $buffer);

		foreach (explode("\n", $buffer) as $line)
		{
			@list($key, $value) = explode(':', $line);
			$key = trim($key);
			$value = trim($value);

			$result[$key] = $value;
		}

		echo 'Host: ' . $host . '<br />';
		foreach (array('inetnum', 'netname', 'descr', 'country', 'admin-c', 'tech-c', 'status', 'mnt-by', 'source', 'role', 'address', 'nic-hdl', 'abuse-mailbox', 'person', 'phone') as $element)
		{
			if (isset($result[$element]))
			{
				echo $element . ' <strong>' . $result[$element] . '</strong><br />';
			}
		}

		exit;

	}
}
?>