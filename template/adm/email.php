<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 3)
			{
				window.location.href = '<?= url('adm/Email/Submit'); ?>';
			}
			else
			{
				if (confirm('Zmiany mogą mieć wpływ na działanie systemu. Kontynuować?'))
				{
					document.form.submit();
				}			
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

<h1>Zarządzanie e-mailami</h1>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Lista szablonów e-mail</caption>
		<thead>
			<tr>
				<th>ID</th>
				<th>Unikalna nazwa</th>
				<th>Opis</th>
				<th></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($email) : ?>
			<?php foreach ($email as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Email/Submit/' . $row['email_id']), $row['email_id']); ?></td>
				<td><?= Html::a(url('adm/Email/Submit/' . $row['email_id']), $row['email_name']); ?></td>
				<td><?= $row['email_description']; ?></td>
				<td class="checkbox"><?= Form::button('', 'Testuj szablon', array('onclick' => 'window.location.href = \'' . url('adm/Email/Send/' . $row['email_id']) . '\'')); ?></td>
				<td class="checkbox"><?= Form::checkbox('delete[]', $row['email_id'], false); ?></td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="5" style="text-align: center;">Brak szablonów e-mail w bazie danych</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4">
					<select  onchange="selectAction(this)">
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Zapisz zmiany</option>
						<option value="3">Dodaj nowy szablon</option>
					</select>
				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystkie"></a>
				</td>
			</tr>
		</tfoot>
	</table>
<?= Form::close(); ?>