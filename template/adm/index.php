<h1>Witaj <?= User::data('name'); ?>!</h1>

<?php if (isset($data)) : ?>
<p>Jesteś zalogowany w systemie administracyjnym serwisu <?= Config::getItem('site.title'); ?>.</p>

<table>
	<caption>Aktualnie zalogowani w panelu administracyjnym</caption>
	<thead>
		<tr>
			<th>Zalogowany użytkownik</th>
			<th>Ostatnia aktywność</th>
			<th>IP</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($sessions as $row) : ?>
		<tr>
			<td><?= Html::a(url('adm/User/Submit/' . $row['session_user_id']), $row['user_name']); ?></td>
			<td><?= User::formatDate($row['session_stop']); ?></td>
			<td><abbr class="whois-ip"><?= $row['session_ip']; ?></abbr></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>

<table>
	<caption>Ostatnie logowanie do panelu administracyjnego</caption>
	<thead>
		<tr>
			<th>ID</th>
			<th>Użytkownik</th>
			<th>Data i czas</th>
			<th>IP</th>
			<th>Zdarzenie</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($log as $row) : ?>
		<tr>
			<td><?= $row['log_id']; ?></td>
			<td><?= Html::a(url('adm/User/Submit/' . $row['log_user']), $row['user_name']); ?></td>
			<td><?= User::formatDate($row['log_time']); ?></td>
			<td><abbr class="whois-ip"><?= $row['log_ip']; ?></abbr></td>
			<td><?= element($logTypes, $row['log_type']); ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?>

<?php else : ?>
<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<?= $hiddenFields; ?>

		<legend>Logowanie do panelu</legend>

		<ol>
			<li>
				<label>Login</label>
				<?= Form::input('name', User::data('name'), array('readonly' => 'readonly')); ?>
			</li>
			<li>
				<label>Hasło</label>
				<?= Form::password('userPassword', ''); ?>
			</li>
			<li>
				<label></label>
				<?= Form::submit('loginSubmit', 'Logowanie', array('class' => 'login-button')); ?>
				<?= Form::button('', 'Przejdź do strony głównej', array('class' => 'homepage-button', 'onclick' => 'document.location.href = \'' . Url::base() . '\'')); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>

<?php if ($error !== false) : ?>
<p class="error"><?= $error; ?></p>
<?php endif; ?>

<!--[if IE]>
<p class="error">
	Używasz przeglądarki Internet Explorer. Do oglądania tej strony zalecane są nowocześniejsze przeglądarki takie jak <?= Html::a('http://opera.com', 'Opera'); ?> czy <?= Html::a('http://firefox.pl', 'Firefox'); ?>
</p>
<![endif]-->
<?php endif; ?>