<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 2)
			{
				window.location.href = '<?= url('adm/Bookmark/Submit'); ?>';
			}
			else
			{
				if (confirm('Czy chcesz usunąć zakładkę?'))
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

<h1>Lista zakładek</h1>

<?php if ($pagination->getTotalPages() > 1) : ?>
Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?>
<?php endif; ?>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Lista zakładek</caption>

		<thead>
			<tr>
				<th>ID</th>
				<th>URL</th>
				<th>Host</th>
				<th>Ilość użytkowników</th>
				<th>Polecane</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($bookmark) : ?>
			<?php foreach ($bookmark as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Bookmark/Submit/' . $row['bookmark_id']), $row['bookmark_id']); ?></td>
				<td><?= Html::a($row['bookmark_url']); ?></td>
				<td><?= $row['bookmark_host']; ?></td>
				<td><?= $row['bookmark_count']; ?></td>
				<td><?= (int)$row['bookmark_digg']; ?></td>
				<td class="checkbox"><?= Form::checkbox('delete[]', $row['bookmark_id'], false); ?></td>
			</tr>	
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="6" style="text-align: center;">Brak zakładek w bazie danych.</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
					<select  onchange="selectAction(this)">
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Dodaj nową zakładkę</option>
					</select>
				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystkie"></a>				
				</td>
			</tr>
		</tfoot>
	</table>
<?= Form::close(); ?>

<?php if ($pagination->getTotalPages() > 1) : ?>
Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?>
<?php endif; ?>