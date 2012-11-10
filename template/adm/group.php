<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 2)
			{
				window.location.href = '<?= url('adm/Group/Submit'); ?>';
			}
			else
			{
				if (confirm('Czy na pewno chcesz usunąć zaznaczone grupy?'))
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

<h1>Lista group</h1>

<p>Na tej stronie możesz dodawać lub edytować istniejące grupy.</p>

Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Grupy</caption>
		<thead>
			<tr>
				<th>ID</th>
				<th>Nazwa grupy</th>
				<th>Opis grupy</th>
				<th>Grupa systemowa</th>
				<th>Ilość członków</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php load_helper('array'); ?>
		<?php foreach ($group as $row) : ?>
			<tr>
				<td><?= $row['group_type'] ? Html::a(url('adm/Group/Submit/' . $row['group_id']), $row['group_id']) : $row['group_id']; ?></td>
				<td><?= $row['group_type'] ? Html::a(url('adm/Group/Submit/' . $row['group_id']), $row['group_name']) : $row['group_name']; ?></td>
				<td><?= $row['group_desc']; ?></td>
				<td><?= element(array('Nie', 'Tak'), !$row['group_type']); ?></td>
				<td><?= $row['group_members']; ?></td>
				<td class="checkbox"><?= $row['group_type'] ? Form::checkbox('delete[]', $row['group_id'], false) : ''; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
					<select  onchange="selectAction(this)">
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Dodaj nową grupę</option>
					</select>		
				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystkie"></a>
				</td>
			</tr>
		</tfoot>
	</table>
</form>

Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?>