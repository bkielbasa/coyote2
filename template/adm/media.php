<h1>Struktura plików i katalogów</h1>

<p>Dzięki tej opcji możesz swobonie poruszać się po drzewie plików i katalogów systemu. Bądź ostrżny ze zmianami. Mogą one mieć negatywny wpływ na prawidłowe działanie systemu.</p>

<?= Form::openMultipart(url('adm/Media/Upload?dir=' . $dir), array('method' => 'post')); ?>
	<fieldset>
		<legend>Wyślij plik na serwer</legend>

		<ol>
			<li>
				<label>Wybierz plik</label>
				<?= Form::file('Filedata', ''); ?>
			</li>
			<li>
				<label></label>
				<?= Form::checkbox('overwrite', 1, false); ?> Zastąp jeżeli plik o takiej nazwie już istnieje
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Wyślij na serwer', array('class' => 'add-button')); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>

<?php if ($part) : ?>
<div id="part">
	<strong>Przeglądasz:</strong> <?= $part; ?>
</div>
<?php endif; ?>

<table id="media">
	<thead>
		<tr>
			<th>Nazwa pliku</th>
			<th>Rozmiar pliku</th>
			<th>Data modyfikacji</th>
			<th>Prawa do pliku/katalogu</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php if (isset($rootDir)) : ?>
		<tr>
			<td colspan="5" style="border-bottom: 1px dashed #ddd">
				<?= Html::a(url('adm/Media?dir=' . $rootDir), 'Katalog główny', array('class' => 'home')); ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if (isset($backDir)) : ?>
		<tr>
			<td colspan="5" style="border-bottom: 1px dashed #ddd">
				<?= Html::a(url('adm/Media?dir=' . $backDir), 'Katalog wyżej', array('class' => 'up')); ?>
			</td>
		</tr>
		<?php endif; ?>

		<?php foreach ($media as $row) : ?>
		<tr <?= Text::alternate('', 'class="alternate"'); ?>>
			<td><?= $row['isDir'] ? Html::a(url('adm/Media?dir=' . $dir . '/' . $row['filename']), $row['filename'], array('class' => 'folder')) : Html::a(url('adm/Media/Submit?path=' . $dir . '/' . $row['filename']), $row['filename'], array('class' => $row['suffix'])); ?></td>
			<td><?= $row['isDir'] ? '' : Text::formatSize($row['filesize']); ?></td>
			<td><?= date('d-m-Y H:i', $row['filemtime']); ?></td>
			<td><?= $row['fileperms']; ?></td>
			<td><?= $row['isDir'] ? '' : Form::button('', '', array('title' => 'Usuń', 'class' => 'delete', 'onclick' => 'window.location.href = \'' . url('adm/Media/Delete?path=' . $dir . '/' . $row['filename']) . '\'')); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>