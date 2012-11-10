<h1>Edycja reguły cron</h1>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label title="Reguła musi być identyfikowana przez unikalną nazwę">Nazwa reguły <em>*</em></label>
				<?= Form::text('name', $input->post('name', @$scheduler_name)); ?>
				<ul><?= $filter->formatMessages('name'); ?></ul>
			</li>
			<li>
				<label>Moduł <em>*</em></label>
				<?= Form::select('module', Form::option($moduleList, $input->post('module', @$scheduler_module))); ?>
			</li>
			<li>
				<label title="Opis będzie widoczny jedynie dla administratorów">Opis reguły</label>
				<?= Form::textarea('description', $input->post('description', @$scheduler_description), array('cols' => 65, 'rows' => 10)); ?>
			</li>
			<li>
				<label>Nazwa klasy <em>*</em></label>
				<?= Form::text('class', $input->post('class', @$scheduler_class)); ?>
				<ul><?= $filter->formatMessages('class'); ?></ul>
			</li>
			<li>
				<label>Nazwa metody <em>*</em></label>
				<?= Form::text('method', $input->post('method', @$scheduler_method)); ?>
				<ul><?= $filter->formatMessages('method'); ?></ul>
			</li>
			<li>
				<label title="Wartość w sekundach">Częstotliwość <em>*</em></label>

				<fieldset>

					<ol>
						<li>
							<label><?= Form::radio('mode', 'frequency', empty($scheduer_id) ? true : $scheduler_frequency != null); ?> Cyklicznie co:</label>
							<?= Form::text('frequency', $input->post('frequency', (int) @$scheduler_frequency), array('style' => 'width: 40px')); ?> sekund
						</li>

						<li>
							<label><?= Form::radio('mode', 'time', empty($scheduler_id) ? false : $scheduler_time != null); ?> Raz na dobę o godz.:</label>
							<?= Form::hour('hour', @$hour); ?> :
							<?= Form::minute('minute', @$minute); ?>
						</li>

					</ol>

				</fieldset>

				<ul><?= $filter->formatMessages('freq'); ?></ul>
			</li>
			<li>
				<label></label>
				<?= Form::checkbox('enable', 1, @$scheduler_enable); ?> Reguła aktywna

			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>