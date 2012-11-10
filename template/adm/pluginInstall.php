<h1>Instalacja wtyczki</h1>

<?php if ($system && version_compare(Config::getItem('version'), $system) != 0) : ?>
<p class="note"><b>UWAGA!</b> Wtyczka nie jest przystosowana do tej wersji systemu. Może ona nie działać prawidłowo.</p>
<?php endif; ?>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label>Unikalna nazwa wtyczki</label>
				<?= $name; ?>
			</li>
			<li>
				<label>Nazwa wtyczki</label>
				<?= $text; ?>
			</li>
			<li>
				<label>Wersja wtyczki</label>
				<?= $version; ?>
			</li>
			<?php if ($author) : ?>
			<li>
				<label>Autor</label>
				<?= $author; ?>
			</li>
			<?php endif; ?>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('', 'Zainstaluj wtyczkę', array('class' => 'add-plugin-button')); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>
