<style type="text/css">

.success
{
	background:			url('<?= Url::site(); ?>template/adm/img/acceptIcon.png') no-repeat 0 50%;
	padding-left:		25px;
	padding-top:		2px;
	padding-bottom:		2px;
}

.failed
{
	background:			url('<?= Url::site(); ?>template/adm/img/noteIcon.png') no-repeat 0 50%;
	padding-left:		25px;
	padding-top:		2px;
	padding-bottom:		2px;
}

</style>
<h1>Status systemu</h1>

<fieldset>
	<ol>
		<li>
			<label title="Wymagana wersja PHP to 5.3">Wersja PHP</label>
			<?= $phpversion; ?>
		</li>
		<li>
			<label title="Wymagana wersja jądra systemu (framework) to 1.2">Coyote Framework</label>
			<?= $framework; ?>
		</li>
		<li>
			<label title="System obsługuje aktualnie system baz danych MySQL">Biblioteka MySQL</label>
			<?= $mysql; ?>
		</li>
		<li>
			<label title="Wymagana do generowania danych w formacie JSON">Biblioteka JSON</label>
			<?= $json; ?>
		</li>
		<li>
			<label title="Biblioteka jest wykorzystywana do konwersji łańcuchów Unicode">Biblioteka mbstring</label>
			<?= $mbstring; ?>
		</li>
		<li>
			<label title="Opcjonalna biblioteka umożliwiająca szyfrowanie zawartości cookies">Biblioteka mcrypt</label>
			<?= $mcrypt; ?>
		</li>
		<li>
			<label title="Opcjonalna biblioteka umożliwiająca przyspieszenie działania systemu jak i również przechowywanie cache w pamięci">Biblioteka eAccelerator</label>
			<?= $eaccelerator; ?>
		</li>
		<li>
			<label title="Opcjonalna biblioteka umożliwiająca cachowanie plików PHP (przechowywanie w postaci binarnej)">XCache</label>
			<?= $xcache; ?>
		</li>
		<li>
			<label title="Opcjonalna biblioteka umożliwiająca cachowanie plików PHP (przechowywanie w postaci skompilowanej)">APC</label>
			<?= $apc; ?>
		</li>
		<li>
			<hr />
		</li>
		<li>
			<label title="UWAGA! Zaleca się, aby ta opcja była WYŁĄCZONA!">register_globals</label>
			<?= $register_globals; ?>
		</li>
		<li>
			<label title="UWAGA! Zaleca się, aby ta opcja była WYŁĄCZONA!">magic_quotes_gpc</label>
			<?= $magic_quotes_gpc; ?>
		</li>
	</ol>
</fieldset>

<fieldset>
	<legend>Prawa zapisu do katalogów</legend>

	<ol>
		<?php foreach ($folders as $folder => $result) : ?>
		<li>
			<label><?= $folder; ?></label>
			<?= $result; ?>
		</li>
		<?php endforeach; ?>

	</ol>
</fieldset>

<fieldset>
	<legend>Prawa zapisu do plików konfiguracji</legend>

	<ol>
		<?php foreach ($files as $file => $result) : ?>
		<li>
			<label><?= $file; ?></label>
			<?= $result; ?>
		</li>
		<?php endforeach; ?>

	</ol>
</fieldset>