<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Widok wyswietlajacy strone XHTML
 */
class View_XHTML implements IView
{
	/**
	 * Instancja klasy View
	 */
	private $context;
	/**
	 * Konfiguracja widoku
	 */
	private $config = array();
	
	/**
	 * Inicjalizacja klasy. Przekazanie do metody instancji do klasy View
	 * @param mixed &$context Referencja do obiektu klasy View
	 */
	public function initialize(&$context)
	{
		$this->context = $context;
	}

	private function arrayMerge($config, $array)
	{
		foreach ($array as $element => $value)
		{
			if ($element == 'stylesheet' || $element == 'javascript')
			{
				if (!is_array($value))
				{
					$value = array($value);
				}
				if (!isset($config[$element]))
				{
					$config[$element] = array();
				}

				foreach ($value as $val)
				{
					if ($val{0} == '-')
					{
						if ($val{1} == '*')
						{
							$config[$element] = array();
							continue;
						}
						$index = array_search(substr($val, 1), $config[$element]);
						if ($index !== false)
						{
							unset($config[$element][$index]);
						}
						continue;
					}
					$config[$element][] = $val;
				}
			}
			else
			{
				$config[$element] = $value;
			}
		}
		return $config;
	}

	private function parseElement($element)
	{
		if (is_array($element))
		{
			return $element;
		}
		return preg_replace('#%([a-zA-Z0-9\._-]*?)%#ie', 'Config::getItem("$1")', $element);
	}

	/**
	 * Odczytuje konfiguracje widokow
	 */
	public function loadConfig()
	{
		// pobranie konfiguracji widokow z okreslonej sciezki
		$config = &View_Config::get($this->context->getPath());

		foreach ((array) $config as $view => $array)
		{
			$view = str_replace(array('*', 'all'), '.*', $view);

			// nalezy sprawdzic, czy dany wzor odpowiada nazwie widoku
			if (preg_match("#^$view$#i", $this->context->getName()))
			{ 
				if (isset($array['overwrite']))
				{
					$this->config = $array;
				}
				else
				{
					$this->config = $this->arrayMerge($this->config, $array);
				}
			}			
		}		
		$core = &Core::getInstance();	

		foreach ($this->config as $function => $arg)
		{ 
			if (is_string($arg))
			{
				$arg = $this->parseElement($arg);
			}
			elseif (is_array($arg))
			{
				$arg = array_map(array(&$this, 'parseElement'), $arg);
			}
			if (method_exists($core->output, 'set' . $function))
			{
				/** 
				 * Poprawka dla PHP 5.3. We wczesniejszych wersjach przekazanie 
				 * lancucha w parametrze funkcji call_user_func_array() nie powodowalo
				 * komunikatu ostrzezenia
				 */
				$arg = array($arg);

				// przekazanie konfiguracji do klasy Output
				call_user_func_array(array(&$core->output, 'set' . $function), $arg);
			}
		}
	}

	/**
	 * Metoda zwraca wartosc w konfiguracji widoku
	 * @param string $option 
	 * @return string Wartosc konfiguracji
	 */
	public function getConfig($option = '')
	{
		if (!$option)
		{
			return $this->config;
		}
		return isset($this->config[$option]) ? $this->config[$option] : false;
	}

	/**
	 * Metoda ustawia wartosc w konfiguracji widoku
	 * @param string $option Opcja 
	 * @param string $value Wartosc opcji
	 */
	public function setConfig($option, $value)
	{
		$this->config[$option] = $value;
	}

	/**
	 * Glowna metoda klasy - wyswietlanie tresci strony
	 * @param bool $display Wartosc True oznacza, ze zawartosc strony zostanie wyswietlona, false - zostanie zwrocona
	 */
	public function display($display = true)
	{
		$core = &Core::getInstance();	

		// referencje do przekazanych parametrow
		extract($this->context->getData(), EXTR_REFS); 
		// referncje do elementow jadra
		extract(get_object_vars($core), EXTR_REFS);

		$suffix = isset($this->suffix) ? $this->suffix : Config::getItem('core.templateSuffix');
		$viewPath = $this->context->getPath() . $this->context->getName() . $suffix;

		ob_start();
		if ((include($viewPath)) === false)
		{
			echo("<b>View $path does not exists!</b>");
		}

		$this->content = ob_get_contents();
		ob_end_clean();

		if ($this->getConfig('layout'))
		{ 
			ob_start();

			include($this->context->getPath() . $this->getConfig('layout') . $suffix);
			$this->content = ob_get_contents();

			ob_end_clean();
		}		
		Trigger::call('system.onDisplay', array(&$this->content, $this->context->getName()));

		if (!$display)
		{
			return $this->content;
		}
		else
		{
			echo $this->content;
		}
	}
}

?>