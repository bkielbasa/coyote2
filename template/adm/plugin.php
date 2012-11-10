<h1>Lista wtyczek</h1>

<p>Na tej stronie znajduje się lista wtyczek, które nie są właczone, a które możesz zainstalować.</p>

<p class="note"><b>UWAGA!</b> Niektóre moduły wymagają utworzenia tabeli bazy danych. Wymagane jest odpowiednie uprawnienie do tworzenia tabel i/lub triggerów.</p>

<table>
	<caption>Dostępne wtyczki</caption>

	<thead>
		<tr>
			<th>Nazwa wtyczki</th>
			<th>Wersja wtyczki</th>
			<th>Autor</th>
			<th>Przystosowany do wersji</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php if (!$plugins) : ?>
		<tr>
			<td colspan="5" style="text-align: center;">Brak wtyczek możliwych do zainstalowania</td>
		</tr>
	<?php else : ?>
	<?php foreach ($plugins as $dir => $row) : ?>
		<tr>
			<td><?= $row['text']; ?></td>
			<td><?= $row['version']; ?></td>
			<td><?= $row['author']; ?></td>
			<td>
			<?php if ($row['system']) : ?>
				<?php if (version_compare(Config::getItem('version'), $row['system']) == 1) : ?>
				<label style="color: red;" title="Wtyczka jest przystosowana do starszej wersji systemu. Może nie działać prawidłowo"><?= $row['system']; ?></label>
				<?php elseif (version_compare(Config::getItem('version'), $row['system']) == -1) : ?>
				<label style="color: green;" title="Wtyczka jest przystosowana do nowszej wersji systemu. Może nie działać prawidłowo"><?= $row['system']; ?></label>
				<?php else : ?>
				<?= $row['system']; ?>
				<?php endif; ?>
			<?php endif; ?>
			</td>
			<td class="checkbox"><?= Form::button('', 'Zainstaluj', array('class' => 'add-plugin-button', 'onclick' => 'window.location.href = \'' . url('adm/Plugin/Install/' . ($row['name']) . '\''))); ?></td>
		</tr>
	<?php endforeach; ?>
	<?php endif; ?>
	</tbody>
</table>