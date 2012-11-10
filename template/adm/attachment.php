<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (confirm('Czy chcesz usunąć zaznaczone załączniki? Mogą one być zawarte w niektórych artykułach!'))
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

<h1>Załączniki</h1>

<p>Ta strona przedstawia listę załączników, które znajdują się w systemie. Jeżeli do tej pory załącznik nie został przypisany do żadnego dokumentu, możesz go usunąć.</p>

<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>

<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
	<table>
		<caption>Załączniki</caption>
		<thead>
			<tr>
				<?= Sort::displayTh('attachment_id', 'ID'); ?>
				<?= Sort::displayTh('attachment_name', 'Nazwa załącznika'); ?>
				<?= Sort::displayTh('attachment_size', 'Rozmiar pliku'); ?>
				<th>Ilość rewizji</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php if ($attachment) : ?>
		<?php foreach ($attachment as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Attachment/Submit/' . $row['attachment_id']), $row['attachment_id']); ?></td>
				<td><?= Html::a(url('adm/Attachment/Submit/' . $row['attachment_id']), $row['attachment_name']); ?></td>
				<td><?= Text::fileSize($row['attachment_size']); ?></td>	
				<td><?= $row['text_id']; ?></td>
				<td class="checkbox">
					<?php if (!$row['text_id']) : ?>
					<?= Form::checkbox('delete[]', $row['attachment_id'], false); ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td colspan="5" style="text-align: center;">Brak załączników.</td>
			</tr>
		<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4">
					<select onchange="selectAction(this)">
						<option>akcja...</option>
						<option value="1">usuń zaznaczone</option>
					</select>

				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystko"></a>
				</td>
			</tr>
		</tfoot>
	</table>
</form>


Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?>
