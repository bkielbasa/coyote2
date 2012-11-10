<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Attachment_Controller extends Controller
{
	/*function main()
	{
		echo Form::openMultipart(url('attachment/Submit') ,array('method' => 'post'));
		echo Form::file('attachment');
		echo Form::submit('', '');
		echo Form::close();
	}*/

	public function submit()
	{
		$data_arr = array();

		try
		{
			if (User::$id == User::ANONYMOUS)
			{
				throw new Exception('Tylko zarejestrowani użytkownicy mogą dodawać załączniki');
			}
			$upload = &$this->getLibrary('upload');
			$upload->setDestination('tmp/');
			$upload->setOverwrite(true);

			Load::loadFile('lib/validate.class.php');
			$validate = new Validate_Upload(false, Config::getItem('attachment.limit', '10MB'), Config::getItem('attachment.suffix', 'jpg,gif,jpeg,png'));
			if (!$validate->isValid('attachment'))
			{
				throw new Exception(implode("\n", $validate->getMessages()));
			}

			if ($upload->recive('attachment'))
			{
				$fileName = uniqid(mt_rand()) . '.' . $upload->getSuffix();
				rename('tmp/' . $upload->getFileName(), 'store/_aa/' . $fileName);

				$attachment = new Attachment;
				$attachment->insert(array(
					'name'		=> $upload->getFileName(),
					'fileName'	=> $fileName
					)
				);
				$id = $attachment->getId();

				$result = array(
					'id'			=> $id,
					'path'			=> url('store/_aa/' . $fileName),
					'size'			=> $upload->getFileSize(),
					'suffix'		=> $upload->getSuffix(),
					'width'			=> $attachment->getWidth(),
					'height'		=> $attachment->getHeight(),
					'name'			=> addslashes($upload->getFileName()),
					'mime'			=> $attachment->getMime(),
					'time'			=> User::formatDate($attachment->getTime())
				);
			}

			echo json_encode($result);
		}
		catch (Exception $e)
		{
			Log::add($e->getMessage(), E_ERROR);

			echo json_encode(array(
				'error'		=> $e->getMessage()
				)
			);
		}

		exit;
	}

	public function delete()
	{
		if (User::$id == User::ANONYMOUS)
		{
			throw new Exception('Tylko zarejestrowani użytkownicy mogą usuwać załączniki');
		}

		$id = (int)$this->get->id;

		$attachment = &$this->load->model('attachment');
		$attachment->delete($id);

		exit;
	}

	public function get($id)
	{
		$id = (int)$id;
		if (!$id)
		{
			throw new UserErrorException('Załącznik o tym ID nie istnieje lub został usunięty!');
		}

		$attachment = new Attachment($id);
		if (!$attachment->getId())
		{
			throw new UserErrorException('Załącznik o tym ID nie istnieje lub został usunięty!');
		}
		set_time_limit(0);

		$this->output->setHttpHeader('Content-Type', $attachment->getMime());
		$this->output->setHttpHeader('Content-Disposition', 'attachment; filename="' . $attachment->getName() . '"');
		$this->output->setHttpHeader('Content-Transfer-Encoding', 'binary');
		$this->output->setHttpHeader('Content-Length', $attachment->getFileSize());
		$this->output->setHttpHeader('Cache-control', 'private');
		$this->output->setHttpHeader('Pragma', 'private');
		$this->output->setHttpHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
		flush();

		$chunk = 1024 * 500;
		$sent = 0;

		if ($file = fopen($attachment->getPath(), 'r'))
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

		exit;
	}
}
?>