<h1>Konfiguracja bazy danych (4/4)</h1>

<p>Do prawidłowego działania systemu, wymagana jest baza danych MySQL (conajmniej w wersji 5.0) oraz uprawnienia
do tworzenia/usuwania triggerów.</p>

<p class="note">  Do prawidłowej instalacji systemu zalecane jest uprawnienie <b>SUPER</b>. Ze względu na kompatybilność, parametr <i>sql-mode</i> musi być ustawiony na wartość pustą.</p>

<?php if (isset($error)) : ?>
<p class="error"><?= $error; ?></p>
<?php endif; ?>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Konfiguracja bazy danych</legend>

		<ol>
			<li>
				<label>Host</label>
				<?= Form::input('db_host', $input->post->db_host($session->db_host, $input->getHost())); ?>
				<ul><?= $filter->formatMessages('db_host'); ?></ul>
			</li>
			<li>
				<label>Login</label> 
				<?= Form::input('db_login', $input->post->db_login($session->db_login)); ?>
				<ul><?= $filter->formatMessages('db_login'); ?></ul>
			</li>
			<li>
				<label>Hasło</label> 
				<?= Form::password('db_password', $input->post->db_password($session->db_password)); ?> 
				<ul><?= $filter->formatMessages('db_password'); ?></ul>
			</li>
			<li>
				<label>Baza danych</label>
				<?= Form::input('db_database', $input->post->db_database($session->db_database)); ?> 
				<ul><?= $filter->formatMessages('db_database'); ?></ul>
			</li>
			<li>
				<label>Port</label> 
				<?= Form::input('db_port', $input->post->db_port($session->db_port)); ?> 
				<br /><small>(puste - port domyślny)</small> 
				<ul><?= $filter->formatMessages('db_port'); ?></ul>
			</li>
			<li>
				<label title="Kodowanie dla połączeń z bazą danych. Przy ustawieniu tego pola, system po każdym połączeniu z bazą będzie wysyłał zapytanie SET NAMES ustawiające odpowiednie kodowanie dla połączenia.">Ustawienie kodowania</label>
				<?= Form::input('charset', $input->post->charset($session->charset)); ?>
				<br /><small>(np. utf8)</small>
				<ul><?= $filter->formatMessages('charset'); ?></ul>
			</li>
		</ol>
	</fieldset>

	<?= Form::button('', 'Powrót', array('class' => 'prev-button', 'onclick' => 'window.location.href = \'' . Url::__('Base') . '\'')); ?>
	<?= Form::submit('', 'Konfiguracja użytkownika', array('class' => 'next-button')); ?>
</form>