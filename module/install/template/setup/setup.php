<script type="text/javascript">
<!--

$(document).ready(function()
{
	$(':input[name=install]').bind('click', function()
	{
		element = this;

		$.ajax(
		{
			url: '<?= Url::__('Setup/Setup'); ?>',
			type: 'GET',
			beforeSend: function()
			{
				$(element).attr('disabled', 'disabled');
				$(element).val('Instalacja...');
			},
			success: function(data)
			{
				window.location.href = '<?= Url::__('Setup/Success'); ?>';
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				$('.error').html(xhr.responseText).show();

				$(element).removeAttr('disabled');
				$(element).val('Zainstaluj');
			}
		}
		);
	}
	);
}
);
//-->
</script>

<h1>Zakończ instalację</h1>

<p>Aby zakończyć proces instalacji, kliknij na przycisk <i>Zainstaluj</i>.</p>

<fieldset>
	<legend>Informacje o stronie</legend>

	<ol>
		<li>
			<label>Tytuł strony</label>
			<?= def($session->site_title, '<i>Brak informacji</i>'); ?>
		</li>
		<li>
			<label>Host</label>
			<?= def($session->core_host, '<i>Brak informacji</i>'); ?>
		</li>
		<li>
			<label>Prefiks cookie</label>
			<?= def($session->cookie_prefix, '<i>Brak informacji</i>'); ?>
		</li>
		<li>
			<label>Host dla cookies</label>
			<?= def($session->cookie_host, '<i>Brak informacji</i>'); ?>
		</li>
	</ol>
</fieldset>

<fieldset>
	<legend>Konfiguracja bazy danych</legend>

	<ol>
		<li>
			<label>Serwer bazy danych</label>
			<?= $session->db_host; ?>
		</li>
		<li>
			<label>Login</label>
			<?= $session->db_login; ?>
		</li>
		<li>
			<label>Nazwa bazy danych</label>
			<?= $session->db_database; ?>
		</li>
				
	</ol>
</fieldset>

<fieldset>
	<legend>Dane administratora</legend>

	<ol>
		<li>
			<label>Login administratora</label>
			<?= $session->name; ?>
		</li>
		<li>
			<label>Hasło</label>
			<?= $session->password; ?>
		</li>
		<li>
			<label>E-mail</label>
			<?= def($session->email, '<i>Brak informacji</i>'); ?>
		</li>
	</ol>
</fieldset>

<?= Form::button('', 'Powrót', array('class' => 'prev-button', 'onclick' => 'window.location.href = \'' . Url::__('User') . '\'')); ?> 
<?= Form::button('install', 'Zainstaluj'); ?>

<p class="message" style="display: none;"></p>
<p class="error" style="display: none;"></p>