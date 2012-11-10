<script type="text/javascript">
//<![CDATA[
	function addParam()
	{
		$('<li><?= Form::input('attributes[key][]', ''); ?> = <?= Form::input('attributes[value][]', '', array('size' => 70)); ?></li>').appendTo('#attributes ol');
	}

	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 2)
			{
				window.location.href = '<?= url('adm/Menu/Item?m=' . @$menu_id); ?>';
			}
			else
			{
				if (confirm('Czy chcesz usunąć zaznaczone pozycję?'))
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
//]]>
</script>

<?= Form::open('adm/Menu/Submit/' . @$menu_id, array('method' => 'post')); ?>
	<fieldset>
		<legend>Konfiguracja menu</legend>

		<ol>
			<li>
				<label>Nazwa menu</label> 
				<?= Form::input('name', $input->post('name', @$menu_name)); ?> 
				<ul><?= $filter->formatMessages('name'); ?></ul>
			</li>
			<li>
				<label title="Znacznik HTML odpowiadający za listę. Domyślnie &lt; ul &gt;">Znacznik HTML</label>
				<?= Form::input('tag', $input->post('tag', @$menu_id ? $menu_tag : 'ul'), array('size' => 5)); ?>
				<ul><?= $filter->formatMessages('tag'); ?></ul>
			</li>
			<li>
				<label title="Znak lub znacznik separatora oddzielającego poszczególne pozycje listy. Domyślnie, to pole jest puste">Separator</label>
				<?= Form::input('separator', $input->post('separator', @$menu_separator)); ?> 
				<ul><?= $filter->formatMessages('separator'); ?></ul>
			</li>
			<li>
				<label title="Dodatkowe parametry, takie jak nazwa klasy dla tego menu lub inne atrybuty HTML">Dodatkowe parametry</label>

				<fieldset id="attributes">
					<ol>
						<?php foreach ((array) $attributes as $key => $value) : ?>
						<li>
							<?= Form::input('attributes[key][]', $key); ?> = <?= Form::input('attributes[value][]', $value, array('size' => 70)); ?>
						</li>
						<?php endforeach; ?>
					</ol>
				</fieldset>
			</li>
			<li>
				<label>&nbsp;</label> <?= Form::button('', 'Dodaj nowy parametr', array('style' => 'font-size: 0.8em;', 'onclick' => 'addParam()')); ?>
			</li>
			<li>
				<label title="Uprawnienie jakie musi posiadać użytkownik, aby zobaczyć to menu">Uprawnienie</label>
				<?= Form::select('auth', Form::option($auth, $input->post('auth', @$menu_auth))); ?>
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
			</li>
		</ol>
	</fieldset>	
</form>

<?php if (@$menu_id) : ?>

<?php

	function img_sort($id, $order, $max)
	{
		if ($order == 1 && $order == $max)
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

<?= Form::open('adm/Menu/Items/' . $menu_id, array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Lista pozycji w menu</caption>
		<thead>
			<tr>
				<th>ID</th>
				<th>Nazwa</th>
				<th>Aktywne</th>
				<th>Ścieżka</th>
				<th>Kolejność</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php if ($items) : ?>
		<?php load_helper('array'); ?>

		<?php foreach ($items as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Menu/Item/' . $row['item_id'] . '?m=' . $menu_id), $row['item_id']); ?></td>
				<td><?= Html::a(url('adm/Menu/Item/' . $row['item_id'] . '?m=' . $menu_id), str_repeat('&nbsp;', $row['item_depth'] * 2) . Text::limit($row['item_name'], 65)); ?></td>
				<td><?= element(array('Nie', 'Tak'), $row['item_enable']); ?></td>
				<td><?= $row['item_path']; ?></td>
				<td><?= img_sort($row['item_id'], $row['item_order'], sizeof($items)); ?></td>
				<td class="checkbox"><?= $row['isRemoveable'] ? Form::checkbox('delete[]', $row['item_id'], false) : ''; ?></td>
			</tr>
		<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td colspan="6" style="text-align: center;">Brak pozycji w tym menu</td>
			</tr>
		<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
					<select  onchange="selectAction(this)">
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Dodaj nową pozycję</option>
					</select>
				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystkie"></a>
				</td>
			</tr>
		</tfoot>
	</table>
</form>

<?php endif; ?>