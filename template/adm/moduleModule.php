<h1>Zainstalowane moduły</h1>

<p>Strona przedstawia listę zainstalowanych modułów. W każdej chwili możesz odinstalować dany moduł poprzez kliknięcie w przycisk <i>Odinstaluj</i>.</p>

<table>
	<caption>Zainstalowane moduły</caption>

	<thead>
		<tr>
			<th>ID</th>
			<th>Nazwa modułu</th>
			<th>Wersja modułu</th>
			<th></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($modules as $row) : ?>
		<tr>
			<td><?= Html::a(url('adm/Module/Submit/' . $row['module_id']), $row['module_id']); ?></td>
			<td><?= Html::a(url('adm/Module/Submit/' . $row['module_id']), $row['module_text']); ?></td>
			<td><?= $row['module_version']; ?></td>
			<td class="checkbox">
				<?= Form::button('', 'Odśwież', array('class' => 'refresh-button', 'onclick' => 'window.location.href = \'' . url('adm/Module/Refresh/' . $row['module_id']) . '\'')); ?>
			</td>
			<td class="checkbox">
				<?php if ($row['module_type'] == Module_Model::NORMAL) : ?>
				<?= Form::button('', 'Odinstaluj', array('class' => 'delete-module-button', 'onclick' => 'window.location.href = \'' . url('adm/Module/Uninstall/' . $row['module_name']) . '\'')); ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>