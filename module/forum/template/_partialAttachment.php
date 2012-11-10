<?php if (@$attachment) : ?>
<?php foreach ($attachment as $row) : ?>
<?= Form::hidden('attachment[' . $row['attachment_file'] . ']', $row['attachment_name'], array('class' => 'attachment-' . $row['attachment_file'])); ?>
<?php endforeach; ?>
<?php endif; ?>

<div class="attachments editor-tab" style="display: none;" title="Załączniki">
	<div class="bubble">
		<table>
			<thead>
				<tr>
					<th>Nazwa pliku</th>
					<th>Typ MIME</th>
					<th>Data dodania</th>
					<th>Rozmiar</th>
					<th style="width: 20px;"></th>
				</tr>
			</thead>
			<tbody>
				<?php if (@$attachment) : ?>
				<?php foreach ($attachment as $row) : ?>
				<tr id="id-<?= $row['attachment_file']; ?>" class="attachment-row <?= Text::alternate('', 'even'); ?>">
					<td><a class="append-attachment-button" title="Wstaw załącznik do tekstu"><?= $row['attachment_name']; ?></a></td>
					<td style="text-align: center;"><?= $row['attachment_mime']; ?></td>
					<td style="text-align: center;"><?= User::date($row['attachment_time']); ?></td>
					<td><?= Text::formatSize($row['attachment_size']); ?></td>
					<td><a class="delete-attachment-button" title="Usuń załącznik"></a></td>
				</tr>
				<?php endforeach; ?>
				<?php else : ?>
				<tr>
					<td colspan="5" style="text-align: center;">Brak załączników.</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<input type="button" class="upload-attachment-button" value="Dodaj załącznik (plików: <?= 15 - count(@$attachment); ?>)" />
	</div>

	<small style="margin-left: 0">Maksymalny rozmiar załącznika nie może przekraczać <strong><?= Config::getItem('attachment.limit'); ?></strong>. Dostępne rozszerzenia: <strong><?= implode(', ', explode(',', Config::getItem('attachment.suffix'))); ?></strong></small>
</div>