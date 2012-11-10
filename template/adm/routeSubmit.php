<script type="text/javascript">
//<![CDATA[
	function addDefault()
	{
		$('<li></label><?= Form::input('default[key][]', ''); ?></label> = <?= Form::input('default[value][]', '', array('size' => 50)); ?></li>').appendTo('#default');
	}

	function addRequirements()
	{
		$('<li></label><?= Form::input('requirements[key][]', ''); ?></label> = <?= Form::input('requirements[value][]', '', array('size' => 50)); ?></li>').appendTo('#requirements');
	}

	function disable(fieldsetId)
	{
		if ($('#' + fieldsetId).attr('checked') !== true)
		{
			$('.' + fieldsetId + '_fields').attr('disabled', 'disabled');
		}
		else
		{
			$('.' + fieldsetId + '_fields').attr('disabled', '');
		}
	}
//]]>
</script>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Konfiguracja routingu</legend>

		<ol>
			<li>
				<label>Nazwa reguły</label>
				<?= Form::input('name', $input->post('name', @$routes[$name]['name'])); ?> 
				<ul><?= $filter->formatMessages('name'); ?></ul>
			</li>
			<li>
				<label>Reguła</label>
				<?= Form::input('url', $input->post('url', @$routes[$name]['url']), array('size' => 50)); ?>
				<ul><?= $filter->formatMessages('url'); ?></ul>
			</li>
			<li>
				<label>Kontroler</label>
				<?= Form::input('controller', $input->post('controller', @$routes[$name]['controller'])); ?>
				<ul><?= $filter->formatMessages('controller'); ?></ul>
			</li>
			<li>
				<label>Akcja</label> 
				<?= Form::input('action', $input->post('action', @$routes[$name]['action'])); ?> 
				<ul><?= $filter->formatMessages('action'); ?></ul>
			</li>
			<li>
				<label title="Podkatalog, w którym znajduje się kontroler">Katalog</label> 
				<?= Form::select('folder', Form::option($folder, $input->post('folder', @$routes[$name]['folder']))); ?>
			</li>
			<li>
				<label>Łącznik</label>
				<?= Form::select('connector', Form::option($connector, $input->post('connector', @$routes[$name]['connector']))); ?>
			</li>
			<li>
				<label>Strona</label>
				<?= Form::input('page', $input->post('page', @$routes[$name]['page']), array('style' => 'width: 40px')); ?>
			</li>
			<li>
				<label>&nbsp;</label>

				<fieldset id="default">
					<legend><?= Form::checkbox('default_c', 1, isset($routes[@$name]['default']), array('id' => 'default_c', 'onclick' => 'disable(\'default_c\');')); ?> Wartości domyślne</legend>

					<ol>
						<?php foreach ((array)@$routes[$name]['default'] as $key => $value) : ?>
						<li><?= Form::input('default[key][]', $key, array('class' => 'default_c_fields')); ?> = <?= Form::input('default[value][]', $value, array('size' => 50, 'class' => 'default_c_fields')); ?></li>
						<?php endforeach; ?>
					</ol>
				</fieldset>
			</li>
			<li>
				<label>&nbsp;</label> 
				<?= Form::button('', 'Dodaj nowy parametr', array('class' => 'add-button default_c_fields', 'style' => 'font-size: 0.8em;', 'onclick' => 'addDefault()')); ?>
			</li>
			<li>
				<label>&nbsp;</label>

				<fieldset id="requirements">
					<legend><?= Form::checkbox('requirements_c', 1, isset($routes[@$name]['requirements']),  array('id' => 'requirements_c', 'onclick' => 'disable(\'requirements_c\');')); ?> Wymagania</legend>
					
					<ol>
						<?php foreach ((array)@$routes[$name]['requirements'] as $key => $value) : ?>
						<li><?= Form::input('requirements[key][]', $key, array('class' => 'requirements_c_fields')); ?> = <?= Form::input('requirements[value][]', $value, array('size' => 50, 'class' => 'requirements_c_fields')); ?></li>
						<?php endforeach; ?>
					</ol>
				</fieldset>	
			</li>
			<li>
				<label>&nbsp;</label>
				<?= Form::button('', 'Dodaj nowy parametr', array('class' => 'add-button requirements_c_fields', 'style' => 'font-size: 0.8em;', 'onclick' => 'addRequirements()')); ?>
			</li>
			<li>
				<label>Host</label> 
				<?= Form::input('host', $input->post('host', @$routes[$name]['host']), array('size' => 50)); ?>
				<ul><?= $filter->formatMessages('host'); ?></ul>
			</li>
			<li>
				<label>&nbsp;</label> <?= Form::submit('', 'Zapisz regułę'); ?>
				<?php if (@$name) : ?>
				<?= Form::button('del', 'Usuń regułę', array('class' => 'delete-button', 'onclick' => 'if (confirm(\'Czy na pewno usunąć tę regułę?\')) { window.location.href = \'' . url('adm/Route/Delete/' . $name) . '\'; } ')); ?>
				<?php endif; ?>
			</li>
		</ol>
	</fieldset>
</form>
