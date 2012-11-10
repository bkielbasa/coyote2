<script type="text/javascript">
<!--

	var deleteUrl =  '<?= url('@user?controller=Pm&action=Trash&id='); ?>';

	function selectItem(itemId)
	{
		if (itemId.value > 0)
		{
			$.windowConfirm(
			{
				windowTitle: 'Usuwanie wiadomości',
				windowMessage: 'Czy chcesz usunąć zaznaczone wątki? Uwaga! Usunięta zostanie cała konwersacja z użytkownikiem',
				onYesClick: function()
				{
					document.form.submit();
				}
			});
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

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<?php $selected = 'pm-icon'; include('_partialUserMenu.php'); ?>

<div id="box-user" class="box">

	<div class="box box-pm" style="margin-top: 40px;">
		<?php include('_partialPmMenu.php'); ?>

		<?php if ($pagination->getTotalPages() > 1) : ?>
		<div class="pagination">Strona <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></div>
		<?php endif; ?>

		<?php if ($pm) :?>
		<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
			<table>
				<tbody>
					<?php foreach ($pm as $row) : ?>
					<tr class="<?= isset($unreadCount[$row['user_id']]) ? 'unread' : ''; ?>">
						<td style="width: 20px" class="<?= isset($unreadCount[$row['user_id']]) ? 'unread' : 'read'; ?>">
							<?= Form::checkbox('delete[]', $row['pm_id'], false); ?>
						</td>
						<td style="width: 50px">
							<?php if (!empty($row['user_photo'])) : ?>
							<img src="<?= Url::site(); ?>store/_a/<?= $row['user_photo']; ?>" width="70" height="60" />
							<?php else : ?>
							<img src="<?= Url::site(); ?>template/img/avatar.jpg" />
							<?php endif; ?>
						</td>
						<td style="width: 10px">
							<?php if ($row['pm_folder'] == Pm_Model::SENTBOX) : ?>
							<img style="vertical-align: middle;" title="Wiadomośc nadawcza" src="<?= url('module/user/template/img/sent-message.png'); ?>"/>
							<?php else : ?>
							<img style="vertical-align: middle;" title="Wiadomość odbiorcza" src="<?= url('module/user/template/img/recieve-message.png'); ?>" />
							<?php endif; ?>
						</td>
						<td style="width: 120px">
							<?= Html::a(url('@profile?id=' . $row['user_id']), $row['user_name']); ?>
							<span class="date"><?= User::formatDate($row['pm_time']); ?></span>
						</td>
						<td>
							<p><?= Html::a(url('@user?controller=Pm&action=View&id=' . $row['pm_id']) . '#pm' . $row['pm_id'], (isset($unreadCount[$row['user_id']]) > 0 ? '(' . $unreadCount[$row['user_id']] . ') ' : ' ') . Text::limitHtml(Text::plain($row['pm_message'], false), 160), array('class' => 'anchor')); ?></p>
						</td>
						<td class="checkbox">
							<a class="trash" data-pm-id="<?= $row['pm_id']; ?>" title="Usuń wiadomość"></a>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<td class="checkbox">
							<a id="selectAll" href="#"></a>
						</td>
						<td colspan="5">

							<select onchange="selectItem(this);">
								<option>wybierz...</option>
								<option value="1">usuń zaznaczone wątki</option>
							</select>

						</td>
					</tr>
				</tfoot>
			</table>
		<?= Form::close(); ?>
		<?php else : ?>

		<p style="text-align: center;">Brak wiadomości w skrzynce.</p>
		<?php endif; ?>

		<?php if ($pagination->getTotalPages() > 1) : ?>
		<div class="pagination">Strona <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></div>
		<?php endif; ?>

	</div>
</div>

<div style="clear: both;"></div>