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

	function selectItem(itemId)
	{
		if (itemId.value > 0)
		{
			if (confirm('Czy na pewno chcesz usunąć zaznaczone rekordy?'))
			{
				$('form[name=form]').attr('method', 'post');
				document.form.submit();
			}
		}
	}

	var checked = false;

	$(document).ready(function()
	{
		$('#selectAll').bind('click', function()
		{
			$('input:checkbox').attr('checked', !checked);
			checked = !checked;
		}
		);
	});
//-->
</script>

<h1>Dziennik zdarzeń</h1>

<p>Dzięki tej stronie, możesz przeglądać dziennik błędów i komunikatów systemu.
W polach <i>nazwa użytkownika</i> oraz <i>adres IP</i>, możesz zastosować znak * jeżeli chcesz wyszukiwać po masce.</p>

<?php if (isset($session->note)) : ?>
<p class="note"><?= $session->getAndDelete('note'); ?></p>
<?php endif; ?>

<?php if ($pagination->getTotalPages() > 1) : ?>
<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>
<?php endif; ?>

<?= Form::open('', array('method' => 'get', 'name' => 'form')); ?>
	<table>
		<caption>Przeglądaj dziennik zdarzeń</caption>
		<thead>
			<tr>
				<?= Sort::displayTh('log_id', 'ID'); ?>
				<?= Sort::displayTh('page_id', 'ID strony'); ?>
				<?= Sort::displayTh('log_type', 'Zdarzenie'); ?>
				<?= Sort::displayTh('user_name', 'Użytkownik'); ?>
				<?= Sort::displayTh('log_time', 'Data i czas'); ?>
				<?= Sort::displayTh('log_ip', 'IP'); ?>
				<th></th>
			</tr>
			<tr>
				<th><?= Form::text('id', $input->get->id, array('style' => 'width: 20px')); ?></th>
				<th><?= Form::text('pageId', $input->get->pageId, array('style' => 'width: 65px')); ?></th>
				<th><?= Form::select('type', Form::option($logType, $input->get->type)); ?></th>
				<th><?= Form::input('user', $input->get->user, array('style' => 'width: 120px')); ?></th>
				<th><?= Form::select('time', Form::option($logTime, $input->get->time)); ?></th>
				<th><?= Form::input('ip', $input->get->ip, array('style' => 'width: 100px')); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($log) : ?>
			<?php foreach ($log as $row) : ?>
			<tr>
				<td><?= $row['page_id'] ? Html::a(url('adm/Page/View/' . $row['page_id']), $row['log_id']) : $row['log_id']; ?></td>
				<td><?= $row['page_id'] ? Html::a(url($row['location_text']), $row['page_subject'], array('title' => 'Przejdź do strony')) : '<i>Brak</i>'; ?></td>
				<td>
					<?= element($logType, $row['log_type']); ?>

					<?php if ($row['log_message']) : ?>
					<br />» <small><?= $row['log_message']; ?></small>
					<?php endif; ?>
				</td>
				<td><?= $row['log_user'] == User::ANONYMOUS ? $row['user_name'] : Html::a(url('adm/User/Submit/' . $row['log_user']), $row['user_name']); ?></td>
				<td><?= User::formatDate($row['log_time']); ?></td>
				<td><abbr class="whois-ip"><?= $row['log_ip']; ?></abbr></td>
				<td class="checkbox">
					<?= Form::checkbox('delete[]', $row['log_id'], false); ?>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="7" style="text-align: center;">Brak rekordów spełniających podane kryteria.</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="6">
					<select onchange="selectItem(this);">
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone rekordy</option>
					</select>
				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystkie"></a>
				</td>
			</tr>
		</tfoot>
	</table>
<?= Form::close(); ?>

<?php if ($pagination->getTotalPages() > 1) : ?>
<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>
<?php endif; ?>


<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Usuwanie wpisów</legend>

		<ol>
			<li>
				<label>Usuń wpisy z kategorii:</label>
				<?= Form::select('purge', Form::option($purge)); ?> z datą starszą niż <?= Form::select('logTime', Form::option($logTime)); ?>
			</li>
			<li><label>&nbsp;</label>
				<?= Form::submit('', 'Usuń', array('class' => 'delete-button')); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>
