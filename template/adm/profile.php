<script type="text/javascript">
<!--

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

<h1>Pola profilu użytkownika</h1>

<p>Tutaj możesz utworzyć dodatkowe, niestandardowe pola w profilu użytkownika.</p>

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


<?= Form::open(url('adm/Profile/Submit')); ?>
	<fieldset>
		<legend>Utwórz nowe pola profilu</legend>

		<ol>
			<li>
				<label>Komponent</label>
				<?= Form::select('componentId', Form::option($component, 0)); ?>
			</li>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('', 'Utwórz nowe pole', array('class' => 'application-add-button')); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>

<h2>Istniejące pola</h2>

<?= Form::open('', array('method' => 'post', 'onsubmit' => 'return confirm(\'Czy na pewno chcesz usunąć zaznaczone pola?\');')); ?>
	<table>
		<caption>Pola panelu użytkownika</caption>

		<thead>
			<tr>
				<th>ID pola</th>
				<th>Etykieta pola</th>
				<th>Komponent</th>
				<th>Kolejność wyświetlania</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($field) : ?>
			<?php foreach ($field as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Profile/Submit/' . $row['field_id'] . '?componentId=' . $row['field_component']), $row['field_id']); ?></td>
				<td><?= Html::a(url('adm/Profile/Submit/' . $row['field_id'] . '?componentId=' . $row['field_component']), $row['field_text']); ?></td>
				<td><?= $row['component_text']; ?></td>		
				<td><?= img_sort($row['field_id'], $row['field_order'], count($field)); ?></td>
				<td class="checkbox"><?= Form::checkbox('delete[]', $row['field_id'], false); ?></td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="5" style="text-align: center;">Brak dodatkowych pól w bazie danych.</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4">
					<select onchange="if (confirm('Czy chcesz usunąć zaznaczone pola?')) this.form.submit();">
						<option>zaznaczone...</option>
						<option>Usuń</option>
					</select>
				</td>
				<td class="checkbox"><a id="selectAll" title="Zaznacz wszystkie"></a></td>
			</tr>
		</tfoot>
	</table>


<?= Form::close(); ?>