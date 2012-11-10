<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa umozliwia wysylanie e-maili, rowniez z zalacznikami
 */
class Email
{
	/**
	 * Adresy e-mail odbiorcow wiadomosci
	 */
	private $to = array();
	/**
	 * Tablica naglowkow wiadomosci
	 */
	private $headers = array();
	/**
	 * Temat e-maila
	 */
	private $subject;
	/**
	 * Kodowanie znakow wiadomosci
	 */
	private $charset = 'utf-8';
	/**
	 * Dane przekazane do szablonu e-maila
	 */
	private $data = array();
	/**
	 * Lista zalacznikow dodawanych do e-maila
	 */
	private $attachment = array();
	/**
	 * Tresc wiadomosci
	 */
	private $message;

	/**
	 * Typ wiadomosci
	 */
	private $contentType = 'text/plain';
	/**
	 * Wewnetrzy licznik wysylanych emaili
	 */
	private $iterator = 0;

	private $replacement = array();

	/**
	 * Metoda ustawia kodowanie dla e-maila
	 * @param string $charset
	 */
	public function setCharset($charset)
	{
		$this->charset = $charset;
	}

	/**
	 * Pobiera kodowanie znakow dla e-maila
	 * @return string
	 */
	public function getCharset()
	{
		return $this->charset;
	}

	/**
	 * Umozliwia nadanie nadawcy wiadomosci
	 *
	 * Metoda moze byc wywolywana wielokrotnie jesli wysylamy do wielu
	 * odbiorcow:
	 * <code>
	 * for ($i = 0; $i < 2; $i++)
	 * {
	 *		$this->email->assign('HELLO', 'Witaj ' . $i);
	 *		$this->email->to('adam' . $i . '@boduch.net');
	 * }
	 * $this->email->send();
	 * </code>
	 * @param string $email Adres e-mail
	 * @param string $name Nazwa uzytkownika (opcjonalnie)
	 * @deprecated
	 */
	public function to($email, $name = '')
	{
		return $this->addRecipient($email, $name);
	}

	/**
	 * Umozliwia nadanie nadawcy wiadomosci
	 *
	 * Metoda moze byc wywolywana wielokrotnie jesli wysylamy do wielu
	 * odbiorcow:
	 * <code>
	 * for ($i = 0; $i < 2; $i++)
	 * {
	 *		$this->email->assign('HELLO', 'Witaj ' . $i);
	 *		$this->email->addRecipient('adam' . $i . '@boduch.net');
	 * }
	 * $this->email->send();
	 * </code>
	 * @param string $email Adres e-mail
	 * @param string $name Nazwa uzytkownika (opcjonalnie)
	 */
	public function addRecipient($email, $name = '')
	{
		if ($name)
		{
			$this->to[$name] = $email;
		}
		else
		{
			$this->to[] = $email;
		}
	}

	/**
	 * Ustawia odbiorcow danego e-maila
	 * @param mixed $recipient Tablica zawierajaca adresy e-mail oraz ewentualne imiona odbiorcow
	 * @return mixed
	 */
	public function setRecipient($recipient)
	{
		foreach ($recipient as $name => $email)
		{
			$this->addRecipient($email, $name);
		}

		return $this;
	}

	/**
	 * Pobiera adres e-mail odbiorcy wiadomosci
	 */
	public function getRecipient()
	{
		return $this->to;
	}

	/**
	 * Sluzy do ustalenia nadawcy e-maila
	 * @param string $email Adres e-mail
	 * @param string $name Nazwa (opcjonalnie)
	 * @deprecated
	 */
	public function from($email, $name = '')
	{
		return $this->setFrom($email, $name);
	}

	/**
	 * Sluzy do ustalenia nadawcy e-maila
	 * @param string $email Adres e-mail
	 * @param string $name Nazwa (opcjonalnie)
	 */
	public function setFrom($email, $name = '')
	{
		if ($name)
		{
			$name = '"' . $name . '"';
		}
		$this->addHeader('From', $name . '<' . $email . '>');
		$this->addHeader('Return-Path', $email);
		return $this;
	}

	/**
	 * Umozliwia nadanie dodatkowych parametrow naglowka w e-mailu
	 * @param string $header Nazwa naglowka
	 * @param string $value Zawartosc naglowka
	 * @deprecated
	 */
	public function setHeader($header, $value)
	{
		return $this->addHeader($header, $value);
	}

