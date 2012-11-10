<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 3)
			{
				window.location.href = '<?= url('adm/Block/Submit'); ?>';
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

<h1>Bloki</h1>

<?php

	function img_sort($id, $order, $max)
	{
		if ($order == 1 && $max == 1)
		{
			echo Html::img(Media::img('adm/down_disabled.gif'), array('style' => 'margin: 0 4px 0 4px')); 
			echo Html::img(Media::img('adm/up_disabled.gif'), array('style' => 'margin: 0 4px 0 4px')); 
		}
		else if ($order == 1) 
		{
			echo Html::a('?id=' . $id . '&mode=down', Html::img(Media::img('adm/down.gif'), array('style' => 'margin: 0 4px 0 4px'))); 
			echo Html::img(Media::img('adm/up_disabled.gif'), array('style' => 'margin: 0 4px 0 4px')); 
		}		
		else if ($order == $max)
		{
			echo Html::img(Media::img('adm/down_disabled.gif'), array('style' => 'margin: 0 4px 0 4px')); 
			echo Html::a('?id=' . $id . '&mode=up', Html::img(Media::img('adm/up.gif'), array('style' => 'margin: 0 4px 0 4px'))); 
		}
		else
		{
			echo Html::a('?id=' . $id . '&mode=down', Html::img(Media::img('adm/down.gif'), array('style' => 'margin: 0 4px 0 4px'))); 
			echo Html::a('?id=' . $id . '&mode=up', Html::img(Media::img('adm/up.gif'), array('style' => 'margin: 0 4px 0 4px')));
		}
	}
?>


<p>Bloki mogą być grupowane w regionach lub wyświetlane pojedynczo w widokach. Mogą również wywoływać <?= Html::a(url('adm/Trigger'), 'triggery'); ?> lub zawierać w sobie <?= Html::a(url('adm/Plugin'), 'wtyczki'); ?></p>
<?= Form::open('', array('method' => 'post', 'name' => 'form', 'onsubmit' => 'return confirm(\'Zmiany mogą mieć wpływ na działanie systemu. Kontynuować?\');')); ?>
	<table>
		<caption>Lista bloków</caption>
		<thead>
			<tr>
				<th>ID</th>
				<th>Nazwa bloku</th>
				<th>Wtyczka</th>
				<th>Region</th>
				<th>Kolejność wyświetlania</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($block) : ?>
			<?php foreach ($block as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Block/Submit/' . $row['block_id']), $row['block_id']); ?></td>
				<td><?= Html::a(url('adm/Block/Submit/' . $row['block_id']), $row['block_name']); ?></td>
				<td><?= $row['block_plugin'] ? $row['plugin_text'] : ''; ?></td>
				<td><?= Form::select('block[' . $row['block_id'] . ']', Form::option($regions, $row['block_region'])); ?></td>
				<td><?= img_sort($row['block_id'], $row['block_order'], $regionBlock[$row['block_region']]); ?></td>
				<td class="checkbox"><?= Form::checkbox('delete[]', $row['block_id'], false); ?></td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="5" style="text-align: center;">Brak bloków</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<td colspan="5">
				<select  onchange="selectAction(this)">
					<option value="0">akcja...</option>
					<option value="1">Usuń zaznaczone</option>
					<option value="2">Zapisz zmiany</option>
					<option value="3">Dodaj nowy blok</option>
				</select>			
			</td>
			<td class="checkbox">
				<a id="selectAll" title="Zaznacz wszystkie"></a>				
			</td>
		</tfoot>
	</table>

</form>

