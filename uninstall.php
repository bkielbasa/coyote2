<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 
Prosty skrypt  realizuje proces odinstalowania projektu.
NIE ingeruje w baze danych! Usuwane sa jedynie pliki konfiguracyjne,
a konkretnie przywracane sa ustawienia domyslne!

Jezeli chcesz przywrocic domyslne ustawienia, odkomentuj ponizszy
kod:
*/

/*unlink('config/config.xml');
unlink('config/db.xml');
unlink('config/trigger.xml');
unlink('config/route.xml');
unlink('config/autoload.xml');
unlink('config/module.xml');

copy('config/config.xml.default', 'config/config.xml');
copy('config/route.xml.default', 'config/route.xml');
copy('config/module.xml.default', 'config/module.xml');*/

?>
