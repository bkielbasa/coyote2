
<p class="note">Weryfikacja adresu e-mail jest konieczna, abyś mógł otrzymywać wiadomości oraz powiadomienia. Adres e-mail <strong>nie</strong> jest udostępniany osobom tzecim.</p>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label>Nazwa użytkownika</label>
				<?= Form::input('name', User::$id > User::ANONYMOUS ? User::data('name') : '', User::$id > User::ANONYMOUS ? array('disabled' => 'disabled') : array()); ?>
				<ul><?= $filter->formatMessages('name'); ?></ul>
			</li>
			<li>
				<label>Adres e-mail</label>
				<?= Form::input('email', User::data('email')); ?>
				<ul><?= $filter->formatMessages('email'); ?></ul>
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Wygeneruj klucz aktywacyjny'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>