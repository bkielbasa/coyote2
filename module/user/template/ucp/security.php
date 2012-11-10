<script type="text/javascript">

	$(document).ready(function()
	{
		$('input[name=ip[]]').keyup(function(e)
		{
			if ($(this).val().length >= 3)
			{
				$('#box-user input[tabindex=' + (parseInt($(this).attr('tabindex')) + 1) + ']').focus();
			}
		});
	});
</script>

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<p class="note">Możesz zabezpieczyć dostęp do swojego konta, podając adresy IP, z jakich będzie możliwy do nich dostęp. Znak gwiazdki (*) zastępuje cały zakres danych.</p>

<?php $selected = 'security-icon'; include('_partialUserMenu.php'); ?>

<div id="box-user" class="box">
	<?= Form::open('', array('method' => 'post')); ?>
		<fieldset>
			<ol>
				<li>
					<label></label>
					<?= Form::checkbox('alert[access]', 1, (bool) User::data('alert_access'), User::data('confirm') ? array() : array('disabled' => 'disabled')); ?> Chce otrzymywać wiadomość e-mail o nieudanej próbie logowania na moje konto
				</li>
				<li>
					<label></label>
					<?= Form::checkbox('alert[login]', 1, (bool) User::data('alert_login'), User::data('confirm') ? array() : array('disabled' => 'disabled')); ?> Chce otrzymywać wiadomość e-mail o udanym logowaniu na moje konto
				</li>
			</ol>
		</fieldset>

		<fieldset>
			<ol>
				<li>
					<label>Ostatnie logowanie</label>
					<?= def(User::data('ip_login'), '<i>Brak informacji</i>'); ?>
				</li>
				<li>
					<label>Ostatnie nieudane logowanie</label>
					<?= def(User::data('ip_invalid'), '<i>Brak informacji</i>'); ?>
				</li>
				<li>
					<label>Ostatnia aktywność z IP</label>
					<?= User::data('ip'); ?>
				</li>
				<li>
					<label>Data ostatniej wizyty</label>
					<?= User::formatDate(User::data('lastvisit')); ?>
				</li>
				<li>
					<label>Ilość wizyt</label>
					<?= User::data('visits'); ?>
				</li>
				<li>
					<label>Obecny IP</label>
					<?= User::$ip; ?>
				</li>
				<li>
					<label>Obecny host</label>
					<?= gethostbyaddr(User::$ip); ?>
				</li>
			</ol>
		</fieldset>

		<fieldset>
			<ol>
				<li>
					<label>Dostęp z adresów IP</label>

					<fieldset>
						<ol>
							<li style="margin-top: 0">
								<?= Form::text('ip[]', @$ip[0], array('tabindex' => 1, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[1], array('tabindex' => 2, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[2], array('tabindex' => 3, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[3], array('tabindex' => 4, 'maxlength' => 3, 'style' => 'width: 20px')); ?>
							</li>

							<li>
								<?= Form::text('ip[]', @$ip[4], array('tabindex' => 5, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[5], array('tabindex' => 6, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[6], array('tabindex' => 7, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[7], array('tabindex' => 8, 'maxlength' => 3, 'style' => 'width: 20px')); ?>
							</li>

							<li>
								<?= Form::text('ip[]', @$ip[8], array('tabindex' => 9, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[9], array('tabindex' => 10, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[10], array('tabindex' => 11, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[11], array('tabindex' => 12, 'maxlength' => 3, 'style' => 'width: 20px')); ?>
							</li>
						</ol>
					</fieldset>

				</li>
				<li>
					<label></label>
					<?= Form::submit('', 'Zapisz zmiany'); ?>
				</li>
			</ol>
		</fieldset>
	<?= Form::close(); ?>
</div>

<div style="clear: both;"></div>