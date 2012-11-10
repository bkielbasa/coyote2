<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 2)
			{
				window.location.href = '<?= url('adm/Ban/Submit'); ?>';
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

<h1>Lista blokad</h1>

<p>Na tej stronie możesz zablokować dostęp do witryny dla adresu IP lub danego użytkownika. Kliknij w ID rekordu, aby edytować.</p>

<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Obecne blokady</caption>
		<thead>
			<tr>
				<th><?= Sort::display('ban_id', 'ID'); ?></th>
				<th><?= Sort::display('u2_name', 'Nazwa użytkownika'); ?></th>
				<th><?= Sort::display('ban_ip', 'IP'); ?></th>
				<th><?= Sort::display('ban_expire', 'Przedawnienie'); ?></th>
				<th><?= Sort::display('ban_reason', 'Powód'); ?></th>
				<th><?= Sort::display('ban_creator', 'Założony przez'); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php if ($ban) : ?>
		<?php foreach ($ban as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Ban/Submit/' . $row['ban_id']), $row['ban_id']); ?></td>
				<td><?= Html::a(url('adm/User/Submit/' .$row['u1_id']), $row['u1_name']); ?></td>
				<td><abbr class="whois-ip"><?= $row['ban_ip']; ?></abbr></td>
				<td><?= $row['ban_expire'] ? User::formatDate($row['ban_expire']) : 'Nigdy'; ?></td>
				<td><?= $row['ban_reason']; ?></td>
				<td><?= $row['u2_id'] ? Html::a(url('adm/User/Submit/' . $row['u2_id']), $row['u2_name']) : ''; ?></td>
				<td class="checkbox"><?= Form::checkbox('delete[]', $row['ban_id'], false); ?></td>
			</tr>
		<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td colspan="7" style="text-align: center;">Brak blokad w bazie danych.</td>
			</tr>
		<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="6">
					<select  onchange="selectAction(this)">
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Dodaj blokadę</option>
					</select>
				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystkie"></a>
				</td>
			</tr>
		</tfoot>
	</table>
</form>

Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?>