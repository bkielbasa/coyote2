<script type="text/javascript">
<!--
	$(document).ready(function()
	{
		$('fieldset:not(:eq(0))').hide();

		$('.page-menu li a').each(function(index)
		{
			$(this).bind('click', function()
			{
				var element = $('fieldset');

				$('.page-menu li a').removeClass('focus');
				$(this).addClass('focus');

				element.hide();
				$(element[index]).show();
			}
			);
		}
		);

		$('input[name=shutdown]').bind('change', function()
		{
			$('#shutdownMessage').toggle();
		}
		);
		if ($('input[name=shutdown]').attr('checked'))
		{
			$('#shutdownMessage').hide();
		}

		<?php if (!Auth::get('a_config')) : ?>
		$(':input').attr('disabled', 'disabled');
		<?php endif; ?>
	}
	);

//-->
</script>

<h1>Konfiguracja projektu</h1>

<p>Wartości konfiguracji. Konfiguracje możesz zmienić w pliku <i>/config/config.xml</i></p>

<?php if (!Auth::get('a_config')) : ?>
<p class="note">Nie posiadasz uprawnień do edycji konfiguracji.</p>
<?php endif; ?>

<?php if (Auth::get('a_config') && !is_writeable('config/config.xml')) : ?>
<p class="error">Nie można zapisać pliku konfiguracji. Zmień prawa dostępu do pliku <i>/config/config.xml</i> na <strong>0666</strong>.</p>
<?php endif; ?>

<?php if ($filter->hasErrors()) : ?>
<p class="error">Wystąpiły błędy walidacji podczas próby zapisu konfiguracji. Sprawdź komunikaty błędów na poszczególnych zakładkach.</p>
<?php endif; ?>

<div class="page-menu">
	<ul>
		<li><a class="focus">Podstawowe dane</a></li>
		<li><a>Informacje o stronie</a></li>
		<li><a>Konfiguracja sesji</a></li>
		<li><a>Rejestracja użytkowników</a></li>
		<li><a>E-mail</a></li>
		<li><a>Przyjazne adresy URL</a></li>
		<li><a>Dokumenty</a></li>
	</ul>
