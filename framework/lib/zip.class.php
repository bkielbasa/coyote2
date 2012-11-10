<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/*
**   Source    :   phpBB 3
** Zip creation class from phpMyAdmin 2.3.0 © Tobias Ratschiller, Olivier Müller, Loïc Chapeaux,
** Marc Delisle, http://www.phpmyadmin.net/
*
** Zip extraction function by Alexandre Tedeschi, alexandrebr at gmail dot com
**
** Modified extensively by psoTFX and DavidMJ, © phpBB Group, 2003
**
** Based on work by Eric Mueller and Denis125
** Official ZIP file format: http://www.pkware.com/appnote.txt
**
** Modyfied by Adam Boduch (c) Coyote Group 2006
*/

interface IZip
{
	public function create($fileName);
	public function open($fileName);
	public function close();
	public function read();
	public function write($fileName, $data, $isDirectory = false, $stat);
}

/*
 * Klasa sluzaca do operowania na plikach tar/gz/bz2
 */
class Zip_Gz implements IZip
{
	/* uchwyt do pliku */
	private $handle;
	private $wrote = false;

	function package_gz($module)
	{
		$this->module = $module;
	}

	public function open($fileName)
	{
		$fzopen = ($this->module == 'bz2' && function_exists('bzopen')) ? 'bzopen' : (($this->module == 'gz' && function_exists('gzopen')) ? 'gzopen' : 'fopen');

		if (!$this->handle = $fzopen($this->file_path, 'rb'))
		{
			trigger_error('Could not open gz file', E_USER_ERROR);
		}
	}

	public function create($fileName)
	{
		$fzopen = ($this->module == 'bz2' && function_exists('bzopen')) ? 'bzopen' : (($this->module == 'gz' && function_exists('gzopen')) ? 'gzopen' : 'fopen');

		if (!$this->handle = $fzopen($this->file_path, 'wb'))
		{
			trigger_error('Could not create gz file', E_USER_ERROR);
		}
	}

	public function close()
	{
		$fzclose = ($this->module == 'bz2' && function_exists('bzclose')) ? 'bzclose' : (($this->module == 'gz' && function_exists('gzclose')) ? 'gzclose' : 'fclose');

		if ($this->wrote)
		{
			$fzwrite = ($this->module == 'bz2' && function_exists('bzwrite')) ? 'bzwrite' : (($this->module == 'gz' && function_exists('gzwrite')) ? 'gzwrite' : 'fwrite');
			$fzwrite($this->handle, pack("a512", ""));
		}

		$fzclose($this->handle);
	}

	/*
	 * Odczyt archiwum tar (zrodlo: phpBB 3)
	 */
	public function read()
	{
		$fzread = ($this->module == 'bz2' && function_exists('bzread')) ? 'bzread' : (($this->module == 'gz' && function_exists('gzread')) ? 'gzread' : 'fread');

		while ($buffer = $fzread($this->handle, 512))
		{
			$tmp = unpack("a100name/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100symlink/a6magic/a2temp/a32temp/a32temp/a8temp/a8temp/a155prefix/a12temp", $buffer);

			$file = array(
				'name' => $tmp['prefix'] . $tmp['name'],
				'stat' => array (
					   2 => $tmp['mode'],
					   4 => octdec($tmp['uid']),
					   5 => octdec($tmp['gid']),
					   7 => octdec($tmp['size']),
					   9 => octdec($tmp['mtime']),
					   ),
				'checksum' => octdec($tmp['checksum']),
				'type' => $tmp['type'],
				'magic' => $tmp['magic'],
				);

			if (trim($tmp['magic']) == 'ustar')
			{
				$filename = trim($file['name']);
				$filetype = (int) trim($file['type']);

				$filesize = (int) trim($file['stat'][7]);

				if ($filesize != 0 && ($filetype == 0 || $filetype == "\0"))
				{
					$this->item[$filename]['Size'] = $filesize;
					$this->item[$filename]['Content'] = $fzread($this->handle, ($filesize + 512 - $filesize % 512));
					$this->item[$filename]['Date'] = $file['stat'][9];
				}
			}
		}
	}

