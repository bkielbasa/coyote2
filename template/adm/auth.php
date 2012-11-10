<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{			
			if (confirm('Czy na pewno chcesz usunąć zaznaczone grupy?'))
			{
				document.form.submit();
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

<h1>Uprawnienia dla grup</h1>

<p>Tutaj możesz ustalać uprawnienia dla danych grup. Wystarczy podać nazwę grupy i nacisnąć przycisk, aby zobaczyć listę uprawnień dla danej grupy.</p>

<?= Form::open('adm/Auth/Submit', array('method' => 'get')); ?>
	<fieldset>
		<legend>Szukaj grupy</legend>

		<ol>		
			<li>
				<label>Grupa</label>
				<?= Form::input('name', ''); ?>
			</li>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('', 'Wyświetl listę uprawnień'); ?>
			</li>
		</ol>
	</fieldset>
</form>	

<h1>Lista group</h1>

Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?>

<?= Form::open('adm/Group', array('method' => 'post', 'name' => 'post')); ?>
	<table>
		<caption>Dostepne grupy</caption>
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
				<td><?= Html::a(url('adm/Auth/Submit/' . $row['group_id']), $row['group_id']); ?></td>
				<td><?= Html::a(url('adm/Auth/Submit/' . $row['group_id']), $row['group_name']); ?></td>
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