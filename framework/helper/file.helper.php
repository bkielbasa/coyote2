<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Helper obslugi plikow
 */
class File 
{
	/**
	 * Domyslne typy MIME
	 * @static
	 */
	private static $defaultMimes = array(
	
		'hqx'	=>	'application/mac-binhex40',
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
	
	/**
	 * Metoda zwraca typ MIME na podstawie sciezki do pliku
	 * @param string $fileName		Sciezka do pliku
	 * @return string				Typ MIME (np. text/plain)
	 */
	public static function mime($fileName)
	{
		if (class_exists('finfo', false))
		{
			$info = finfo_open(FILEINFO_MIME);
			if (!$info)
			{
				throw new Exception("$fileName does not exist");
			}
			
			$mime = finfo_file($info, $fileName);
			finfo_close($info);
			
			/*
			 * Funkcja finfo_file() zwraca typ MIME w postaci <typ mime>; charset
			 * Chcemy, aby metoda zwracala jedynie typ mime, tzn. zamiast text/plain; charset=utf-8 --
			 * samo text/plain
			 * 
			 * @todo W metodzie powinien znalezc sie parametr, ktory okresla, czy metoda powinna
			 * zwracac rowniez informacje o kodowaniu
			 */
			list($mime, ) = explode(';', $mime);
			
			return $mime;
		}
		else
		if (function_exists('mime_content_type'))
		{
			return mime_content_type($fileName);
		}
		else
		{
			$mimes = Config::getItem('mimes', self::$defaultMimes);
			$suffix = pathinfo($fileName, PATHINFO_EXTENSION);
			
			return isset($mimes[$suffix]) ? $mimes[$suffix] : false;			
		}
	}
}

?>