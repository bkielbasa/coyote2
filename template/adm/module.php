<h1>Lista modułów</h1>

<p>Na tej stronie możesz aktywować lub dezaktywować moduły znalezione w tym systemie. Usuwając niektóre moduły musisz się liczyć z ewentualną utratą danych.</p>

<p class="note"><b>UWAGA!</b> Niektóre moduły wymagają utworzenia tabeli bazy danych. Wymagane jest odpowiednie uprawnienie do tworzenia tabel i/lub triggerów.</p>


<table>
	<caption>Dostępne moduły</caption>

	<thead>
		<tr>
			<th>Nazwa modułu</th>
			<th>Wersja modułu</th>
			<th>Autor</th>
			<th>Przystosowany do wersji</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($modules as $dir => $row) : ?>
		<tr>
			<td><?= $row['text']; ?></td>
			<td><?= $row['version']; ?></td>
			<td><?= $row['author']; ?></td>
			<td>
			<?php if ($row['system']) : ?>
				<?php if (version_compare(Config::getItem('version'), $row['system']) == 1) : ?>
				<label style="color: red;" title="Moduł jest przystosowany do starszej wersji systemu. Moduł może nie działać prawidłowo"><?= $row['system']; ?></label>
				<?php elseif (version_compare(Config::getItem('version'), $row['system']) == -1) : ?>
				<label style="color: green;" title="Moduł jest przystosowany do nowszej wersji systemu. Może nie działać prawidłowo"><?= $row['system']; ?></label>
				<?php else : ?>
				<?= $row['system']; ?>
				<?php endif; ?>
			<?php endif; ?>
			</td>
			<td class="checkbox"><?= Form::button('', 'Zainstaluj', array('class' => 'add-module-button', 'onclick' => 'window.location.href = \'' . url('adm/Module/Install/' . ($row['name']) . '\''))); ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>