<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 2)
			{
				window.location.href = '<?= url('adm/Menu/Submit'); ?>';
			}
			else
			{
				if (confirm('Czy chcesz usunąć zaznaczone menu?'))
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

<h1>Konfiguracja menu</h1>

<p>Na tej stronie możesz dodać i skonfigurować menu, które następnie może być dodane w bloku.</p>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Lista pozycji menu</caption>

		<thead>
			<tr>
				<th>ID</th>
				<th>Nazwa menu</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($menu) : ?>
			<?php foreach ($menu as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Menu/Submit/' . $row['menu_id']), $row['menu_id']); ?></td>
				<td><?= Html::a(url('adm/Menu/Submit/' . $row['menu_id']), $row['menu_name']); ?></td>
				<td class="checkbox"><?= Form::checkbox('delete[]', $row['menu_id'], false); ?></td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="3" style="text-align: center;">Brak menu w bazie danych</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2">
					<select  onchange="selectAction(this)">
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Dodaj nowe menu</option>
					</select>
				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystkie"></a>				
				</td>
			</tr>
		</tfoot>
	</table>
</form>
