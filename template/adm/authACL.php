<script type="text/javascript">
<!--
	function addNew()
	{
		$('<tr><td></td><td><?= Form::input('text[]', ''); ?></td><td><?= Form::input('label[]', '', array('size' => 50)); ?></td><td><?= Form::select('default[]', Form::option(array('Nie', 'Tak'), 0)); ?></td><td><?= Form::checkbox('delete[]', 1, false, array('onclick' => 'deleteRow(this)')); ?></td></tr>').appendTo('tbody');
	}

	function deleteRow(element)
	{
		$($(element).parent('td').parent()).remove();
	}

	function selectAction(element)
	{
		if (element.value != 0)
		{
			if (confirm('Czy na pewno chcesz usunąć zaznaczone pozycję?'))
			{
				$('form').submit();
			}
		}
	}

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

<h1>ACL</h1>

<p>Tutaj znajduje się lista <strong>uprawnień</strong>, które następnie możesz przypisywać danym grupom. Przykładowo opcja 
<i>a_</i> może regulować dostęp do panelu administracyjnego. Następnie możesz przypisać tę opcję do danych grup. W ten sposób 
wybrane przez Ciebie grupy będą miały dostęp do panelu.</p>

<?= Form::open('', array('method' => 'post')); ?>
	<table>
		<caption>Lista ACL</caption>

		<thead>
			<tr>
				<th>ID</th>
				<th>Opcja</th>
				<th>Opis</th>
				<th>Domyślnie</th>
				<th></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($option as $row) : ?>
			<?= Form::input('text[' . $row['option_id'] . ']', $row['option_text'], array('type' => 'hidden')); ?>
			<tr>
				<td><?= $row['option_id']; ?></td>
				<td><?= $row['option_text']; ?></td>
				<td><?= Form::input('label[' . $row['option_id'] . ']', $row['option_label'], array('size' => 50)); ?></td>
				<td><?= Form::select('default[' . $row['option_id'] . ']', Form::option(array('Nie', 'Tak'), $row['option_default'])); ?></td>
				<td class="checkbox"><?= Form::checkbox('delete[' . $row['option_id'] . ']', 1, false); ?></td>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4">
					<select onchange="return selectAction(this);">
						<option value="0">akcja...</option>
						<option>Usuń zaznaczone</option>
						<option>Zapisz zmiany</option>
					</select>

				</td>
				<td class="checkbox"><a id="selectAll" title="Zaznacz wszystko"></a></td>
			</tr>
		</tfoot>
	</table>

	<?= Form::button('', 'Dodaj nowe', array('onclick' => 'addNew()')); ?>
</form>