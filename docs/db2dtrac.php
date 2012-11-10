<?php

define('DEBUG', false);
$system_dir = '../../framework/';

$config_path = 'config.php';

/**
 *	Ta zmienna nie powinna byc zmieniana. Okresla ona sciezke do katalogu, w ktorym
 *	znajduja sie pliki frameworka. 
 */
$root_dir = realpath(dirname(__FILE__)) . '/' . $system_dir;
$root_dir = str_replace('\\', '/', $root_dir);

chdir($base_dir = dirname(dirname(__FILE__)));

set_include_path(get_include_path() . PATH_SEPARATOR . $root_dir);
set_include_path(get_include_path() . PATH_SEPARATOR . $base_dir);

function __autoload($classname)
{
	include_once('lib/' . strtolower($classname) . '.class.php');
}

Config::path(array(

			'root_dir'		=> $root_dir,
			'base_dir'		=> $base_dir . '/',
			'cfg_path'		=> $config_path

			)
		);
//Config::$base_dir = $base_dir . '/';

$object = array();

/**
 * Zaladowanie i utworzenie obiektu klasy z katalogu /lib
 *
 * @param string $class_name Nazwa klasy
 * @param bool $init Okresla czy klasa zostanie zainicjalizowana
 * @param string $params Dodatkowe parametry, ktore maja byc przekazane w konstruktorze
 * @return mixed Zwraca obiekt klasy
 */
function &load_class($class_name, $init = true, $params = '')
{
	global $object;

	if (!isset($object[$class_name]))
	{
		include_once("lib/{$class_name}.class.php");
		$object[$class_name] = true;
	}

	if ($init)
	{
		// jezeli klasa jest TYLKO zaladowana (wartosc true), nalezy utworzyc jej instancje
		if ($object[$class_name] === true)
		{
			$object[$class_name] = new $class_name($params);	
		}
		// zwracamy instancje klasy
		return $object[$class_name];
	}
}

include_once($root_dir . 'lib/core.class.php');
include_once($root_dir . 'lib/config.class.php');
Config::load($config_path);

$load = new load();
$core = &Core::getInstance();


function show_table($name)
{
	global $core;

	$sql = 'SHOW FULL COLUMNS FROM ' . $name;
	$query = $core->db->query($sql);
	?>

=== Struktura tabeli <?= $name; ?> ===

||Kolumna||Typ||Opis||
<?php
	
	while ($row = $query->fetchAssoc())
	{
		$extra = '';
		if ($row['Extra'] == 'auto_increment')
		{
			$extra = 'auto_increment';
		}
?>
||<?= $row['Field']; ?>||<?= $row['Type'] . ($extra ? ' (' . $extra . ')' : ''); ?>||<?= $row['Comment']; ?>||
<?php
	}

	?>
		
	<?php

	$sql = 'SHOW KEYS FROM ' . $name;
	$query = $core->db->query($sql);
	?>

=== Klucze tabeli <?= $name; ?> ===

||Klucz||Kolumna||
<?php

	if ($query->rows())
	{
		while ($row = $query->fetchAssoc())
		{
?>
||<?= $row['Key_name']; ?>||<?= $row['Column_name']; ?>||			
<?php
		}
	}

	?>
		
	<?php
}
ob_start();

if (isset($_GET['table']))
{
	$table = $_GET['table'];
}
elseif (isset($_SERVER['argv'][1]))
{
	$table = $_SERVER['argv'][1];
}
else
{

	$sql = 'SHOW TABLE STATUS FROM ' . Config::item('db_database');
	
	$result = $core->db->query($sql)->fetch();
	foreach ($result as $row)
	{ 
		$name = $row['Name'];
		?>

== Tabela ''<?= $name; ?>'' ==

<?= $row['Comment']; ?>

<?php show_table($name); ?>

		<?php
	}	

}

$content = ob_get_contents();
ob_end_clean();

echo $content;
file_put_contents('docs/db.trac', $content);

?>