<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 3)
			{
				$('tbody').append('<tr><td><input type="text" name="name[]" value="" /></td><td><input type="text" name="text[]" value="" /></td><td><?= Form::select('cache[]', Form::option($cacheConfig, 0)); ?></td><td></td></tr>');
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

<h1>Regiony</h1>

<p>Regiony są miejscem w widoku, umożliwiającym wstawianie elementów takich jak bloki. Tutaj możesz decydować o cachowaniu 
całych reionów w celu przyspieszenia działania witryny.</p>

<?php if (!is_writeable('config/region.xml')) : ?>
<p class="error">Nie można zapisać konfiguracji regionów! Zmień prawa zapisu do pliku <i>/config/region.xml</i> na <b>0666</b>!</p>
<?php endif; ?>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Dostepne regiony</caption>
		<thead>
			<tr>
				<th>Nazwa regionu</th>
				<th>Opis regionu</th>
				<th>Cachowanie</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php $regions = (array)Config::getItem('region'); ?>
			<?php foreach ($regions as $row) : ?>
			<tr>
				<td><?= $row['name']; ?></td>
				<td><?= $row['text']; ?></td>
				<td><?= Form::select('cache[' . $row['name'] . ']', Form::option($cacheConfig, $row['cache'])); ?></td>
				<td class="checkbox"><?= Form::checkbox('delete[]', $row['name'], false); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<td colspan="3">
				<select  onchange="selectAction(this)">
					<option value="0">akcja...</option>
					<option value="1">Usuń zaznaczone</option>
					<option value="2">Zapisz zmiany</option>
					<option value="3">Dodaj nowy region</option>
				</select>			
			</td>
			<td class="checkbox">
				<a id="selectAll" title="Zaznacz wszystkie"></a>				
			</td>
		</tfoot>
	</table>
</form>