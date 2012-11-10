<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/** 
 * Adapter bazujacy na tablicach PHP
 */
class Lang_Array extends Lang_Abstract
{
	public function load($lang_file, $locale = null, array $option = array())
	{
		if ($locale == null)
		{
			$locale = $this->default;
		}

		if ((include_once("i18n/{$locale}/$lang_file.lang.php")) == false)
		{
			throw new Exception("Unable to find language file: $lang_file ");
		}				

		if (isset($lang))
		{
			$this->data[$locale] = array_merge((array)@$this->data[$locale], $lang);
		} 
		$this->option = array_merge($this->option, $option);
	}	

	public function add($lang_file, $message, $locale)
	{
		if (Load::fileExists("i18n/{$locale}/$lang_file.lang.php"))
		{ 
			$this->data[$locale][$message] = $message;

			/* sformatowanie kodu PHP */
			$php_code = "<?php \$lang = " . var_export($this->data[$locale], true) . "; ?>";

			/* proba zapisu danych */
			if ( $fp = @fopen("i18n/{$locale}/$lang_file.lang.php", 'wb') )
			{
				@flock($fp, LOCK_EX);
				fwrite($fp, $php_code);
				@flock($fp, LOCK_UN);
				fclose($fp);
			}
		}
	}
}
?>