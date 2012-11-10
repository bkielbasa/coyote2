<h1>Konfiguracja powiadomień</h1>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label title="Ta nazwa będzie wyświetlana w panelu użytkownika">Nazwa <em>*</em></label>
				<?= Form::text('name', $input->post('name', @$notify_name)); ?>
			</li>
			<li>
				<label title="Treść która będzie wyświetlana na liście powiadomień">Nagłówek powiadomienia <em>*</em></label>
				<?= Form::text('message', $input->post('message', @$notify_message)); ?>
			</li>
			<li>
				<label>Moduł <em>*</em></label>
				<?= Form::select('module', Form::option($modules, $input->post('module', @$notify_module))); ?>
			</li>
			<li>
				<label>Wtyczka</label>
				<?= Form::select('plugin', Form::option($plugins, $input->post('plugin', @$notify_plugin))); ?>
			</li>
			<li>
				<label title="Trigger po którego wystąpieniu, zostanie wygenerowane powiadomienie">Trigger</label>
				<?= Form::select('trigger', Form::option($triggers, $input->post('trigger', @$notify_trigger))); ?>
			</li>
			<li>
				<label title="Określ, która klasa ma odpowiadać za obsługę powiadomienia">Klasa obsługi powiadomienia</label>
				<?= Form::select('class', Form::option($notifyClass, $input->post('class', @$notify_class))); ?>
			</li>
			<li>
				<label title="E-mail wysyłany do użytkownika przy tworzeniu tego powiadomienia">E-mail</label>
				<?= Form::select('email', Form::option($email, $input->post('email', @$notify_email))); ?>
			</li>
			<li>
				<label title="Domyślna wartość: czy powiadomienie jest włączone">Domyślnie</label>
				<?= Form::select('default', Form::option(array(0 => 'Nie', 3 => 'Tak (powiadomienie w profilu oraz na e-mail)', 1 => 'Tak (tylko w profilu)', 2 => 'Tak (tylko na e-mail)'), $input->post('default', @$notify_default))); ?>
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
			</li>


		</ol>
	</fieldset>
<?= Form::close(); ?>