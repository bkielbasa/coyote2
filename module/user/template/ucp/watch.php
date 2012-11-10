<script type="text/javascript">
<!--

	function selectItem(itemId)
	{
		if (itemId.value > 0)
		{
			$.windowConfirm(
			{
				windowTitle: 'Usuwanie wiadomości',
				windowMessage: 'Czy chcesz usunąć zaznaczone wiadomości?',
				onYesClick: function()
				{
					$('#watch').submit();
				}
			});
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

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<?php $selected = 'watch-icon'; include('_partialUserMenu.php'); ?>

<div id="box-watch" class="box">

	<?php if ($pagination->getTotalPages() > 1) : ?>
	<p>Strona <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></p>
	<?php endif; ?>

	<?php if ($watch) :?>
	<?= Form::open('', array('method' => 'post', 'id' => 'watch')); ?>
		<table class="table-visit">
			<thead>
				<tr>
					<th></th>
					<th>Tytuł strony</th>
					<th>Data obserwacji</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($watch as $row) : ?>
				<tr>
					<td>
						<?= Form::checkbox('delete[]', $row['page_id'], false); ?>
					</td>
					<td>
						<?= Html::a(url($row['location_text']), $row['page_subject']); ?>
					</td>
					<td>
						<?php if ($row['watch_time']) : ?>
						<?= User::date($row['watch_time']); ?>
						<?php else : ?>
						--
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<td class="checkbox">
						<a id="selectAll" href="#"></a>
					</td>
					<td colspan="4">

						<select onchange="selectItem(this);">
							<option>wybierz...</option>
							<option value="1">usuń zaznaczone</option>
						</select>

					</td>
				</tr>
			</tfoot>
		</table>
	<?= Form::close(); ?>
	<?php else : ?>

	<p style="text-align: center;">Brak obserwowanych stron.</p>
	<?php endif; ?>

	<?php if ($pagination->getTotalPages() > 1) : ?>
	<p>Strona <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></p>
	<?php endif; ?>
</div>

<div style="clear: both;"></div>