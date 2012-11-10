<h1>Konfiguracja łącznika</h1>

<p class="note">Łączniki są <strong>zaawansowanym</strong> elementem obsługi stron przez system. Zanim dokonasz zmian na tej stronie, <strong>upewnij się</strong>, że wiesz, co robisz!</p>

<?= Form::open('', array('method' => 'post', 'onsubmit' => 'return confirm(\'UWAGA! Zmiany mogą mieć wpływ na działanie systemu. Kontynuować?\');')); ?>
	<fieldset>
		<ol>
			<li>
				<label>Nazwa łącznika <em>*</em></label>
				<?= Form::input('name', $input->post('name', @$connector_name)); ?>
				<ul><?= $filter->formatMessages('name'); ?></ul>
			</li>
			<li>
				<label>Klasa łącznika <em>*</em></label>
				<?= Form::input('class', $input->post('class', @$connector_class)); ?>
				<ul><?= $filter->formatMessages('class'); ?></ul>
			</li>
			<li>
				<label>Opis <em>*</em></label>
				<?= Form::input('text', $input->post('text', @$connector_text)); ?>
			</li>
			<li>
				<label>Kontroler <em>*</em></label>
				<?= Form::input('controller', $input->post('controller', @$connector_controller)); ?>
				<ul><?= $filter->formatMessages('controller'); ?></ul>
			</li>
			<li>
				<label>Akcja <em>*</em></label>
				<?= Form::input('action', $input->post('action', @$connector_action)); ?>
				<ul><?= $filter->formatMessages('action'); ?></ul>
			</li>
			<li>
				<label title="Podkatalog, w którym znajduje się kontroler">Katalog</label> 
				<?= Form::select('folder', Form::option($folder, $input->post('folder', @$connector_folder))); ?>
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
				<?= Form::button('', 'Anuluj', array('class' => 'cancel-button', 'onclick' => 'window.location.href = \'' . url('adm/Connector') . '\'')); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>