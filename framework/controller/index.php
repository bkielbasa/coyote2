<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class index extends controller
{
	private $project_files = array(
			'template/index.php',
			'template/layout.php',
			'template/view.cfg',
			'template/main.css',
			'template/create.php',
			'template/changelog.php',
			'template/manual.php',
			'template/demo.php',
			'template/img',
			'controller/index.php'
		);

	public function main()
	{ 
		$view = $this->load->view('index');		
		$view->project_files = $this->project_files;
		

		echo $view; 
	}

	public function docs()
	{
		echo $this->load->view('docs');
	}

	public function manual()
	{
		$manual = file_get_contents(Config::getRootPath() . 'docs/manual.html');
		echo $this->load->view('manual', array('manual' => $manual));
	}

	public function api()
	{
		$this->redirect(Url::site() . 'docs/api/');
	}

	public function demo()
	{
		echo $this->load->view('demo');
	}

	public function faq()
	{
		echo $this->load->view('faq');
	}

	public function export()
	{
		$sandbox = 'tmp/sandbox' . Core::version() . '.zip';

		$this->load->library('zip');
		$this->zip->package(Config::getBasePath() . $sandbox);
		$this->zip->open(false);

		foreach ($this->fetch() as $path)
		{
			$arr = explode('/', $path);
			$file = $arr[count($arr) -1];
			$dir = $arr[count($arr) -2];	
			
			$this->zip->write(($dir == 'img'? 'img/' : '') . $file, file_get_contents($path), ($dir == 'img'), stat($path));
		}
		$this->zip->close();

		$this->redirect(site_url() . $sandbox);
	}

	private function fetch()
	{
		$list = array();

		foreach ($this->project_files as $file)
		{
			if (is_dir(config::getBasePath() . $file))
			{
				$handler = opendir(config::getBasePath() . $file);
			
				while ($entry = readdir($handler))
				{
					if ($entry != '.' && $entry != '..')
					{
						$list[] = config::getBasePath() . $file . '/' . $entry;
					}
				}
				closedir($handler);
			}
			elseif (file_exists(config::getBasePath() . $file))
			{
				$list[] = config::getBasePath() . $file;
			}
		}
		
		return $list;
	}	

	public function uninstall()
	{	
		$this->load->helper('box');

		if (confirm_box('Usuwanie dema!', 'Jeżeli naciśniesz "Tak", pliki prezentacyjne tego frameworka zostaną usnięte. Kontynuować?'))
		{
			foreach ($this->fetch() as $path)
			{
				unlink($path);
			}
		}
		else
		{
			$this->redirect('');
		}
	}

	public function changelog()
	{
		echo $this->load->view('changelog');
	}

	public function create()
	{ 
		$this->load->library('validate');

		if ($this->input->submit())
		{
			$data = array(
							'page'		=> array(	
													array('xss'),
													array('string', false, 2, 28),
													array('match', '/([a-zA-Z]+)/')
											),
							'action'	=> array(
													array('xss'),
													array('string', false, 2, 28),
													array('match', '/([a-zA-Z]+)/')
											)
			);
			if ($this->validate->post($data))
			{
				$controller = strtolower($this->input->post->page);
				$controller = str_replace(' ', '_', $controller);

				if (file_exists(config::getBasePath() . "controller/$controller.php"))
				{
					throw new TriggerException("Kontroler o tej nazwie już istnieje!");
				} 
				$action = 'main';

				if ($this->input->post->action)
				{
					$action = $this->input->post->action;
				}
				$content = "
<?php
/* Plik kontrolera utworzony automatcznie */

class $controller extends controller
{
	public function $action()
	{
		echo 'Hello from $controller!';
	}
}
?>"; 
				file_put_contents(config::getBasePath() . "controller/$controller.php", $content);				
				$this->redirect(ucfirst($controller) . ($action ? '/' . ucfirst($action) : ''));
			}
		}

		echo $this->load->view('create');
	}
}
?>