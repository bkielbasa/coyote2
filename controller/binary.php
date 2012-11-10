<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Binary_Controller extends Controller
{
	function main()
	{
		$attachments = $this->page->getAttachments();
		if (!$attachments)
		{
			throw new Error(500, 'Brak załączników. Nie można wygenerować pliku binarnego');
		}

		if (count($attachments) > 1)
		{
			$fileName = preg_replace('#\..+$#', '', $this->page->getSubject()) . '.zip';

			$zip = new Zip('zip');
			$zip->create('tmp/' . $fileName);

			foreach ($attachments as $attachment)
			{
				$zip->write($attachment->getName(), file_get_contents($attachment->getPath()), false, stat($attachment->getPath()));
			}

			$zip->close();
			unset($zip);

			$this->sendFile('tmp/' . $fileName, $fileName, 'application/zip', filesize('tmp/' . $fileName));
			@unlink('tmp/' . $fileName);
		}
		else
		{
			$this->sendFile($attachments[0]->getPath(), $attachments[0]->getName(), $attachments[0]->getMime(), $attachments[0]->getFileSize());
		}

		exit;
	}

	private function sendFile($path, $fileName, $mimeType, $fileSize)
	{
		set_time_limit(0);

		$this->output->setHttpHeader('Content-Type', $mimeType);
		$this->output->setHttpHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
		$this->output->setHttpHeader('Content-Transfer-Encoding', 'binary');
		$this->output->setHttpHeader('Content-Length', $fileSize);
		$this->output->setHttpHeader('Cache-control', 'private');
		$this->output->setHttpHeader('Pragma', 'private');
		$this->output->setHttpHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
		flush();	

		$chunk = 1024 * 500;
		$sent = 0;

		if ($file = fopen($path, 'r'))
		{
			while (!feof($file)
				&& !connection_aborted())
			{
				echo fread($file, $chunk);
			}

			fclose($file);
		}		
		else
		{
			die('Error while opening file');
		}
	}
}
?>