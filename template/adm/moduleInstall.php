<h1>Instalacja modułu</h1>

<?php if (!is_writeable('config/module.xml')) : ?>
<p class="error">Plik <i>config/module.xml</i> powinien mieć ustawione prawa do zapisu. Proszę ustawić prawa <b>0666</b> i spróbować ponownie.</p>
<?php endif; ?>

<?php if ($system && version_compare(Config::getItem('version'), $system) != 0) : ?>
<p class="note"><b>UWAGA!</b> Moduł nie jest przystosowany do tej wersji systemu. Może on nie działać prawidłowo.</p>
<?php endif; ?>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label>Unikalna nazwa modułu</label>
				<?= $name; ?>
			</li>
			<li>
				<label>Nazwa modułu</label>
				<?= $text; ?>
			</li>
			<li>
				<label>Wersja modułu</label>
				<?= $version; ?>
			</li>
			<?php if ($author) : ?>
			<li>
				<label>Autor</label>
				<?= $author; ?>
			</li>
			<?php endif; ?>
			<?php if (is_writeable('config/config.xml')) : ?>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('', 'Zainstaluj moduł', array('class' => 'add-module-button')); ?>
			</li>
			<?php endif; ?>
		</ol>
	</fieldset>
<?= Form::close(); ?>
