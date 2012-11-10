<script type="text/javascript">
<!--

	$(document).ready(function()
	{
		$('thead select').bind('change', function()
		{
			$('form[name=form]').attr('method', 'get');
			document.form.submit();
		});

		$('thead input[type=text]').bind(($.browser.opera ? "keypress" : "keydown"), function(e)
		{
			keyCode = e.keyCode || window.event.keyCode;

			if (keyCode == 13)
			{
				e.preventDefault();

				$('form[name=form]').attr('method', 'get');
				document.form.submit();
			}
		});
	});
//-->
</script>

<h1>Lista użytkowników</h1>

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<?php if (isset($session->note)) : ?>
<p class="note"><?= $session->getAndDelete('note'); ?></p>
<?php endif; ?>

<?= Form::open('', array('method' => 'get', 'name' => 'form')); ?>
	<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>

	<table>
		<caption>Użytkownicy <?= isset($count) ? '(' . $count . ')' : ''; ?></caption>

		<thead>
			<tr>
				<?= Sort::displayTh('user_id', 'ID'); ?>
				<?= Sort::displayTh('user_name', 'Nazwa użytkownika'); ?>
				<?= Sort::displayTh('user_email', 'E-mail'); ?>
				<?= Sort::displayTh('session_stop', 'Status'); ?>
				<?= Sort::displayTh('user_active', 'Aktywny'); ?>
				<?= Sort::displayTh('user_regdate', 'Data rejestracji'); ?>
				<?= Sort::displayTh('user_lastvisit', 'Data aktywności'); ?>
				<?= Sort::displayTh('user_ip', 'IP'); ?>
				<?= Sort::displayTh('user_ip_login', 'IP użyte przy logowaniu'); ?>
			</tr>
			<tr>
				<th><?= Form::input('id', $input->get->id, array('style' => 'width: 30px')); ?></th>
				<th><?= Form::input('name', $input->get->name, array('style' => 'width: 150px')); ?></th>
				<th><?= Form::input('email', $input->get->email, array('style' => 'width: 150px')); ?></th>
				<th><?= Form::select('status', Form::option(array('', 'Offline', 'Online'), $input->get->status)); ?></th>
				<th></th>
				<th><?= Form::select('regdate', Form::option($userTime, $input->get->regdate)); ?></th>
				<th><?= Form::select('lastvisit', Form::option($userTime, $input->get->lastvisit)); ?></th>
				<th><?= Form::input('ip', $input->get->ip, array('style' => 'width: 90px')); ?></th>
				<th><?= Form::input('login', $input->get->login); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php load_helper('array'); ?>
		<?php if ($user) : ?>
		<?php foreach ($user as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/User/Submit/' . $row['user_id']), $row['user_id']); ?></td>
				<td><?= Html::a(url('adm/User/Submit/' . $row['user_id']), $row['user_name']); ?></td>
				<td><?= $row['user_email']; ?></td>
				<td><?= $row['session_id'] ? 'Online' : 'Offline'; ?></td>
				<td><?= element(array('Nie', 'Tak'), $row['user_active']); ?></td>
				<td><?= User::formatDate($row['user_regdate']); ?></td>
				<td><?= User::formatDate($row['session_id'] ? $row['session_stop'] : $row['user_lastvisit']); ?></td>
				<td><abbr class="whois-ip"><?= $row['user_ip']; ?></abbr></td>
				<td><span title="<?= $row['user_ip_login']; ?>"><?= Text::limit($row['user_ip_login'], 40); ?></span></td>
			</tr>
		<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="9" style="text-align: center;">Brak użytkowników spełniających kryteria.</td>
			</tr>
		<?php endif;?>
		</tbody>
	</table>

	Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?>
<?= Form::close(); ?>