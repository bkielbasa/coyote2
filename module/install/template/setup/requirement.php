<h1>Sprawdzanie wymagań systemu (2/5)</h1>

<?php if (!$systemTest) : ?>
<p class="error">Instalator wykrył, iż Twoje środowisko nie spełnia wymogów stawianych przez projekt. Możesz kontynuować instalacje, lecz prawdopodobnie pewne elementy systemu nie będą funkcjonować prawidłowo.</p> 
<?php endif; ?>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label title="Wymagana wersja PHP to 5.3.1. Zalecana: 5.3.3">Wersja PHP</label>
				<?= $phpversion; ?>
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

	<fieldset>
		<legend>Prawa odczytu plików konfiguracyjnych</legend>

		<ol>
			<?php foreach ($readable as $file => $result) : ?>
			<li>
				<label><?= $file; ?></label>
				<?= $result; ?>
			</li>
			<?php endforeach; ?>

		</ol>
	</fieldset>


	<?= Form::button('', 'Powrót', array('class' => 'prev-button', 'onclick' => 'window.location.href = \'' . Url::base() . '\'')); ?>
	<?= Form::button('', 'Powtórz test', array('onclick' => 'window.location.reload(true);', 'class' => 'refresh-button')); ?>
	<?= Form::submit('', 'Dalej', array('class' => 'next-button')); ?>

<?= Form::close(); ?>