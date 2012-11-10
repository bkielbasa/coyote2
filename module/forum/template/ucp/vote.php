<?php $selected = 'vote-icon'; include(Config::getBasePath() . 'module/user/template/ucp/_partialUserMenu.php'); ?>

<div id="box-watch" class="box">
	<?php if ($pagination->getTotalPages() > 1) : ?>
	<p>Strona <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></p>
	<?php endif; ?>

	<table class="table-visit" style="width: 100%">
		<thead>
		<tr>
			<th>Temat wątku</th>
			<th>Data napisania postu</th>
			<th>Ocena</th>
			<th>Data wystawienia oceny</th>
		</tr>
		</thead>
		<tbody>
		<?php if ($votes) : ?>
			<?php foreach ($votes as $row) : ?>
			<tr>
				<td><?= Html::a(url($row['location_text']) . '?p=' . $row['post_id'] . '#id' . $row['post_id'], $row['page_subject']); ?></td>
				<td><?= User::formatDate($row['post_time']); ?></td>
				<td><?= $row['value'] > 0 ? '+1' : '-1'; ?></td>
				<td><?= $row['vote_time'] ? User::formatDate($row['vote_time']) : '--'; ?></td>
			</tr>
			<?php endforeach; ?>
		<?php else : ?>
		<tr>
			<td colspan="4" style="text-align: center;">Brak odnotowanych ocen.</td>
		</tr>
		<?php endif; ?>
		</tbody>
	</table>

	<?php if ($pagination->getTotalPages() > 1) : ?>
	<p>Strona <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></p>
	<?php endif; ?>
</div>

<div style="clear: both;"></div>