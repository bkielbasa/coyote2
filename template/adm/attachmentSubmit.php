<script type="text/javascript">
<!--
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

<h1><?= $attachment_name; ?></h1>

<?= Form::open('', array('method' => 'post', 'onsubmit' => 'return confirm(\'UWAGA! Dany plik zostanie usunięty permanentnie. Nie będzie również widoczny w archiwum tekstów!\');')); ?>
	<fieldset>
		<legend>Dane załącznika</legend>

		<ol>

			<li>
				<label>Nazwa załącznika</label>
				<?= $attachment_name; ?>
			</li>
			<li>
				<label>Rozmiar załącznika</label> 
				<?= Text::fileSize($attachment_size); ?>
			</li>
			<li>
				<label>Data dodania</label>
				<?= User::formatDate($attachment_time); ?>
			</li>
			<?php if ($attachment_width) : ?>
			<li>
				<label>&nbsp;</label> 
				<?= Html::a(url('store/_aa/' . $attachment_file), Html::img(url('store/_aa/' . $attachment_file), array('width' => 160))); ?>
			</li>
			<li>
				<label>Szerokość</label>
				<?= $attachment_width; ?> px
			</li>
			<li>
				<label>Wysokość</label> 
				<?= $attachment_height; ?> px
			</li>
			<?php endif; ?>

			<li>
				<label></label>
				<?= Form::submit('delete', 'Usuń załącznik'); ?>
			</li>

		</ol>
	</fieldset>
<?= Form::close(); ?>

<?php if ($versions) : ?>
<table>
	<caption>Lista stron używających danego załącznika</caption>

	<thead>
		<tr>
			<th>Tytuł strony</th>
			<th>Ścieżka</th>
			<th>ID rewizji</th>
			<th>Data utworzenia</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($versions as $row) : ?>
		<tr>
			<td><?= $row['page_subject']; ?></td>
			<td><?= $row['location_text']; ?></td>
			<td><?= $row['text_id']; ?></td>
			<td><?= User::formatDate($row['text_time']); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>

<?php if ($thumbnail) : ?>
<?= Form::open('adm/Attachment/Purge/' . $attachment_id, array('method' => 'post')); ?>
	<table>
		<caption>Miniaturki</caption>

		<thead>
			<tr>
				<th>Szerokość miniaturki</th>
				<th>Rozmiar</th>
				<th>Data utworzenia</th>
				<th></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($thumbnail as $row) : ?>
			<tr>
				<td><?= $row['width']; ?> px</td>
				<td><?= Text::formatSize($row['size']); ?></td>
				<td><?= User::formatDate($row['time']); ?></td>
				<td class="checkbox">
					<?= Form::checkbox('delete[]', $row['width'], false); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="3">
					<?= Form::submit('', 'Usuń zaznaczone miniaturki', array('class' => 'delete-button')); ?>

				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystko"></a>
				</td>
			</tr>
		</tfoot>
	</table>
<?= Form::close(); ?>
<?php endif; ?>