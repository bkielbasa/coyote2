
<?= Form::open('', array('method' => 'post', 'onsubmit' => 'return confirm(\'UWAGA! Wprowadzone zmiany mogą mieć wpływ na działania systemu. Czy chcesz kontynuować?\');')); ?>
	<fieldset>
		<legend>Informacje o module</legend>

		<ol>
			<li>
				<label>Nazwa modułu</label> 
				<?= $text; ?>
			</li>
			<li>
				<label>Wersja</label>
				<?= $version; ?>
			</li>
			<?php if (isset($author)) : ?>
			<li>
				<label>Autor</label>
				<?= $author; ?>
			</li>
			<?php endif; ?>
		</ol>		
	</fieldset>

	<fieldset>
		<legend>Konfiguracja modułu</legend>

		<ol>
			<?= $form; ?>
		</ol>
	</fieldset>

	<fieldset style="border: none; margin: 0;">
		<ol>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
			</li>
		</ol>
	</fieldset>
</form>