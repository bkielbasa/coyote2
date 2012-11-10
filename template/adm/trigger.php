<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 2)
			{
				window.location.href = '<?= url('adm/Trigger/Submit'); ?>';
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

<h1>Triggery</h1>

<p>Triggery pozwalają na wykonanie określonych czynności (zdarzeń) w skutek zajścia pewnych czynników. Więcej informacji 
na temat działania triggerów, możesz znaleźć na stronie <?= Html::a('http://boduch.net/coyote/docs', 'projektu Coyote'); ?></p>

<?php if (!is_writeable('config/trigger.xml')) : ?>
<p class="error">Nie można zapisać konfiguracji do pliku <i>/config/trigger.xml></i>! Zmień prawa dostępu do tego pliku na <b>0666</b>!</p>
<?php endif; ?>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Lista triggerów</caption>
		<thead>
			<tr>
				<th>Nazwa triggera</th>
				<th>Opis</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($trigger as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Trigger/Submit/' . $row['trigger_id']), $row['trigger_name']); ?></td>
				<td><?= Text::limit($row['trigger_description'], 60); ?></td>
				<td class="checkbox">
					<?php if ($row['trigger_type'] != Trigger_Model::SYSTEM) : ?>
					<?= Form::checkbox('delete[]', $row['trigger_id'], false); ?>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2">
					<select  onchange="selectAction(this)">
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Dodaj nowy blok</option>
					</select>
				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystkie"></a>
				</td>
			</tr>
		</tfoot>
	</table>
</form>

