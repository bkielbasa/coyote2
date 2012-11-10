<h1>Zainstalowane wtyczki</h1>

<p>Strona przedstawia listę zainstalowanych wtyczek. W każdej chwili możesz odinstalować daną wtyczkę poprzez kliknięcie w przycisk <i>Odinstaluj</i>.</p>

<table>
	<caption>Zainstalowane wtyczki</caption>

	<thead>
		<tr>
			<th>ID</th>
			<th>Nazwa wtyczki</th>
			<th>Wersja wtyczki</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($plugins as $row) : ?>
		<tr>
			<td><?= Html::a(url('adm/Plugin/Submit/' . $row['plugin_id']), $row['plugin_id']); ?></td>
			<td><?= Html::a(url('adm/Plugin/Submit/' . $row['plugin_id']), $row['plugin_text']); ?></td>
			<td><?= $row['plugin_version']; ?></td>
			<td class="checkbox">
				<?= Form::button('', 'Odinstaluj', array('class' => 'delete-plugin-button', 'onclick' => 'window.location.href = \'' . url('adm/Plugin/Uninstall/' . $row['plugin_name']) . '\'')); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>