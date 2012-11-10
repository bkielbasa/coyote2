<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Aplikacja uruchamiana z crona: operacja moze zajac dluzszy  czas
 * dlatego nadajemy nowy limit
 */
set_time_limit(0);

/**
 * Ten plik powinien byc wywolywany jedynie przez aplikacje cron. W tym celu
 * ustalamy stala IN_CRON, aby pozostale biblioteki mogly rozpoznac, czy aplikacja
 * zostala uruchomiona z crona
 */
define('IN_CRON', true);
require('index.php');
?>