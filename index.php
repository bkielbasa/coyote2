<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 *	Ustawienie raportowania bledow. W ustawieniu E_ALL, wszystkie bledy i ostrzezenia
 *	beda domyslnie wyswietlane. To ustawienie jest wykorzystywane przez projekt, tylko
 *	wowczas, gdy nie wykorzystujemy klasy error. Wtedy klasa error zajmuje sie
 *	wyswietlaniem i analiza bledow projektu i decyduje, ktore komunikaty wyswietlic.
 */
error_reporting(E_ALL);

/**
 * Ustawienie lokalizacji dla jezyka polskiego. Jezeli chcesz, mozesz usunac
 * ponizsza linie, lub zmienic lokalizacje
 */
setlocale(LC_ALL, array('pl_PL.UTF-8', 'polish_pol'));

/**
 * Ustawienie strefy czasowej na polska. Jezeli chcesz, mozesz dowolnie zmienic ponizsze
 * ustawienie lub usunac ponizsza linie i pozostawic ustawienie domyslne
 */
date_default_timezone_set('Europe/Warsaw');


/**
 * Domyslnie wartosc true. Skrypt jednak jest wowczas generowany wolniej ze wzgledu
 * na informacje zbierane w trakcie wykonywania. Ulatwia to prace programiscie. Jednak
 * w wersji finalnej, wartosc DEBUG powinna wynosic false.
 */
define('DEBUG', true);


/**
 *	Domyslnie wartosc tej zmiennej jest pusta. Mozesz ja wykorzystac jezeli ten plik
 *	(index.php) nie jest umieszczony w folderze z frameworkiem. Wowczas ta zmienna okresla,
 *	w ktorym katalogu znajduja sie pliki frameworka. Nie zapomnij na koncu dodac slasha (/),
 *	np. coyote-f/
 */
$system_dir = 'framework/';


/**
 *	Ta zmienna nie powinna byc zmieniana. Okresla ona sciezke do katalogu, w ktorym
 *	znajduja sie pliki frameworka.
 */
$root_dir = realpath(dirname(__FILE__)) . '/' . $system_dir;
$root_dir = str_replace('\\', '/', $root_dir);

if (!file_exists($root_dir . 'lib/core.class.php'))
{
	die('Could not find framework directory');
}
include_once($root_dir . 'lib/core.class.php');

Core::setBasePath(getcwd());

/**
 * Ustawienie pliku konfiguracji projektu
 */
Core::setConfigPath('config/config.xml');
Core::setConfigPath('config/module.xml');
Core::setConfigPath('config/autoload.xml');
Core::setConfigPath('config/db.xml');
Core::setConfigPath('config/route.xml');
Core::setConfigPath('config/trigger.xml');
Core::setConfigPath('config/region.xml');
Core::setConfigPath('config/robots.xml');
/**
 *	Metoda bootstrap() sluzy do inicjalizacji podstawowych zmiennych projektu
 */
Core::bootstrap($root_dir)->dispatch();
?>