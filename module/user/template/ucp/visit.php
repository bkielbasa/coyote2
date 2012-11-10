<?php $selected = 'visit-icon'; include('_partialUserMenu.php'); ?>

<div id="box-user" class="box">
	<table class="table-visit">
		<thead>
			<tr>
				<th style="width: 110px">Data logowania</th>
				<th style="width: 100px">Długość wizyty</th>
				<th>IP</th>
				<th>Strona</th>
			</tr>
		</thead>
		<tbody>
			<?php if ($visit) : ?>
			<?php foreach ($visit as $row) : ?>
			<tr>
				<td><?= User::date($row['log_start']); ?></td>
				<td><?= sprintf('%02d:%02d', floor(($row['log_stop'] - $row['log_start']) / 60) / 60, round(($row['log_stop'] - $row['log_start']) / 60) % 60); ?> h.</td>
				<td><?= $row['log_ip']; ?></td>
				<td><?= Html::a($row['log_url'], $row['log_page']); ?></td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="4" style="text-align: center;">Brak odnotowanych wizyt.</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<div style="clear: both;"></div>