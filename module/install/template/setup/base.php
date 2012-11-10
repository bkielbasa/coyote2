<h1>Podstawowa konfiguracja projektu (3/5)</h1>

<p>Wprowadź podstawową konfigurację projektu. Wszelkie dane będziesz mógł zmienić po instalacji projektu w panelu administracyjnym.</p>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Podstawowa konfiguracja</legend>

		<ol>
			<li>
				<label title="Tytuł strony będzie wyświetlany w belce tytułowej przeglądarki">Tytuł strony</label>
				<?= Form::input('site_title', $input->post->site_title($session->site_title, '4programmers.net')); ?>
				<ul><?= $filter->formatMessages('site_title'); ?></ul>
			</li>
			<li>
				<label title="Jeżeli posiadasz włączony moduł mod_rewrite, pozostaw to pole puste. W przeciwnym wypadku wprowadź nazwę pliku front-controllera - index.php">Front Controller</label>
				<?= Form::input('core_frontController', $input->post->core_frontController($session->core_frontController, Config::getItem('core_frontController'))); ?>
				<br /><small>Pozostaw puste dla przyjaznych linków</small>
				<ul><?= $filter->formatMessages('core_frontController'); ?></ul>
			</li>
			<li>
				<label title="Nie musisz wypełniać tego pola. Wówczas system automatycznie przypisze host dla systemu">Host</label> 
				<?= Form::input('site_host', $input->post->core_host($session->site_host, $input->getHost())); ?>
				<br /><small>Jeżeli pole pozostanie puste, system będzie przypisywał host automatycznie</small>
				<ul><?= $filter->formatMessages('site_host'); ?></ul>
			</li>
			<li>
				<label title="Jeżeli wprowadzisz prefiks dla plików cookie, unikniesz ewentualnego konfliktu nazw">Cookie prefix</label> 
				<?= Form::input('cookie_prefix', $input->post->cookie_prefix($session->cookie_prefix)); ?> 
				<ul><?= $filter->formatMessages('cookie_prefix'); ?></ul>
			</li>
			<li>
				<label title="Zalecane jest pozostawienie tego pola pustego">Cookie host</label>
				<?= Form::input('cookie_host', $input->post->cookie_host($session->cookie_host)); ?> 
				<ul><?= $filter->formatMessages('cookie_host'); ?></ul>
			</li>
		</ol>
	</fieldset>

	<?= Form::button('', 'Wróć', array('class' => 'prev-button', 'onclick' => 'window.location.href = \'' . Url::__('Requirement') . '\'')); ?>
	<?= Form::submit('', 'Konfiguracja bazy danych', array('class' => 'next-button')); ?>
</form>