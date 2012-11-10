<?php if (!empty($stylesheet)) : ?>
<style type="text/css">
@import url('<?= url($stylesheet); ?>');
</style>
<?php endif; ?>

<div class="box-poll">
	<?= Form::open('', array('method' => 'post')); ?>
		<table>
			<caption><?= $poll_title; ?></caption>

			<?php if (isset($error)) : ?>
			<tr>
				<td colspan="3" class="error" style="padding-left: 35px"><?= $error; ?></td>
			</tr>
			<?php endif; ?>

			<?php foreach ($items as $row) : ?>
			<tr>
				<td class="poll">
					<label>
					<?php if (!$hasVoted && !$hasExpired) : ?>
					<?= Form::$componentName('pollItem[]', $row['item_id'], false) ?>
					<?php endif; ?>

					<?= $row['item_text'] ?>
					</label>
				</td>
				<td class="poll percentage"><strong style="width: <?= $row['percentage']; ?>%"></strong></td>
				<td class="poll"><?= sprintf('%s%% [%d]', $row['percentage'], $row['item_total']); ?></td>
			</tr>
			<?php endforeach; ?>

			<?php if ($hasExpired) : ?>
			<tr>
				<td colspan="3" class="poll"><em>Ankieta wygasła <?= User::date($poll_length + $poll_start); ?></em></td>
			</tr>
			<?php endif; ?>

			<?php if (!$hasVoted && !$hasExpired) : ?>
			<tr>
				<td colspan="3" class="poll"><?= Form::submit('', 'Głosuj'); ?></td>
			</tr>
			<?php endif; ?>
		</table>
	<?= Form::close(); ?>
</div>