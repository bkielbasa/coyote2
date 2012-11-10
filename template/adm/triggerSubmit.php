<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 2)
			{
				window.location.href = '<?= url('adm/Trigger/Event/' . @$trigger_id); ?>';
			}
			else
			{
				if (confirm('Czy chcesz usunąć zaznaczone zdarzenia?'))
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

<?= Form::open('adm/Trigger/Submit/' . @$trigger_id, array('method' => 'post')); ?>
	<fieldset>
		<legend>Konfiguracja triggera</legend>

		<ol>
			<?php if (@$trigger_id && @$trigger_mode == Trigger_Model::SYSTEM) : ?>
			<?= Form::hidden('name', $trigger_name); ?>
			<?php endif; ?>

			<li>
				<label>Nazwa</label>
				<?= Form::input('name', $input->post('name', @$trigger_name), @$trigger_id && @$trigger_type == Trigger_Model::SYSTEM ? array('disabled' => 'disabled', 'size' => 50) : array('size' => 50)); ?> 
				<ul><?= $filter->formatMessages('name'); ?></ul>
			</li>
			<li>
				<label title="Nagłówek zdarzenia, lista parametrów">Nagłówek</label>
				<?= Form::input('header', $input->post('header', @$trigger_header), array('size' => 70)); ?> 
				<ul><?= $filter->formatMessages('header'); ?></ul>
			</li>
			<li>
				<label title="Krótki opis działania triggera (okoliczności wywołania itp.)">Opis</label>
				<?= Form::textarea('description', $input->post('description', @$trigger_description), array('cols' => 70, 'rows' => 10)); ?> 
				<ul><?= $filter->formatMessages('description'); ?></ul>
			</li>

			<li><label>&nbsp;</label> <?= Form::submit('', 'Zapisz zmiany'); ?></li>
		</ol>
	</fieldset>
</form>

<?php if (@$trigger_id) : ?>
<?= Form::open('adm/Trigger/Events/' . $trigger_id, array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Zdarzenia dla triggera</caption>
		<thead>
			<tr>
				<th>ID</th>
				<th>Nazwa zdarzenia</th>
				<th></th>
			</tr>
		<tbody>
			<?php if (@$event) : ?>
			<?php foreach ($event as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Trigger/Event/' . $trigger_id . '/' . $row['event_id']), $row['event_id']); ?></td>
				<td><?= Html::a(url('adm/Trigger/Event/' . $trigger_id . '/' . $row['event_id']), $row['event_name']); ?></td>
				<td class="checkbox"><?= Form::checkbox('delete[]', $row['event_id'], false); ?></td>
			</tr>		
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="3" style="text-align: center">Brak zdarzeń przypisanych do tego triggera</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2">
					<select  onchange="selectAction(this)">
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Przypisz nowe zdarzenie</option>
					</select>
				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystko"></a>
				</td>
			</tr>
		</tfoot>
	</table>
</form>
<?php endif; ?>