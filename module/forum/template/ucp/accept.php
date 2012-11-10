<?php $selected = 'accept-icon'; include(Config::getBasePath() . 'module/user/template/ucp/_partialUserMenu.php'); ?>

<div id="box-watch" class="box">
	<?php if ($pagination->getTotalPages() > 1) : ?>
	<p>Strona <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></p>
	<?php endif; ?>

	<table class="table-visit" style="width: 100%">
		<thead>
		<tr>
			<th>Temat wątku</th>
			<th>Data napisania postu</th>
			<th>Data akceptacji odpowiedzi</th>
			<th>Użytkownik, który zaakceptował odpowiedź</th>
		</tr>
		</thead>
		<tbody>
		<?php if ($accept) : ?>
			<?php foreach ($accept as $row) : ?>
			<tr>
				<td><?= Html::a(url($row['location_text']) . '?p=' . $row['post_id'] . '#id' . $row['post_id'], $row['page_subject']); ?></td>
				<td><?= User::formatDate($row['post_time']); ?></td>
				<td><?= $row['accept_time'] ? User::formatDate($row['accept_time']) : '<em>(brak danych)</em>'; ?></td>
				<td><?= Html::a(url('@profile?id=' . $row['accept_user']), $row['user_name']); ?></td>
			</tr>
				<?php endforeach; ?>
			<?php else : ?>
		<tr>
			<td colspan="4" style="text-align: center;">Nikt jeszcze nie zaakceptował Twojej odpowiedzi na forum.</td>
		</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ($pagination->getTotalPages() > 1) : ?>
	<p>Strona <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></p>
	<?php endif; ?>
</div>

<div style="clear: both;"></div>