<?php if (!is_writeable('config/trigger.xml')) : ?>
<p class="error">Nie można zapisać konfiguracji do pliku XML! Zmień prawa dostępu do pliku <i>/config/trigger.xml</i> na <label>0666</label>!</p>
<?php endif; ?>
<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Konfiguracja zdarzenia</legend>

		<ol>
			<li>
				<label>Trigger</label>
				<?= Html::a(url('adm/Trigger/Submit/' . $trigger->trigger_id), $trigger->trigger_name); ?>
			</li>
			<li>
				<label>Nazwa zdarzenia</label> 
				<?= Form::input('name', $input->post('name', @$event_name)); ?>
				<ul><?= $filter->formatMessages('name'); ?></ul>
			</li>
			<li>
				<label title="Nazwa klasy PHP, która zostanie wywołana w skutek wystąpienia triggera">Nazwa klasy</label>
				<?= Form::input('class', $input->post('class', @$event_class), array('id' => 'class')); ?> 
				<ul><?= $filter->formatMessages('class'); ?></ul>
			</li>
			<li>
				<label title="Nazwa metody lub funkcji">Nazwa metody</label> 
				<?= Form::input('function', $input->post('function', @$event_function), array('id' => 'function')); ?> 
				<ul><?= $filter->formatMessages('function'); ?></ul>
			</li>
			<li>
				<label title="Dodatkowy parametr, który zostanie przekazany w formie parametru do metody">Dodatkowe parametry</label>
				<?= Form::input('params', $input->post('params', @$event_params), array('id' => 'params')); ?> 
				<ul><?= $filter->formatMessages('params'); ?></ul>
			</li>
			<li>
				<label title="Kod PHP, który zostanie wywołany w momencie wystąpienia triggera">Kod PHP</label> 
				<?= Form::textarea('eval', $input->post('eval', @$event_eval), array('cols' => 70, 'rows' => 10)); ?>
			</li>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
			</li>
		</ol>
	</fieldset>
</form>