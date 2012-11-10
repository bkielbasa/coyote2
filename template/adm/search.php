<h1>Konfiguracja wyszukiwarki</h1>

<p>Na tej stronie możesz skonfigurować jaki system będzie odpowiedzialny za wyszukiwanie
oraz indeksację treści na stronie.</p>

<?= Form::open('', array('method' => 'post')); ?>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Mechanizm wyszukiwarki</th>
				<th>Włączony</th>
				<th>Domyślny</th>
			</tr>
		</thead>
		
		<tbody>
			<?php foreach ($search as $row) : ?>
			<tr>
				<td><?= $row['search_id']; ?></td>
				<td><?= $row['search_name']; ?></td>
				<td><?= Form::checkbox('enable[' . $row['search_id'] . ']', 1, (bool) $row['search_enable']); ?></td>
				<td><?= Form::radio('default', $row['search_id'], (bool) $row['search_default'], ($row['search_enable'] ? array() : array('disabled' => 'disabled'))); ?></td>
			</tbody>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4">
					<select onchange="$('form').submit();">
						<option>akcja...</option>
						<option value="1">Zapisz zmiany</option>
					</select>
				</td>
			</tr>
		</tfoot>
	</table>
<?= Form::close(); ?>


			