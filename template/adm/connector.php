<h1>Konfiguracja łączników</h1>

<p class="note">Łączniki są <strong>zaawansowanym</strong> elementem obsługi stron przez system. Zanim dokonasz zmian na tej stronie, <strong>upewnij się</strong>, że wiesz, co robisz!</p>

<table>
	<caption>Lista łączników</caption>

	<thead>
		<tr>
			<th>ID</th>
			<th>Nazwa</th>
			<th>Moduł</th>
			<th>Opis</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($connector as $row) : ?>
		<tr>
			<td><?= Html::a(url('adm/Connector/Submit/' . $row['connector_id']), $row['connector_id']); ?></td>
			<td><?= Html::a(url('adm/Connector/Submit/' . $row['connector_id']), $row['connector_name']); ?></td>
			<td><?= $row['module_text']; ?></td>
			<td><?= $row['connector_text']; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>