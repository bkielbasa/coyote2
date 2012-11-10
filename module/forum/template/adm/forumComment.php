<h1>Lista komentarzy forum</h1>

Strony [<?= $pagination; ?>] z <?= $totalPages; ?>

<?= Form::open('', array('method' => 'post')); ?>
	<table>
		<caption>Lista komentarzy na forum</caption>
		<thead>
			<tr>
				<th style="width: 20%"><?= Sort::display('page_subject', 'Temat wątku'); ?></th>
				<th style="width: 15%"><?= Sort::display('comment_id', 'Data napisania'); ?></th>
				<th style="width: 15%"><?= Sort::display('user_name', 'Autor'); ?></th>
				<th>Treść</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($comment as $row) : ?>
			<tr>
				<td><?= Html::a(url($row['location_text']) . '?p=' . $row['post_id'] . '#comment-' . $row['comment_id'], $row['page_subject']); ?></td>
				<td><?= User::formatDate($row['comment_time']); ?></td>
				<td><?= Html::a(url('adm/User/Submit/' . $row['comment_user']), $row['user_name']); ?></td>
				<td><?= $row['comment_text']; ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?= Form::close(); ?>

Strony [<?= $pagination; ?>] z <?= $totalPages; ?>