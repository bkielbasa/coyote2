<h1>Czasy generowania stron</h1>

<p>Znajdziesz tutaj średnie czasy generowania poszczególnych stron systemu. <i>Grupa reprezentacyjna</i> to informacja 
o ilości wyświetleń strony, które brały udział w obliczaniu wyniku.</p>

<p>Logowanie czasów generowania stron spowolnia działanie systemu. Ten moduł powinien być używany
jedynie w krótkich odstępach czasu</p>

<?php if (intval($module->profiler('enable') == 0)) : ?>
<p class="error">Profiler jest wyłączony. Możesz go właczyć zmieniając opcję modułu na zakładce <i>Moduły</i>.</p>
<?php endif; ?>

<table>
	<caption>Czas generowania stron</caption>
	<thead>
		<tr>
			<?= Sort::displayTh('profiler_page', 'Strona'); ?>
			<?= Sort::displayTh('profiler_count', 'Grupa reprezentacyjna'); ?>
			<?= Sort::displayTh('profiler_sql', 'Zapytania SQL'); ?>
			<?= Sort::displayTh('profiler_php', 'Kod PHP'); ?>
			<?= Sort::displayTh('profiler_time', 'Czas generowania'); ?>
		</tr>
	</thead>
	<tbody>
		<?php if ($profiler) : ?>
		<?php load_helper('text'); ?>
		<?php foreach ($profiler as $row) : ?>
		<tr>
			<td><?= Html::a(url(ltrim($row['profiler_page'], '/')), Text::limit($row['profiler_page'], 40)); ?></td>
			<td><?= $row['profiler_count']; ?></td>
			<td><?= sprintf("%.7f", $row['profiler_sql']); ?> sek.</td>
			<td><?= sprintf("%.7f", $row['profiler_php']); ?> sek.</td>
			<td><?= sprintf("%.7f", $row['profiler_time']); ?> sek.</td>
		</tr>
		<?php endforeach; ?>
		<?php else : ?>
		<tr>
			<td colspan="5" style="font-style: italic;">Brak wpisów w bazie danych.</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>

<p>Strony [<?= $pagination; ?>] z <?= $total_page; ?></p>

<?php if ($sql) : ?>
<table>
	<caption>Najwojniejsze zapytania SQL</caption>
	
	<thead>
		<tr>
			<th>Zapytanie SQL</th>
			<th>Średni czas wykonywania</th>
		</tr>
	</thead>
	<tbody>	
		<?php foreach ($sql as $row) : ?>
		<tr>
			<td><?= $row['sql_query']; ?></td>
			<td><?= sprintf("%.7f", $row['sql_time']); ?> sek.</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Usuń dane</legend>

		<div><b>&nbsp;</b> <?= Form::submit('', 'Usuń dane z bazy danych', array('class' => 'button')); ?></div>
	</fieldset>
</form>