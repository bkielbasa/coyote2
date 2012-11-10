<script type="text/javascript">
	<!--

	var checked = false;

	$(document).ready(function()
	{
		$('select').change(function()
		{
			if ($(this).val() == 2)
			{
				window.location.href = '<?= url('adm/Infobox/Submit'); ?>';
			}
			else if ($(this).val() == 1)
			{
				if (confirm('Czy chcesz usunąć zaznaczone komunikaty?'))
				{
					$('#infobox').trigger('submit');
				}
			}

			$(this).val(0);
		});

		$('#selectAll').bind('click', function()
		{
			$('input:checkbox').attr('checked', !checked);
			checked = !checked;
		});
	});
	//-->
</script>

<h1>Zarządzanie komunikatami systemowymi</h1>

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>

<?= Form::open('', array('method' => 'post', 'id' => 'infobox')); ?>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Tytuł komunikatu</th>
				<th>Data dodania</th>
				<th>Aktywny</th>
				<th>Data aktywności</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if (!$infobox) : ?>
			<tr>
				<td colspan="6" style="text-align: center">Brak komunikatów.</td>
			</tr>
			<?php else : ?>
			<?php foreach ($infobox as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Infobox/Submit/' . $row['infobox_id']), $row['infobox_id']); ?></td>
				<td><?= Html::a(url('adm/Infobox/Submit/' .$row['infobox_id']), $row['infobox_title']); ?></td>
				<td><?= User::formatDate($row['infobox_time']); ?></td>
				<td><?= $row['infobox_enable'] ? 'Tak' : 'Nie'; ?></td>
				<td><?= User::formatDate($row['infobox_time'] + $row['infobox_lifetime'], User::data('date_format'), false); ?></td>
				<td><?= Form::checkbox('delete[]', $row['infobox_id'], false); ?></td>
			</tr>
			<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5">
					<select>
						<option value="0">akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Dodaj nowy</option>
					</select>

				</td>
				<td class="checkbox"><a id="selectAll" title="Zaznacz wszystko"></a></td>
			</tr>
		</tfoot>
	</table>
<?= Form::close(); ?>

<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>