<script type="text/javascript">
<!--

	$(document).ready(function()
	{
		$('thead select').bind('change', function()
		{
			document.form.submit();
		});

		$('thead input[type=text]').bind(($.browser.opera ? "keypress" : "keydown"), function(e)
		{
			keyCode = e.keyCode || window.event.keyCode;

			if (keyCode == 13)
			{
				e.preventDefault();

				document.form.submit();
			}
		}
		);
	}
	);
//-->
</script>

<h1>Ostatnie wizyty</h1>

<p>Strona zawiera listę ostatnich wizyt w serwisie.</p>

<?php if ($pagination->getTotalPages() > 1) : ?>
<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>
<?php endif; ?>

<?= Form::open('', array('name' => 'form')); ?>
	<table>
		<caption>Lista ostatnich wizyt</caption>
		<thead>
			<tr>
				<?= Sort::displayTh('user_name', 'Nazwa użytkownika'); ?>
				<?= Sort::displayTh('log_start', 'Data logowania'); ?>
				<?= Sort::displayTh('log_stop', 'Ostatnia aktywność'); ?>
				<?= Sort::displayTh('log_ip', 'IP'); ?>
				<th>Strona</th>
			</tr>
			<tr>
				<th><?= Form::text('user', $input->get->user); ?></th>
				<th><?= Form::select('start', Form::option($date, $input->get->start)); ?></th>
				<th><?= Form::select('stop', Form::option($date, $input->get->stop)); ?></th>
				<th><?= Form::text('ip', $input->get->ip); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php if ($visit) : ?>
		<?php foreach ($visit as $row) : ?>
			<tr>
				<td><?= $row['log_user'] > User::ANONYMOUS ? Html::a(url('adm/User/Submit/' . $row['log_user']), $row['user_name']) : ($row['log_robot'] ? $row['log_robot'] : $row['user_name']); ?></td>
				<td><?= User::formatDate($row['log_start']); ?></td>
				<td><?= User::formatDate($row['log_stop']); ?></td>
				<td><abbr class="whois-ip"><?= $row['log_ip']; ?></abbr></td>
				<td><?= Html::a(url(ltrim($row['log_page'], '/')), Text::limit($row['log_page'], 25)); ?></td>
			</tr>
		<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td colspan="6" style="text-align: center;">Brak wpisów w bazie danych</td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>
<?= Form::close(); ?>

<?php if ($pagination->getTotalPages() > 1) : ?>
Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?>
<?php endif; ?>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Usuwanie wpisów</legend>

		<ol>
			<li>
				<label>Usuń wpisy starsze niż</label>
				<?= Form::select('purge', Form::option($purge, 0)); ?>
			</li>
			<li>
				<label></label>
				<?= Form::checkbox('anonymous', 1, false); ?> Usuń informacje <strong>tylko</strong> o wizytach użytkowników anonimowych
			</li>
			<li><label>&nbsp;</label>
				<?= Form::submit('', 'Usuń', array('class' => 'delete-button')); ?>
			</li>
		</ol>
	</fieldset>
</form>