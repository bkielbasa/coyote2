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
		}
		);
	}
	);

//-->
</script>

<h1>Raporty</h1>

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<p>Ta strona służy do zarządzania raportami zgłoszonymi przez użytkowników. Raporty możesz dowolnie filtrować.
W celu zobaczenia szczegołów raportu, kliknij na jego ID.</p>

<?php if ($pagination->getTotalPages() > 1) : ?>
<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>
<?php endif; ?>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Raporty</caption>

		<thead>
			<tr>
				<?= Sort::displayTh('report_id', 'ID'); ?>
				<?= Sort::displayTh('module_text', 'Moduł'); ?>
				<?= Sort::displayTh('report_time', 'Data utworzenia'); ?>
				<?= Sort::displayTh('report_user', 'Użytkownik'); ?>
				<?= Sort::displayTh('report_email', 'E-mail'); ?>
				<?= Sort::displayTh('report_page', 'ID strony'); ?>
				<?= Sort::displayTh('report_ip', 'IP'); ?>
			</tr>
			<tr>
				<th><?= Form::text('id', $input->get->id, array('style' => 'width: 20px')); ?></th>
				<th><?= Form::select('module', Form::option($modules, $input->get->module)); ?></th>
				<th><?= Form::select('time', Form::option($reportTime, $input->get->time)); ?></th>
				<th><?= Form::text('user', $input->get->user); ?></th>
				<th><?= Form::text('email', $input->get->email); ?></th>
				<th><?= Form::text('page', $input->get->page, array('style' => 'width: 30px')); ?></th>
				<th><?= Form::text('ip', $input->get->ip, array('style' => 'width: 100px')); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($report) : ?>
			<?php foreach ($report as $row) : ?>
			<tr <?= $row['report_close'] ? 'class="close"' : ''; ?>>
				<td><?= Html::a(url('adm/Report/Submit/' . $row['report_id']), $row['report_id']); ?></td>
				<td><?= $row['module_text']; ?></td>
				<td><?= User::formatDate($row['report_time']); ?></td>
				<td><?= $row['user_id'] > User::ANONYMOUS ? Html::a(url('adm/User/Submit/' . $row['user_id']), $row['user_name']) : $row['user_name']; ?></td>
				<td><?= Html::mailto($row['user_email'] ? $row['user_email'] : $row['report_email']); ?></td>
				<td><?= Html::a(url($row['location_text']), $row['page_subject']); ?></td>
				<td><?= $row['report_ip']; ?></td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="6" style="text-align: center;">Brak wpisów spełniających podane kryteria.</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
<?= Form::close(); ?>

<?php if ($pagination->getTotalPages() > 1) : ?>
<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>
<?php endif; ?>