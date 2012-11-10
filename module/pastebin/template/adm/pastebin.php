<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{			
			if (confirm('Czy chcesz usunąć zaznaczone rekordy?'))
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

<h1>Pastebin</h1>

<p>Na tej stronie możesz usunąć zaznaczone rekordy.</p>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Lista wpisów Pastebin</caption>
		<thead>
			<tr>
				<th>ID</th>
				<th>Autor wpisu</th>
				<th>Data utworzenia</th>
				<th>Data przedawnienia</th>
				<th></th>
			<tr>
		</thead>
		<tbody>
			<?php foreach ($pastebin as $row) : ?>
			<tr>
				<td><?= $row['pastebin_id']; ?></td>
				<td><?= $row['pastebin_user'] > User::ANONYMOUS ? $row['user_name'] : def($row['pastebin_username'], 'Anonim'); ?></td>
				<td><?= User::formatDate($row['pastebin_time']); ?></td>
				<td><?= $row['pastebin_expire'] > 0 ? User::formatDate($row['pastebin_expire']) : 'Brak'; ?></td>
				<td class="checkbox">
					<?= Form::checkbox('delete[]', $row['pastebin_id'], false); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4">
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
<?= Form::close(); ?>