	public function write($fileName, $data, $isDirectory = false, $stat)
	{
		$this->wrote = true;
		$fzwrite = ($this->module == 'bz2' && function_exists('bzwrite')) ? 'bzwrite' : (($this->module == 'gz' && function_exists('gzwrite')) ? 'gzwrite' : 'fwrite');

		$typeflag = ($isDirectory) ? '5' : '';

		$header = '';
		$header  = pack("a100", $fileName); // file name
		$header .= pack("a8", sprintf("%07o", $stat[2])); // file mode
		$header .= pack("a8", sprintf("%07o", $stat[4])); // owner id
		$header .= pack("a8", sprintf("%07o", $stat[5])); // group id
		$header .= pack("a12", sprintf("%011o", $stat[7])); // file size
		$header .= pack("a12", sprintf("%011o", $stat[9])); // last mod time

		// Checksum
		$checksum = 0;
		for ($i = 0; $i < 148; $i++)
		{
			$checksum += ord(substr($header, $i, 1));
		}

		// We precompute the rest of the hash, this saves us time in the loop and allows us to insert our hash without resorting to string functions
		$checksum += 2415 + (($isDirectory) ? 53 : 0);

		$header .= pack("a8", sprintf("%07o", $checksum)); // checksum
		$header .= pack("a1", $typeflag); // link indicator
		$header .= pack("a100", ''); // name of linked file
		$header .= pack("a6", 'ustar'); // ustar indicator
		$header .= pack("a2", '00'); // ustar version
		$header .= pack("a32", 'Unknown'); // owner name
		$header .= pack("a32", 'Unknown'); // group name
		$header .= pack("a8", ''); // device major number
		$header .= pack("a8", ''); // device minor number
		$header .= pack("a155", ''); // filename prefix
		$header .= pack("a12", ''); // end

		// This writes the entire file in one shot. Header, followed by data and then null padded to a multiple of 512
		$fzwrite($this->handle, $header . (($stat[7] !== 0 && !$isDirectory) ? $data . (($stat[7] % 512 > 0) ? str_repeat("\0", 512 - $stat[7] % 512) : '') : ''));
		unset($data);
	}
}

class Zip_Zip implements IZip
{
	private $datasec = array();
	private $ctrl_dir = array();
	private $eof_cdh = "\x50\x4b\x05\x06\x00\x00\x00\x00";
	private $old_offset = 0;
	private $datasec_len = 0;
	private $handle;

	public function open($fileName)
	{
		if (!$this->handle = fopen($fileName, 'rb'))
		{
			trigger_error('Could not open gz file', E_USER_ERROR);
		}
	}

	public function create($fileName)
	{
		if (!$this->handle = fopen($fileName, 'wb'))
		{
			trigger_error('Could not create gz file', E_USER_ERROR);
		}
	}

