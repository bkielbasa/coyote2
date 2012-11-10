<?php $selected = 'stat-icon'; include(Config::getBasePath() . 'module/user/template/ucp/_partialUserMenu.php'); ?>

<script type="text/javascript">

	$(document).ready(function()
	{
		$('#box-watch input[name="count[]"]').change(function()
		{
			var totalItems = 0;

			$('#box-watch input[name="count[]"]:checked').each(function()
			{
				totalItems += parseInt($(this).val());
			});

			$('#total-items').text(totalItems);
		});
	});

</script>

<div id="box-watch" class="box">
	<table class="table-visit" style="width: 100%">
		<thead>
		<tr>
			<th>Kategoria forum</th>
			<th>Ilość postów</th>
			<th style="width: 30px">Sumuj</th>
		</tr>
		</thead>
		<tbody>
		<?php if ($stat) : ?>
			<?php foreach ($stat as $row) : ?>
			<tr>
				<td><?= Html::a(url($row['location_text']), $row['page_subject']); ?></td>
				<td><?= $row['COUNT(*)']; ?></td>
				<td><?= Form::checkbox('count[]', $row['COUNT(*)'], true); ?></td>
			</tr>
				<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="3" style="text-align: center;">Nie znaleziono żadnych postów.</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td>Suma:</td>
				<td id="total-items" style="text-align: left; font-weight: bold">
					<?= array_sum($totalItems); ?>
				</td>
				<td></td>
			</tr>
		</tfoot>
	</table>
</div>

<div style="clear: both;"></div>