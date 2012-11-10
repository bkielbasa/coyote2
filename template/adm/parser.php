<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 2)
			{
				window.location.href = '<?= url('adm/Parser/Submit'); ?>';
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

<h1>Parsery</h1>

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

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


<p>Parsery służą do konwersji tekstu. Podczas dodawania dokumentów możesz określić które parsery zostaną użyte w procesie wyświetlania danego dokumentu. Na tej 
stronie możesz zmienić kolejność wykonywania parserów.</p>

<?= Form::open('', array('method' => 'post', 'name' => 'form', 'onsubmit' => 'return confirm(\'Zmiany mogą mieć wpływ na działanie systemu. Kontynuować?\');')); ?>
	<table>
		<caption>Lista parserów</caption>
		<thead>
			<tr>
				<th>ID</th>
				<th>Parser</th>
				<th>Opis</th>
				<th>Kolejność wyświetlania</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($parser) : ?>
			<?php foreach ($parser as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Parser/Submit/' . $row['parser_id']), $row['parser_id']); ?></td>
				<td><?= Html::a(url('adm/Parser/Submit/' . $row['parser_id']), $row['parser_text']); ?></td>
				<td><?= $row['parser_description']; ?></td>
				<td><?= img_sort($row['parser_id'], $row['parser_order'], sizeof($parser)); ?></td>
				<td class="checkbox"><?= Form::checkbox('delete[]', $row['parser_id'], false); ?></td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="5" style="text-align: center;">Brak parserów</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<td colspan="4">
				<select  onchange="selectAction(this)">
					<option value="0">akcja...</option>
					<option value="1">Usuń zaznaczone</option>
					<option value="2">Dodaj nowy parser</option>
				</select>			
			</td>
			<td class="checkbox">
				<a id="selectAll" title="Zaznacz wszystkie"></a>				
			</td>
		</tfoot>
	</table>

</form>