	/**
	 * Umozliwia nadanie dodatkowych parametrow naglowka w e-mailu
	 * @param string $header Nazwa naglowka
	 * @param string $value Zawartosc naglowka
	 */
	public function addHeader($header, $value)
	{
		$this->headers[$header] = $value;
		return $this;
	}

	/**
	 * Zwraca tablice z przekazanymi naglowkami
	 * @return mixed
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Ustawia naglowek Content-Type dla e-maila
	 * @param string $contentType Naglowek Content-Type
	 * @return mixed
	 */
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;
		return $this;
	}

	/**
	 * Pobiera wartosc Content-Type
	 * @return string
	 */
	public function getContentType()
	{
		return $this->contentType;
	}

	/**
	 * Ustawia temat wiadomosci
	 * @param string $subject
	 * @deprecated
	 */
	public function subject($subject)
	{
		return $this->setSubject($subject);
	}

	/**
	 * Ustawia temat wiadomosci
	 * @param string $subject
	 */
	public function setSubject($subject)
	{
		$this->subject = str_replace("\n", '', $subject);
		return $this;
	}

	/**
	 * Sluzy do ustawiania tresci e-maila
	 * @param string $message Tresc
	 * @deprecated
	 */
	public function message($message)
	{
		return $this->setMessage($message);
	}

	/**
	 * Sluzy do ustawiania tresci e-maila
	 * @param string $message Tresc
	 */
	public function setMessage($message)
	{
		$this->message = stripslashes(rtrim(str_replace("\r", "", $message)));

		if (strpos($this->getContentType(), 'plain') !== false)
		{
			$this->message = htmlspecialchars_decode($this->message);
		}

		return $this;
	}

	/**
	 * Tresc e-maila pobierana jest z zewnetrznego pliku
	 * @param string $filename Pelna sciezka do pliku z trescia e-maila
	 * @deprecated
	 */
	public function load($filename)
	{
		return $this->loadMessage($filename);
	}

	/**
	 * Tresc e-maila pobierana jest z zewnetrznego pliku
	 * @param string $filename Pelna sciezka do pliku z trescia e-maila
	 */
	public function loadMessage($filename)
	{
		if (!file_exists($filename))
		{
			throw new FileNotFoundException("File $filename cannot be found");
		}
		$this->setMessage(file_get_contents($filename));

		return $this;
	}

	/**
	 * Przypisanie danych do widoku
	 * @param string $key Klucz (nazwa parametru przekazywanego do widoku)
	 * @param string | mixed $value Wartosc
	 */
	public function assign($key, $value = '')
	{
		if (!is_array($key))
		{
			$this->data[$key][] = $value;
		}
		else
		{
			while (list($k, $v) = each($key))
			{
				$this->data[$k][] = $v;
			}
		}
	}

	/**
	 * Metoda dodaje nowe wartosci do uprzednio przekazanej tablicy
	 * @param string $key Klucz tablicy asocjacyjnej
	 * @param string $value Wartosc
	 */
	public function append($key, $value)
	{
		if (isset($this->data[$key]))
		{
			if (!is_array($this->data[$key]))
			{
				$this->data[$key] = array($this->data[$key]);
			}
		}
		$this->data[$key][] = $value;
	}

	/**
	 * Dodawanie zalacznika do e-maila
	 * @param string $filename sciezka do pliku na serwerze
	 * @deprecated
	 */
	public function attachment($filename)
	{
		return $this->addAttachment($filename);
	}

	/**
	 * Dodawanie zalacznika do e-maila
	 * @param string $filename sciezka do pliku na serwerze
	 */
	public function addAttachment($filename)
	{
		$this->attachment[] = $filename;
		return $this;
	}

	/**
	 * Metoda generuje unikalne ID wiadomosci
	 * @return string
	 */
	private function getMessageId()
	{
		$from = @$this->headers['Return-Path'];
		$from = str_replace(">", "", $from);
		$from = str_replace("<", "", $from);

		return  "<" . uniqid(''). strstr($from, '@') . ">";
	}

	/**
	 * Kompilacja szablonu wiadomosci e-mail
	 */
	private function compile()
	{
		foreach ($this->data as $k => $ary)
		{
			$this->replacement['{' . $k . '}'] = $ary[$this->iterator];
		}
		++$this->iterator;
		return str_ireplace(array_keys($this->replacement), array_values($this->replacement), $this->message);
	}

	/**
	 * Funkcja wysyla emaile (faktyczny proces generowania naglowkow i tresci)
	 * @todo Dodac mozliwosc wysylania e-maili w formacie HTML
	 */
	public function send()
	{
		reset($this->data);

		$this->addHeader('X-Mailer', 'Coyote');
		$this->addHeader('Message-ID', $this->getMessageId());
		$this->addHeader('Date', gmdate('D, d M Y H:i:s T', time()));
		$this->addHeader('Mime-Version', '1.0');

		if (!isset($this->headers['From']))
		{
			$this->setFrom(Config::getItem('site.email', 'Coyote@system'), Config::getItem('site.title'));
		}
		$headers = '';
		foreach ($this->getHeaders() as $k => $v)
		{
			$headers .= "$k: $v\n";
		}

		if ($this->attachment)
		{
			// MIME boundary
			$boundary = 'PHP' . md5(uniqid(time()));

			$pre_msg = "--$boundary\n";

			if ($this->getContentType() == 'text/html')
			{
				$pre_msg .= 'Content-Type: text/html; charset=' . $this->charset . "\n";
			}
			else
			{
				$pre_msg .= 'Content-Type: text/plain; charset=' . $this->charset . "\n";
			}
			$pre_msg .= "Content-transfer-encoding: 8bit\n\n";

			$attachment = '';

			for ($i = 0; $i < count($this->attachment); $i++)
			{
				$mime = $this->getMime($this->attachment[$i]);
				$basename = basename($this->attachment[$i]);

				$attachment .= "\n--$boundary\n";
				$attachment .= 'Content-type: ' . $mime . '; name="' . $basename . "\"\n";
				$attachment .= 'Content-Disposition: attachment; filename="' . $basename . "\"\n";
				$attachment .= "Content-ID: <" . md5($basename) . ">\n";
				$attachment .= "Content-Transfer-Encoding: base64\n\n";

				$attachment .= chunk_split(base64_encode(file_get_contents($this->attachment[$i]))) . "\n";
			}
		}

		switch ($this->getContentType())
		{
			case 'text/html':

				if (isset($attachment))
				{
					$headers .= 'Content-Type: multipart/mixed; boundary="' . $boundary . "\"\n";
				}
				else
				{
					$headers .= "Content-type: text/html; charset=" . $this->charset . "\n";
				}

				break;

			case 'text/plain':
			default:

				if (isset($attachment))
				{
					$headers .= 'Content-Type: multipart/mixed; ' . "\n" . ' boundary="' . $boundary . "\"\n";
				}
				else
				{
					$headers .= 'Content-Type: text/plain; charset=' . $this->charset . "\n";
					$headers .= "Content-transfer-encoding: 8bit\n";
				}

				break;
		}

		/* jezeli ustawiono kodowanie UTF-8, nalezy zmodyfikowac temat tak, aby byl poprawnie wyswietlany we wszystkich czytnikach */
		if (strtolower($this->charset) == 'utf-8')
		{
			$this->subject = '=?UTF-8?B?' . base64_encode($this->subject) . '?=';
		}
		$result = true;

		foreach ($this->to as $name => $email)
		{
			if (!$email)
			{
				continue;
			}
			/* jezeli mamy podana nazwe odbiorcy, nalezy dodac kolejny naglowek */
			if (is_string($name))
			{
				$email = "$name <$email>";
			}
			$message = (isset($pre_msg) ? $pre_msg : '') . $this->compile();

			if (isset($attachment))
			{
				$message .= "\n";
				$message .= $attachment;
				$message .= "\n--$boundary--\n";
			}

			if (!@mail($email, $this->subject, $message, $headers))
			{
				Log::add("Cannot send to $email", E_ERROR);
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * Metoda zwraca MIME pliku na podstawie sciezki
	 * @param string $filename Pelna sciezka do pliku
	 * @return string
	 */
	private function getMime($filename)
	{
		if (function_exists('mime_content_type'))
		{
			/* zwracamy wartosc MIME, tylko jezeli zostala poprawnie odczytana */
			if ($mime = mime_content_type($filename))
			{
				return $mime;
			}
		}

		$mimes = array(	'hqx'	=>	'application/mac-binhex40',
						'cpt'	=>	'application/mac-compactpro',
						'doc'	=>	'application/msword',
						'bin'	=>	'application/macbinary',
						'dms'	=>	'application/octet-stream',
						'lha'	=>	'application/octet-stream',
						'lzh'	=>	'application/octet-stream',
						'exe'	=>	'application/octet-stream',
						'class'	=>	'application/octet-stream',
						'psd'	=>	'application/octet-stream',
						'so'	=>	'application/octet-stream',
						'sea'	=>	'application/octet-stream',
						'dll'	=>	'application/octet-stream',
						'oda'	=>	'application/oda',
						'pdf'	=>	'application/pdf',
						'ai'	=>	'application/postscript',
						'eps'	=>	'application/postscript',
						'ps'	=>	'application/postscript',
						'smi'	=>	'application/smil',
						'smil'	=>	'application/smil',
						'mif'	=>	'application/vnd.mif',
						'xls'	=>	'application/vnd.ms-excel',
						'ppt'	=>	'application/vnd.ms-powerpoint',
						'wbxml'	=>	'application/vnd.wap.wbxml',
						'wmlc'	=>	'application/vnd.wap.wmlc',
						'dcr'	=>	'application/x-director',
						'dir'	=>	'application/x-director',
						'dxr'	=>	'application/x-director',
						'dvi'	=>	'application/x-dvi',
						'gtar'	=>	'application/x-gtar',
						'php'	=>	'application/x-httpd-php',
						'php4'	=>	'application/x-httpd-php',
						'php3'	=>	'application/x-httpd-php',
						'phtml'	=>	'application/x-httpd-php',
						'phps'	=>	'application/x-httpd-php-source',
						'js'	=>	'application/x-javascript',
						'swf'	=>	'application/x-shockwave-flash',
						'sit'	=>	'application/x-stuffit',
						'tar'	=>	'application/x-tar',
						'tgz'	=>	'application/x-tar',
						'xhtml'	=>	'application/xhtml+xml',
						'xht'	=>	'application/xhtml+xml',
						'zip'	=>	'application/zip',
						'mid'	=>	'audio/midi',
						'midi'	=>	'audio/midi',
						'mpga'	=>	'audio/mpeg',
						'mp2'	=>	'audio/mpeg',
						'mp3'	=>	'audio/mpeg',
						'aif'	=>	'audio/x-aiff',
						'aiff'	=>	'audio/x-aiff',
						'aifc'	=>	'audio/x-aiff',
						'ram'	=>	'audio/x-pn-realaudio',
						'rm'	=>	'audio/x-pn-realaudio',
						'rpm'	=>	'audio/x-pn-realaudio-plugin',
						'ra'	=>	'audio/x-realaudio',
						'rv'	=>	'video/vnd.rn-realvideo',
						'wav'	=>	'audio/x-wav',
						'bmp'	=>	'image/bmp',
						'gif'	=>	'image/gif',
						'jpeg'	=>	'image/jpeg',
						'jpg'	=>	'image/jpeg',
						'jpe'	=>	'image/jpeg',
						'png'	=>	'image/png',
						'tiff'	=>	'image/tiff',
						'tif'	=>	'image/tiff',
						'css'	=>	'text/css',
						'html'	=>	'text/html',
						'htm'	=>	'text/html',
						'shtml'	=>	'text/html',
						'txt'	=>	'text/plain',
						'text'	=>	'text/plain',
						'log'	=>	'text/plain',
						'rtx'	=>	'text/richtext',
						'rtf'	=>	'text/rtf',
						'xml'	=>	'text/xml',
						'xsl'	=>	'text/xml',
						'mpeg'	=>	'video/mpeg',
						'mpg'	=>	'video/mpeg',
						'mpe'	=>	'video/mpeg',
						'qt'	=>	'video/quicktime',
						'mov'	=>	'video/quicktime',
						'avi'	=>	'video/x-msvideo',
						'movie'	=>	'video/x-sgi-movie',
						'doc'	=>	'application/msword',
						'word'	=>	'application/msword',
						'xl'	=>	'application/excel',
						'eml'	=>	'message/rfc822'
					);

		$ext = next(explode('.', basename($filename)));

		$mime = (!isset($mimes[strtolower($ext)]) ? "application/x-unknown-content-type" : $mimes[strtolower($ext)]);
		return $mime;
	}
}
?>