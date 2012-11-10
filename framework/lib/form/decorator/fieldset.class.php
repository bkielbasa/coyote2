<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */


/**
 * Dekorator generujacy znacznik <fieldset>
 */
class Form_Decorator_Fieldset extends Form_Decorator_Tag
{
	protected $placement = self::WRAP;
	protected $tag = 'fieldset';
}
?>