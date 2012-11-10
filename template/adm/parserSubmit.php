<h1>Konfiguracja parsera</h1>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label title="Nazwa parsera, jednocześnie stanowi nazwę klasy parsera">Nazwa parsera <em>*</em></label>
				<?= Form::input('name', $input->post('name', @$parser_name)); ?>
				<ul><?= $filter->formatMessages('name'); ?></ul>
			</li>
			<li>
				<label title="Krótki opis parsera">Etykieta parsera <em>*</em></label>
				<?= Form::input('text', $input->post('text', @$parser_text)); ?>
				<ul><?= $filter->formatMessages('text'); ?></ul>
			</li>
			<li>
				<label title="Wydłużony opis działania parsera">Opis parsera</label>
				<?= Form::textarea('description', $input->post('description', @$parser_description), array('cols' => 60, 'rows' => 10)); ?>
				<ul><?= $filter->formatMessages('description'); ?></ul>
			</li>
			<li>
				<label>Domyślnie</label>
				<?= Form::radio('default', 0, isset($input->post->default) ? $input->post->default == 0 : @$parser_default == 0); ?> Wyłączony
				<?= Form::radio('default', 1, isset($input->post->default) ? $input->post->default == 1 : @$parser_default == 1); ?> Włączony
			<li>
				<label></label>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>