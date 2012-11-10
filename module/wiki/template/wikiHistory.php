<?= Form::open(url('Diff/' . $page->getLocation())); ?>
	<table style="width: 100%">
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th>ID rewizji</th>
				<th>Data utworzenia</th>
				<th>Autor</th>
				<th>Opis zmian</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($text as $index => $row) : ?>
			<tr>
				<td>
					<?php if ($index < sizeof($text) -1) :?>
					<?= Form::radio('r1', $row['text_id']); ?>
					<?php endif; ?>
				</td>
				<td>
					<?php if ($index > 0) : ?>
					<?= Form::radio('r2', $row['text_id']); ?>
					<?php endif; ?>
				</td>
				<td>
					<?= $row['text_id']; ?>
				</td>
				<td><?= User::formatDate($row['text_time']); ?></td>
				<td><?= Html::a(url('@profile?id=' . $row['text_user']), $row['user_name']); ?></td>
				<td><?= $row['text_log']; ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?= Form::submit('', 'Pokaż różnicę'); ?>
<?= Form::close(); ?>