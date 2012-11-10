<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

interface IContext
{
	public function &getContext();
}

/**
 * Klasa zapewnia metody dostepu do jadra systemu
 */
class Context implements IContext
{
	private static $instance = null;
	
	protected $router;
	protected $get;
	protected $post;
	protected $cookie;
	protected $server;
	
	/** 
	 * Inicjalizacja loadera oraz pobranie informacji o routerze
	 */
	function __construct() 
	{
		$this->router = &Load::loadClass('router');	

		$this->get = &$this->getContext()->input->get;
		$this->post = &$this->getContext()->input->post;
		$this->cookie = &$this->getContext()->input->cookie;
		$this->server = &$this->getContext()->input->server;
	}
	
	/**
	 * Zwraca referencje do modelu
	 * @param string $name Nazwa modelu
	 * @return object
	 */
	protected function &getModel($name)
	{
		return $this->getContext()->load->model($name);
	}

	/**
	 * Zwraca referencje do biblioteki jadra
	 * @param string $name Nazwa biblioteki
	 * @return object
	 */
	protected function &getLibrary($name)
	{
		return $this->getContext()->load->library($name);
	}

	/**
	 * Zwraca referencje do jadra systemu
	 * @return object
	 */
	public function &getContext()
	{
		return Core::getInstance();
	}

	/**
	 * Umozliwia proste odwolanie do danego elementu jadra
	 */
	public function __get($name)
	{
		return $this->getContext()->$name;
	}

	public static function &getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new Context;
		}
		return self::$instance;
	}
}
?>