<h1>Statystyki forum</h1>

<table>
	<caption>Wartości średnie</caption>
	<thead>
		<tr>
			<th>Nazwa</th>
			<th>Wartość</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Średnio postów na dzień</td>
			<td><?= $avgPost; ?></td>
		</tr>
		<tr>
			<td>Średnio wątków na dzień</td>
			<td><?= $avgTopic; ?></td>
		</tr>
		<tr>
			<td>Średnio komentarzy</td>
			<td><?= $avgComment; ?></td>
		</tr>
	</tbody>
</table>

<table>
	<caption>Postów na dzień w podziale na kategorię</caption>
	<thead>
	<tr>
		<th style="width: 20px"></th>
		<th>Kategoria</th>
		<th>Wartość</th>
	</tr>
	</thead>
	<tbody>
		<?php foreach ($avgPerForum as $index => $row) : ?>
		<tr>
			<td><?= $index + 1; ?></td>
			<td><?= $row['page_subject']; ?></td>
			<td><?= $row['value']; ?></td>
		</tr>
			<?php endforeach; ?>
	</tbody>
</table>

<table>
	<caption>Top 10 użytkowników z największa ilością postów</caption>
	<thead>
		<tr>
			<th style="width: 20px"></th>
			<th>Nazwa użytkownika</th>
			<th>Ilość postów</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($topPostUser as $index => $row) : ?>
		<tr>
			<td><?= $index + 1; ?></td>
			<td><?= Html::a(url('adm/User/Submit/' . $row['user_id']), $row['user_name']); ?></td>
			<td><?= $row['user_post']; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<table>
	<caption>Top 10 użytkowników z największą ilością komentarzy</caption>
	<thead>

		<tr>
			<th style="width: 20px"></th>
			<th>Nazwa użytkowników</th>
			<th>Ilośc komentarzy</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($topCommentUser as $index => $row) : ?>
		<tr>
			<td><?= $index + 1; ?></td>
			<td><?= Html::a(url('adm/User/Submit/' . $row['user_id']), $row['user_name']); ?></td>
			<td><?= $row['count']; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<table>
	<caption>Miesiące o największej ilości postów</caption>
	<thead>
		<tr>
			<th style="width: 20px"></th>
			<th>Rok/miesiac</th>
			<th>Ilośc postów</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($topMonths as $index => $row) : ?>
		<tr>
			<td><?= $index + 1; ?></td>
			<td><?= $row['year'] . '/' . $row['month']; ?></td>
			<td><?= $row['count']; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<table>
	<caption>Najbardziej aktywni użytownicy w ciągu ostatnich 30 dni</caption>
	<thead>
	<tr>
		<th style="width: 20px"></th>
		<th>Nazwa użytownika</th>
		<th>Ilośc postów</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($mostActiveUsers as $index => $row) : ?>
	<tr>
		<td><?= $index + 1; ?></td>
		<td><?= $row['user_name']; ?></td>
		<td><?= $row['count']; ?></td>
	</tr>
		<?php endforeach; ?>
	</tbody>
</table>