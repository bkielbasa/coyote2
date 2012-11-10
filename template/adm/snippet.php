<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 2)
			{
				window.location.href = '<?= url('adm/Snippet/Submit'); ?>';
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
<h1>Skawki kodu</h1>

<?php if ($session->message) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<p>Jest to zaawansowany element systemu umożliwiający dynamiczne generowanie treści w dokumentach. 
Skrawki kodu mogą być fizycznie istniejącą klasą lub kodem PHP przechowywanym w bazie danych.</p>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Lista skrawków</caption>

		<thead>
			<tr>
				<th>ID</th>
				<th>Nazwa</th>
				<th>Opis</th>
				<th>Data utworzenia</th>
				<th>Autor</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($snippet) : ?>
			<?php foreach ($snippet as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Snippet/Submit/' . $row['snippet_id']), $row['snippet_id']); ?></td>
				<td><?= Html::a(url('adm/Snippet/Submit/' . $row['snippet_id']), $row['snippet_name']); ?></td>
				<td><?= $row['snippet_text']; ?></td>
				<td><?= User::formatDate($row['snippet_time']); ?></td>
				<td><?= Html::a(url('adm/User/Submit/' . $row['snippet_user']), $row['user_name']); ?></td>
				<td class="checkbox"><?= Form::checkbox('delete[]', $row['snippet_id'], false); ?></td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="6" style="text-align: center;">Brak zarejestrowanych skrawków kodu.</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
					<select  onchange="selectAction(this)">
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Dodaj nowy skrawek kodu</option>
					</select>		
				</td>

				<td class="checkbox">
					<a class="checkbox"></a>
				</td>
			</tr>
		</tfoot>
	</table>
<?= Form::close(); ?>