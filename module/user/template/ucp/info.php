<?php $selected = 'info-icon'; include('_partialUserMenu.php'); ?>

<div id="box-user" class="box">
	<?= Form::open('', array('method' => 'post')); ?>
		<fieldset>
			<ol>
				<li>
					<label>Data rejestracji</label>
					<?= User::formatDate(User::data('regdate')); ?>
				</li>
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
	<?= Form::close(); ?>
</div>

<div style="clear: both;"></div>