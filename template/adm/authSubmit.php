<h1>Uprawnienia grupy <?= $group_arr->group_name; ?></h1>

<p>Na tej stronie możesz edytować uprawnienia dla danej grupy!</p>

<?= Form::open('adm/Auth/Submit/' . $group_id, array('method' => 'post')); ?>
	<table>
		<caption>Obecne uprawnienia dla grupy</caption>
		<thead>
			<tr>
				<th>Opcja</th>
				<th>Opis</th>
				<th>Uprawnienie</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data as $row) : ?>
			<tr>
				<td><?= $row['option_text']; ?></td>
				<td><?= $row['option_label']; ?></td>
				<td><?= Form::select('data[' . $row['option_id'] . ']', Form::option(array('Nie', 'Tak'), $row['data_value'])); ?></td>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?= Form::input('', 'Zapisz zmiany', array('class' => 'button', 'type' => 'submit', 'onclick' => 'return confirm(\'Uwaga! Wprowadzone zmiany mogą wpłynąc na bezpieczeństwo. Kontynuować?\');')); ?>
</form>