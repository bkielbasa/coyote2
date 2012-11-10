<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 2)
			{
				window.location.href = '<?= url('adm/Notify/Submit'); ?>';
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

<h1>Konfiguracja powiadomień</h1>

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Trigger</th>
				<th>Klasa obsługi powiadomienia</th>
				<th>Nazwa</th>
				<th>Domyślnie</th>
				<th></th>
			</tr>
		</thead>
	
		<tbody>
			<?php foreach ($notify as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Notify/Submit/' . $row['notify_id']), $row['notify_id']); ?></td>
				<td><?= Html::a(url('adm/Notify/Submit/' . $row['notify_id']), $row['notify_trigger']); ?></td>
				<td><?= $row['notify_class']; ?></td>
				<td><?= $row['notify_name']; ?></td>
				<td><?= element(array('Nie', 'Tak'), $row['notify_default']); ?></td>
				<td class="checkbox"><?= Form::checkbox('delete[]', $row['notify_id'], false); ?></td>
			</tr>
			<?php endforeach;; ?>
		</tbody>
		<tfoot>
			<td colspan="5">
				<select  onchange="selectAction(this)">
					<option value="0">akcja...</option>
					<option value="1">Usuń zaznaczone</option>
					<option value="2">Dodaj nowe powiadomienie</option>
				</select>	
			</td>
			<td class="checkbox">
				<a id="selectAll" title="Zaznacz wszystkie"></a>
			</td>
		</tfoot>
	</table>

<?= Form::close(); ?>