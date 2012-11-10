<h1>Top10 wyszukiwanych fraz</h1>

<p>Tutaj znajdziesz 10 najczęstszych szukanych fraz w serwisie.</td>

<table>
	<thead>
		<tr>
			<th>Szukana fraza</th>
			<th>Ilość</th>
		</tr>
	</thead>
	<tbody>
		<?php if ($top10) : ?>
		<?php foreach ($top10 as $row) : ?>
		<tr>
			<td><?= $row['top10_query']; ?></td>
			<td><?= $row['top10_weight']; ?></td>
		</tr>
		<?php endforeach; ?>
		<?php else : ?>
		<tr>
			<td colspan="2" style="text-align: center;">Brak fraz w bazie danych.</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>