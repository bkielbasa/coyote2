<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 2)
			{
				$('#submit-form').show();
				$('#submit-form :input:not([type=submit])').val('');
			}
			else
			{
				if (confirm('Czy na pewno chcesz usunąć zaznaczone rekordy?'))
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
		});
	});
//-->
</script>

<h1>Powody interwencji moderatorów</h1>

<p>Podczas usuwania, przenoszenia tematów możesz określić powód Twojej interwencji. Wówczas taka informacja
jest zapisywana w dzienniku zdarzeń, a sam użytkownik - dostanie informacje o powodzenia usunięcia/przeniesienia
tematu/postu.</p>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Powód usunięcia</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($reason) : ?>
			<?php foreach ($reason as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Forum/Reason/' . $row['reason_id']), $row['reason_id']); ?></td>
				<td><?= Html::a(url('adm/Forum/Reason/' . $row['reason_id']), $row['reason_name']); ?></td>
				<td class="checkbox">
					<?= Form::checkbox('delete[]', $row['reason_id'], false); ?>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="3" style="text-align: center;">Brak wpisów w bazie danych.</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2">
					<select  onchange="selectAction(this)">
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Dodaj nowy</option>
					</select>
				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystkie"></a>
				</td>
			</tr>
		</tfoot>
	</table>
<?= Form::close(); ?>

<?= Form::open('', array('method' => 'post', 'id' => 'submit-form', 'style' => (@$reason_id ? '' : 'display: none;'))); ?>
	<fieldset>
		<?= Form::hidden('id', @$reason_id); ?>
		<ol>
			<li>
				<label>Nazwa <em>*</em></label>
				<?= Form::text('name', $input->post->name(@$reason_name)); ?>
				<ol><?= $filter->formatMessages('name'); ?></ol>
			</li>
			<li>
				<label>Treść (opis)</label>
				<?= Form::textarea('content', $input->post->content(@$reason_content), array('cols' => 60, 'rows' => 10)); ?>
			</li>
			<li>
				<?= Form::submit('', 'Zapisz'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>