</div>
<div class="menu-block">

	<?= Form::open('', array('method' => 'post', 'onsubmit' => 'return confirm(\'Czy na pewno chesz zapisać nową konfigurację?\');')); ?>
		<fieldset>
			<ol>
				<li>
					<label>Wersja aplikacji</label>
					<?= Config::getItem('version'); ?>
				</li>
				<li>
					<label>Wersja bazy danych</label>
					<?= Config::getItem('build_date', 'brak danych'); ?>
				</li>
				<li>
					<label title="Jeżeli nie posiadasz obsługi mod_rewrite, w tym polu powinna znaleźć się wartość index.php, jako główny plik front controllera">Plik front controllera</label>
					<?= Form::input('core_frontController', Config::getItem('core.frontController')) ?>
					<ul><?= $filter->formatMessages('core_frontController'); ?></ul>
				</li>
				<li>
					<label title="Po wyłączeniu systemu będzie możliwy dostęp jedynie do panelu administracjnego">System włączony</label>
					<?= Form::checkbox('shutdown', 1, !(bool)Config::getItem('shutdown')); ?>
				</li>
				<li id="shutdownMessage">
					<label title="Komunikat informacyjny w przypadku, gdy system jest wyłączony">Komunikat informacyjny</label>
					<?= Form::textarea('message', Config::getItem('shutdown'), array('rows' => 10, 'cols' => 60)); ?>
				</li>

				<li><label>&nbsp;</label> <?= Form::submit('', 'Zapisz zmiany'); ?></li>
			</ol>
		</fieldset>

		<fieldset>
			<ol>
				<li>
					<label title="Np. localhost. Jeżeli pozostawisz to pole puste, host będzie wykrywany automatycznie">Host</label>
					<?= Form::input('site_host', Config::getItem('site.host')); ?>
					<ul><?= $filter->formatMessages('site_host'); ?></ul>
				</li>
				<li>
					<label>Tytuł strony</label>
					<?= Form::input('site_title', Config::getItem('site.title'), array('size' => 60)); ?>
					<ul><?= $filter->formatMessages('site_title'); ?></ul>
				</li>
				<li>
					<label title="Słowa kluczowe w nagłówku meta. Te dane mogą być zastępowane przez moduły, kontrolery.">Słowa kluczowe</label>
					<?= Form::textarea('site_keywords', Config::getItem('site.keywords'), array('cols' => 60, 'rows' => 10, 'style' => 'width: 500px')); ?>
				</li>
				<li>
					<label title="Opis strony w nagłówku meta. Te dane mogą być zastępowane przez moduły, kontrolery.">Słowa kluczowe</label>
					<?= Form::textarea('site_description', Config::getItem('site.description'), array('cols' => 60, 'rows' => 10, 'style' => 'width: 500px')); ?>
				</li>
				<li>
					<label title="Nagłówek strony z informacjami o prawach autorskich. Wartość może zawierać kod PHP">Stopka</label>
					<?= Form::textarea('site.copyright', Config::getItem('site.copyright'),  array('cols' => 60, 'rows' => 5, 'style' => 'width: 500px')); ?>
				</li>

				<li><label>&nbsp;</label> <?= Form::submit('', 'Zapisz zmiany'); ?></li>
			</ol>
		</fieldset>

		<fieldset>
			<ol>
				<li>
					<label title="Prefiks dla nazw plików cookies">Prefiks dla cookies</label>
					<?= Form::input('cookie_prefix', Config::getItem('cookie.prefix')); ?>
					<ul><?= $filter->formatMessages('cookie_prefix'); ?></ul>
				</li>
				<li>
					<label title="Host z jakim będą ustawione plik cookies. Zalecana wartość pusta">Host dla cookies</label>
					<?= Form::input('cookie_host', Config::getItem('cookie.host')); ?>
					<ul><?= $filter->formatMessages('cookie_host'); ?></ul>
				</li>
				<li>
					<label title="Wartość w sekundach. Czas nieaktywności po jakiej użytkownik zostanie wylogowany z systemu">Długość sesji</label>
					<?= Form::input('session_length', Config::getItem('session.length')); ?> sek.
					<ul><?= $filter->formatMessages('session_length'); ?></ul>
				</li>
				<li>
					<label title="Wartość w sekundach. Odstęp czasu wykonywania procedury sprawdzania nieaktywnych użytkowników">Częstotliwość <abbr title="garbage collector">GC</abbr></label>
					<?= Form::input('session.gc', Config::getItem('session.gc')); ?> sek.
					<ul><?= $filter->formatMessages('session_gc'); ?></ul>
				</li>

				<li><label>&nbsp;</label> <?= Form::submit('', 'Zapisz zmiany'); ?></li>
			</ol>
		</fieldset>

		<fieldset>
			<ol>
				<li>
					<label title="Opcja określająca, czy wymagana jest weryfikacja adresu e-mail po rejestracji">Weryfikacja adresu e-mail</label>
					<?= Form::checkbox('user_confirm', 'true', Config::getItem('user.confirm') == 'true' ? true : false); ?>
				</li>
				<li>
					<label title="Wybierz jaki szablon ma być użyty do wysłania e-maila z linkiem potwierdzającym adres e-mail">E-mail wysyłany w celu potwierdzenia adresu</label>
					<?= Form::select('email_confirm', Form::option($email, Config::getItem('email.confirm'))); ?>
				</li>
				<li>
					<label title="E-mail wysyłany po potwierdzeniu autentyczności adresu e-mail">E-mail wysyłany po potwierdzeniu rejestracji</label>
					<?= Form::select('email_success', Form::option($email, Config::getItem('email.success'))); ?>
				</li>
				<li>
					<label>E-mail z przypomnieniem hasła</label>
					<?= Form::select('email_password', Form::option($email, Config::getItem('email.password'))); ?>
				</li>
				<li>
					<label title="Nieprawidłowe zalogowanie na konto spowoduje wysłanie e-maila z powiadomieniem oraz adresem IP">E-mail z informacją o nieprawidłowym logowaniu</label>
					<?= Form::select('email_invalid_login', Form::option($email, Config::getItem('email.invalid_login'))); ?>
				</li>
				<li>
					<label title="Prawidłowe logowanie będzie skutkowało wysłaniem informacji (via e-mail) do właściciela profilu">E-mail z informacją o prawidłowym logowaniu</label>
					<?= Form::select('email_login', Form::option($email, Config::getItem('email.login'))); ?>
				</li>
				<li>
					<label title="E-mail wysyłany tuż po rejestracji, który zawiera losowo wygenerowane hasło">E-mail z wygenerowanym hasłem</label>
					<?= Form::select('email_random', Form::option($email, Config::getItem('email.random'))); ?>
				</li>

				<li><label>&nbsp;</label> <?= Form::submit('', 'Zapisz zmiany'); ?></li>
			</ol>
		</fieldset>

		<fieldset>
			<ol>
				<li>
					<label title="Adres zwrotny e-mail w listach wysyłanych przez system">E-mail administracji</label>
					<?= Form::input('site_email', Config::getItem('site.email'), array('size' => 40)); ?>
					<ul><?= $filter->formatMessages('site_email'); ?></ul>
				</li>
				<li>
					<label title="Nazwa nadawcy w e-mailach wysyłanych przez system">Nazwa nadawcy</label>
					<?= Form::input('email_from', Config::getItem('email.from'), array('size' => 40)); ?>
				</li>

				<li><label>&nbsp;</label> <?= Form::submit('', 'Zapisz zmiany'); ?></li>
			</ol>
		</fieldset>

		<fieldset>
			<ol>
				<li>
					<label title="Po zaznaczeniu tej opcji znaki w ścieżce będą zamieniane na małe litery">Konwertuj do małych znaków</label>
					<?= Form::checkbox('url_lowercase', 'true', Config::getItem('url.lowercase') == 'true' ? true : false); ?>
				</li>
				<li>
					<label title="Po zaznaczeniu tej opcji, pierwszy znak w ścieżce będzie zamieniany na wielką literę">Zamieniaj pierwszy znak na wielką literę</label>
					<?= Form::checkbox('url_ucfirst', 'true', Config::getItem('url.ucfirst') == 'true' ? true : false); ?>
				</li>
				<li>
					<label title="Usuwa polskie znaki diaktryczne. Np. ł zostanie zamieniona na l">Zamieniaj polskie znaki</label>
					<?= Form::checkbox('url_diacritics', 'true', Config::getItem('url.diacritics') == 'true' ? true : false); ?>
				</li>
				<li>
					<label title="Znak separatora, który zastąpi znak spacji">Separator</label>
					<?= Form::input('url_spacechar', Config::getItem('url.spaceChar'), array('size' => 1, 'style' => 'width: 5px')); ?>
				</li>
				<li>
					<label>Usuwaj znaki z adresu URL</label>
					<?= Form::input('url_remove', htmlspecialchars(Config::getItem('url.remove'))); ?>
				</li>
				<li><label>&nbsp;</label> <?= Form::submit('', 'Zapisz zmiany'); ?></li>
			</ol>
		</fieldset>

		<fieldset>
			<ol>
				<li>
					<label title="Domyślne ustawienie dla nowego dokumentu">Publikuj dokument</label>
					<?= Form::checkbox('page_publish', 'true', Config::getItem('page.publish') == 'true' ? true : false); ?>
				</li>
				<li>
					<label title="Domyślne ustawienie dla nowego dokumentu">Zapisuj w cache</label>
					<?= Form::checkbox('page_cache', 'true', Config::getItem('page.cache') == 'true' ? true : false); ?>
				</li>
				<li>
					<label title="Domyślne ustawienie dla nowego dokumentu">Domyślny edytor</label>
					<?= Form::select('page_richtext', Form::option($richtext, Config::getItem('page.richtext'))); ?>
				</li>
				<li>
					<label title="Możliwe dozwolone rozszerzenia załączników do tekstów. Należy wypisać po przecinku (bez spacji)">Dozwolone rozszerzenia załączników</label>
					<?= Form::input('attachment_suffix', Config::getItem('attachment.suffix')); ?>
				</li>
				<li>
					<label title="Limit wielkości załącznika (używaj sufiksu MB, KB lub GB)">Limit wielkości załącznika</label>
					<?= Form::input('attachment_limit', Config::getItem('attachment.limit'), array('style' => 'width: 50px')); ?>
				</li>
				<li><label>&nbsp;</label> <?= Form::submit('', 'Zapisz zmiany'); ?></li>
			</ol>
		</fieldset>
	</form>
</div>