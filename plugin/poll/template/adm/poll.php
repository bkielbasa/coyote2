<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 2)
			{
				window.location.href = '<?= url('adm/Poll/Submit'); ?>';
			}
			else
			{
				if (confirm('Czy na pewno chcesz usunąć zaznaczone ankiety?'))
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

<h1>Ankiety</h1>

<p>Wtyczka umożliwia zarządzanie ankietami.</p>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Lista ankiet</caption>

		<thead>
			<tr>
				<th>ID</th>
				<th>Temat ankiety</th>
				<th>Data rozpoczęcia</th>
				<th>Data zakończenia</th>
				<th>Ankieta aktywna</th>
				<th>Ilość głosów</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($poll) : ?>
			<?php load_helper('array'); ?>
			<?php foreach ($poll as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Poll/Submit/' . $row['poll_id']), $row['poll_id']); ?></td>
				<td><?= Html::a(url('adm/Poll/Submit/' . $row['poll_id']), $row['poll_title']); ?></td>
				<td><?= date('d-m-Y H:i', $row['poll_start']); ?></td>
				<td><?= date('d-m-Y H:i', $row['poll_start'] + $row['poll_length'] * Time::DAY); ?></td>
				<td><?= element(array('Nie', 'Tak'), $row['poll_enable']); ?></td>
				<td><?= $row['poll_votes']; ?></td>
				<td class="checkbox"><?= Form::checkbox('delete[]', $row['poll_id'], false); ?></td>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="7" style="text-align: center;">Brak ankiet w bazie danych</td>
			</tr>
			<?php endif; ?>
		</tbody>

		<tfoot>
			<tr>
				<td colspan="6">
					<select  onchange="selectAction(this)">
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Dodaj nową ankiete</option>
					</select>	
				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystkie"></a>
				</td>
			</tr>
		</tfoot>
	</table>
</form>