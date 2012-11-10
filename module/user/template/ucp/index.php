<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<?php if (!User::data('email')) : ?>
<p class="note">Nie podałeś adresu e-mail. Dopóki nie podasz adresu e-mail, nie będziesz otrzymywał żadnych powiadomień e-mail z serwisu.</p>
<?php elseif (!User::data('confirm')) : ?>
<p class="note">Nie potwierdziłeś adresu e-mail. Dopóki nie potwierdzisz adresu e-mail, nie będziesz dostawał żadnych powiadamień na e-mail. <a href="<?= url('@user?controller=Confirm'); ?>">Dowiedz się więcej.</a></p>
<?php endif; ?>

<?php $selected = 'user-icon'; include('_partialUserMenu.php'); ?>

<div id="box-user" class="box">
	<?= Form::openMultipart('', array('method' => 'post')); ?>

		<fieldset>
			<ol>
				<?= $form; ?>

				<li>
					<label></label>
					<?= Form::submit('', 'Zapisz zmiany'); ?>
				</li>
			</ol>
		</fieldset>

		<fieldset style="display: none;">
			<ol>
				<li>
					<label>Data rejestracji</label>
					<?= User::formatDate(User::data('regdate')); ?>
				</li>
				<li>
					<label>Ostatnia wizyta</label>
					<?= User::formatDate(User::data('lastvisit')); ?>
				</li>
				<li>
					<label>Ilość wizyt</label>
					<?= User::data('visits'); ?>
				</li>
				<li>
					<label>Ostatnio z IP</label>
					<?= User::data('ip'); ?>
				</li>
				<li>
					<label>Ostatnie logowanie</label>
					<?= def(User::data('ip_login'), '<i>Brak informacji</i>'); ?>
				</li>
				<li>
					<label>Ostatnie nieudane logowanie</label>
					<?= def(User::data('ip_invalid'), '<i>Brak informacji</i>'); ?>
				</li>
			</ol>
		</fieldset>
	<?= Form::close(); ?>
</div>

<div style="clear: both;"></div>