<h1>Konfiguracja wtyczki</h1>

<p>Wtyczka nie może działać samodzielnie. Musi być dołączona do jednego lub więcej modułów.</p>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Włącz wtyczkę w modułach</legend>

		<ol>
			<?php foreach ($module->getModules() as $row) : ?>
			<li>
				<label></label>
				<?= Form::checkbox('module[]', $row['module_id'], (bool)in_array($row['module_id'], $pluginEnable)); ?>   <?= $row['module_text']; ?>
			</li>
			<?php endforeach; ?>

			<li>
				<label></label>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>