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

<h1>Szukanie stron</h1>

<p>Strona służy do filtrowania i wyszukiwania stron w systemie. W polach tekstowych można używać znaku *</p>

<?php if ($pagination->getTotalPages() > 1) : ?>
<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>
<?php endif; ?>

<?= Form::open('', array('method' => 'get', 'name' => 'form')); ?>
	<table>
		<caption>Przeglądaj strony</caption>
		<thead>
			<tr>
				<?= Sort::displayTh('page_id', 'ID'); ?>
				<?= Sort::displayTh('page_subject', 'Tytuł strony'); ?>
				<?= Sort::displayTh('location_text', 'Ścieżka'); ?>
				<?= Sort::displayTh('page_time', 'Data utworzenia'); ?>
				<?= Sort::displayTh('page_edit_time', 'Data edycji'); ?>
				<?= Sort::displayTh('user_name', 'Autor'); ?>
				<?= Sort::displayTh('text_ip', 'IP'); ?>
				<th></th>
			</tr>
			<tr>
				<th><?= Form::text('pageId', $input->get->pageId, array('style' => 'width: 65px')); ?></th>
				<th><?= Form::input('subject', $input->get->subject, array('style' => 'width: 120px')); ?></th>
				<th><?= Form::input('location', $input->get->location, array('style' => 'width: 120px')); ?></th>
				<th><?= Form::select('time', Form::option($pageTime, $input->get->time)); ?></th>
				<th><?= Form::select('editTime', Form::option($pageTime, $input->get->editTime)); ?></th>
				<th><?= Form::input('user', $input->get->user, array('style' => 'width: 100px')); ?></th>
				<th><?= Form::input('ip', $input->get->ip, array('style' => 'width: 100px')); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($result) : ?>
			<?php foreach ($result as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Page/View/' . $row['page_id']), $row['page_id']); ?></td>
				<td><?= Html::a(url($row['location_text']), $row['page_subject'], array('title' => 'Przejdź do strony')); ?></td>
				<td><?= $row['location_text']; ?></td>
				<td><?= User::formatDate($row['page_time']); ?></td>
				<td><?= User::formatDate($row['page_edit_time']); ?></td>
				<td><?= $row['user_id'] ? Html::a(url('adm/User/Submit/' . $row['user_id']), $row['user_name']) : ''; ?></td>
				<td><abbr class="whois-ip"><?= $row['text_ip']; ?></abbr></td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="7" style="text-align: center;">Brak rekordów spełniających podane kryteria.</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
<?= Form::close(); ?>

<?php if ($pagination->getTotalPages() > 1) : ?>
<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>
<?php endif; ?>
