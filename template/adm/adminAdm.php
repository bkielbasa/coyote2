<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (confirm('Czy na pewno chcesz usunąć zaznaczone menu?'))
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

<h1>Kolejność wyświetlania zakładek</h1>

<p>Na tej stronie możesz ustalić kolejność wyświetlania zakładek w panelu administracyjnym, a także dodać/usunąć nową zakładkę. Prawa dostępu do poszczególnych elementów możesz
ustalić w zakładce <i>Uprawnienia</i>.</p>

<?php

	function img_sort($id, $order, $max)
	{
		if ($order == 1) 
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

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Kolejność wyświetlania zakładek</caption>
		<thead>
			<tr>
				<th>Zakładka</th>
				<th></th>
				<th></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($menu as $row) : ?>
			<tr>
				<td><b><?= Html::a(url('adm/Admin/Adm/' . $row['menu_id']), $row['menu_text']); ?></b></td>
				<td><?= img_sort($row['menu_id'], $row['menu_order'], count($menu)); ?></td>
				<td class="checkbox"><?= !isset($row['subcat']) ? Form::checkbox('delete[]', $row['menu_id'], false) : ''; ?></td>
			</tr>
				<?php foreach ((array)@$row['subcat'] as $row2) : ?>
				<tr>
					<td style="padding-left: 30px;"><?= Html::a(url('adm/Admin/Adm/' . $row2['menu_id']), $row2['menu_text']); ?></td>
					<td><div style="margin-left: -60px;"><?= img_sort($row2['menu_id'], $row2['menu_order'], count($row['subcat'])); ?></div></td>
					<td class="checkbox"><?= Form::checkbox('delete[]', $row2['menu_id'], false); ?></td>
				</tr>
				<?php endforeach ; ?>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<td colspan="2">
				<select onchange="selectAction(this)">
					<option value="0">akcja...</option>
					<option value="1">Usuń zaznaczone</option>
				</select>
			</td>
			<td class="checkbox">
				<a id="selectAll" title="Zaznacz wszystkie"></a>
			</td>
		</tfoot>
	</table>
<?= Form::close(); ?>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Dodaj nową zakładkę</legend>

		<ol>
			<li>
				<label>Nazwa zakładki <em>*</em></label>
				<?= Form::input('text', $input->post('text', @$menu_text)); ?>
				<ul><?= $filter->formatMessages('text'); ?></ul>
			</li>
			<li>
				<label>Zakładka macierzysta</label>
				<?= Form::select('parent', Form::option($parent, $input->post('parent', @$menu_parent))); ?>
			</li>
			<li>
				<label>Kontroler <em>*</em></label>
				<?= Form::input('controller', $input->post('controller', @$menu_controller)); ?>
				<ul><?= $filter->formatMessages('controller'); ?></ul>
			</li>
			<li>
				<label>Akcja <em>*</em></label>
				<?= Form::input('action', $input->post('action', @$menu_action)); ?>
				<ul><?= $filter->formatMessages('action'); ?></ul>
			</li>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('', 'Dodaj zakładkę'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>