	private function unix_to_dos_time($time)
	{
		$timearray = (!$time) ? getdate() : getdate($time);

		if ($timearray['year'] < 1980)
		{
			$timearray['year'] = 1980;
			$timearray['mon'] = $timearray['mday'] = 1;
			$timearray['hours'] = $timearray['minutes'] = $timearray['seconds'] = 0;
		}
		return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);

	}

	public function read()
	{
	 // Loop the file, looking for files and folders
		$dd_try = false;
		rewind($this->handle);

		$items = array();

		while (!feof($this->handle))
		{
			// Check if the signature is valid...
			$signature = fread($this->handle, 4);

			switch ($signature)
			{
				// 'Local File Header'
				case "\x50\x4b\x03\x04":

				// Lets get everything we need.
				// We don't store the version needed to extract, the general purpose bit flag or the date and time fields
				$data = unpack("@4/vc_method/@10/Vcrc/Vc_size/Vuc_size/vname_len/vextra_field", fread($this->handle, 26));
				$file_name = fread($this->handle, $data['name_len']); // filename

//                  if ($data['extra_field'])
//                  {
//                      fread($this->handle, $data['extra_field']); // extra field
//                  }

				if (!$data['uc_size'])
				{
					$content = '';
				}
				else
				{
					$content = fread($this->handle, $data['c_size']);
				}

				switch ($data['c_method'])
				{
					case 8:

					// Deflate
					$content = gzinflate($content, $data['uc_size']);
					break;

					case 12:

					// Bzip2
					$content =  bzdecompress($content);
					break;
				}
				$items[$file_name]['Content'] = $content;
				$items[$file_name]['Size'] = strlen($content);

				break;


				// We hit the 'Central Directory Header', we can stop because nothing else in here requires our attention
				// or we hit the end of the central directory record, we can safely end the loop as we are totally finished with looking for files and folders
				case "\x50\x4b\x01\x02":
				// This case should simply never happen.. but it does exist..
				case "\x50\x4b\x05\x06":

				break 2;

				// 'Packed to Removable Disk', ignore it and look for the next signature...
				case 'PK00':
				continue 2;

				// We have encountered a header that is weird. Lets look for better data...
				default:

				if (!$dd_try)
				{
					// Unexpected header. Trying to detect wrong placed 'Data Descriptor';
					$dd_try = true;
					fseek($this->handle, 8, SEEK_CUR); // Jump over 'crc-32'(4) 'compressed-size'(4), 'uncompressed-size'(4)
					continue 2;
				}
				echo("Unexpected header, ending loop");
				$items = array();
				break 2;
			}
			$dd_try = false;
		}

		return $items;
	}

	public function close()
	{
		// Write out central file directory and footer ... if it exists
		if (sizeof($this->ctrl_dir))
		{
			fwrite($this->handle, $this->file());
		}
		fclose($this->handle);
	}

	// Create the structures ... note we assume version made by is MSDOS
	public function write($name, $data, $isDirectory = false, $stat)
	{
		$name = str_replace('\\', '/', $name);

		$dtime = dechex($this->unix_to_dos_time($stat[9]));
		$hexdtime = pack('H8', $dtime[6] . $dtime[7] . $dtime[4] . $dtime[5] . $dtime[2] . $dtime[3] . $dtime[0] . $dtime[1]);

		if ($isDirectory)
		{
			$unc_len = $c_len = $crc = 0;
			$zdata = '';
			$var_ext = 10;
		}
		else
		{
			$unc_len = strlen($data);
			$crc = crc32($data);
			$zdata = gzdeflate($data);
			$c_len = strlen($zdata);
			$var_ext = 20;

			// Did we compress? No, then use data as is
			if ($c_len >= $unc_len)
			{
				$zdata = $data;
				$c_len = $unc_len;
				$var_ext = 10;
			}
		}
		unset($data);

		// If we didn't compress set method to store, else deflate
		$c_method = ($c_len == $unc_len) ? "\x00\x00" : "\x08\x00";

		// Are we a file or a directory? Set archive for file
		$attrib = ($isDirectory) ? 16 : 32;
		// File Record Header
		$fr = "\x50\x4b\x03\x04";		// Local file header 4bytes
		$fr .= pack('v', $var_ext);		// ver needed to extract 2bytes
		$fr .= "\x00\x00";				// gen purpose bit flag 2bytes
		$fr .= $c_method;				// compression method 2bytes
		$fr .= $hexdtime;				// last mod time and date 2+2bytes
		$fr .= pack('V', $crc);			// crc32 4bytes
		$fr .= pack('V', $c_len);		// compressed filesize 4bytes
		$fr .= pack('V', $unc_len);		// uncompressed filesize 4bytes
		$fr .= pack('v', strlen($name));// length of filename 2bytes
		$fr .= pack('v', 0);			// extra field length 2bytes
		$fr .= $name;
		$fr .= $zdata;
		unset($zdata);

		$this->datasec_len += strlen($fr);

		// Add data to file ... by writing data out incrementally we save some memory
		fwrite($this->handle, $fr);
		unset($fr);

		// Central Directory Header
		$cdrec = "\x50\x4b\x01\x02";		// header 4bytes
		$cdrec .= "\x00\x00";               // version made by
		$cdrec .= pack('v', $var_ext);		// version needed to extract
		$cdrec .= "\x00\x00";               // gen purpose bit flag
		$cdrec .= $c_method;				// compression method
		$cdrec .= $hexdtime;                // last mod time & date
		$cdrec .= pack('V', $crc);          // crc32
		$cdrec .= pack('V', $c_len);        // compressed filesize
		$cdrec .= pack('V', $unc_len);      // uncompressed filesize
		$cdrec .= pack('v', strlen($name)); // length of filename
		$cdrec .= pack('v', 0);             // extra field length
		$cdrec .= pack('v', 0);             // file comment length
		$cdrec .= pack('v', 0);             // disk number start
		$cdrec .= pack('v', 0);             // internal file attributes
		$cdrec .= pack('V', $attrib);		// external file attributes
		$cdrec .= pack('V', $this->old_offset); // relative offset of local header
		$cdrec .= $name;

		// Save to central directory
		$this->ctrl_dir[] = $cdrec;
		$this->old_offset = $this->datasec_len;
	}

	private function file()
	{
		$ctrldir = implode('', $this->ctrl_dir);

		return $ctrldir . $this->eof_cdh .

			  pack('v', sizeof($this->ctrl_dir)) .	// total # of entries "on this disk"
		  pack('v', sizeof($this->ctrl_dir)) .	// total # of entries overall
		  pack('V', strlen($ctrldir)) .			// size of central dir
		  pack('V', $this->datasec_len) .			// offset to start of central dir
		  "\x00\x00";								// .zip file comment length
	}
}

class Zip
{
	var $item = array();

	function __construct($adapter)
	{
		$class = "Zip_$adapter";
		$this->adapter = new $class;

		if (!$this->adapter instanceof IZip)
		{
			throw new Exception("Class $class must implements IZip interface");
		}
	}

	public function create($fileName)
	{
		return $this->adapter->create($fileName);
	}

	public function open($fileName)
	{
		return $this->adapter->open($fileName);
	}

	public function close()
	{
		return $this->adapter->close();
	}

	public function read()
	{
		return $this->adapter->read();
	}

	public function write($fileName, $data, $isDirectory = false, $stat)
	{
		return $this->adapter->write($fileName, $data, $isDirectory = false, $stat);
	}
}
?>