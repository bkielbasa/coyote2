<script type="text/javascript">
<!--

	var deleteUrl =  '<?= url('@user?controller=Pm&action=Trash&id='); ?>';

	$(document).ready(function()
	{
		$('#message-container').height($(window).height() - 400);
		$('textarea').width($('.box-pm').width() - 58);

		if ($.browser.mozilla)
		{
			$('#message-container').animate({ scrollTop: $(window.location.hash).offset().top }, { duration: 100});
		}
	});
//-->
</script>

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<?php $selected = 'pm-icon'; include('_partialUserMenu.php'); ?>

<div id="box-user" class="box" style="margin-top: 40px">

	<div class="box box-pm" style="margin-top: 40px;">
		<?php include('_partialPmMenu.php'); ?>

		<div id="message-container">
			<table>
				<tbody>
					<?php foreach ($messages as $row) : ?>
					<?php $color = Text::alternate('even', 'odd'); ?>

					<tr id="pm<?= $row['pm_id']; ?>" class="message-header <?= $color; ?> <?= !$row['pm_read'] && $row['pm_folder'] == Pm_Model::INBOX ? 'unread' : ''; ?>">
						<td style="width: 50px <?= !$row['folding'] ? ';border-bottom: none' : ''; ?>">
							<?php if (!empty($row['user_photo'])) : ?>
							<img src="<?= Url::site(); ?>store/_a/<?= $row['user_photo']; ?>" width="43" />
							<?php else : ?>
							<img src="<?= Url::site(); ?>template/img/avatar.jpg" width="43" />
							<?php endif; ?>
						</td>

						<td class="message-user-name" <?= !$row['folding'] ? 'style="border-bottom: none;"' : ''; ?>'>
							<?= Html::a(url('@profile?id=' . $row['user_id']), $row['user_name']); ?>

							<span class="date" <?= $row['folding'] ? 'style="display: none;"' : ''; ?>><?= User::formatDate($row['pm_time']); ?> <?= $row['pm_read'] > 1 && $row['pm_folder'] == Pm_Model::SENTBOX ? ' (przeczytana: ' . User::formatDate($row['pm_read']) . ')' : ''; ?></span>
							<span class="snippet" <?= !$row['folding'] ? 'style="display: none;"' : ''; ?>><?= $row['pm_snippet']; ?></span>
						</td>

						<td class="message-date" <?= !$row['folding'] ? 'style="border-bottom: none;"' : ''; ?>>
							<span class="date" style="<?= !$row['folding'] ? 'display: none;"' : ''; ?>"><?= User::formatDate($row['pm_time']); ?> <?= $row['pm_read'] > 1 && $row['pm_folder'] == Pm_Model::SENTBOX ? ' (przeczytana: ' . User::formatDate($row['pm_read']) . ')' : ''; ?></span>

							<a title="Usuń tę wiadomość" class="delete-icon" data-pm-id="<?= $row['pm_id']; ?>"></a>
						</td>
					</tr>
					<tr style="<?= $row['folding'] ? 'display: none' : ''; ?>" class="<?= $color; ?> <?= !$row['pm_read'] && $row['pm_folder'] == Pm_Model::INBOX ? 'unread' : ''; ?>">
						<td style="width: 50px">

						</td>
						<td colspan="2" class="message-content">
							<?= $row['pm_message']; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

	<div id="message-form">
		<?= Form::open('@user?controller=Pm&action=Submit&user=' . $nextId, array('method' => 'post')); ?>

			<?php if (User::data('photo')) : ?>
			<img src="<?= Url::site(); ?>store/_a/<?= User::data('photo'); ?>" width="43" />
			<?php else : ?>
			<img src="<?= Url::site(); ?>template/img/avatar.jpg" width="43" />
			<?php endif; ?>

			<?= Form::textarea('text', $input->post('text'), array('placeholder' => 'Kliknij, aby odpowiedzieć...', 'tabindex' => 1, 'style' => 'resize: none')); ?>
		<?= Form::close(); ?>
	</div>
</div>

<div style="clear: both;"></div>