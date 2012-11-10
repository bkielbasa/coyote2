<h1>Indeksacja stron</h1>

<script type="text/javascript">
<!--

	$(document).ready(function()
	{
		$('select:eq(0)').change(function()
		{
			$('form:eq(0)').submit();
		});

		$('select:eq(1)').change(function()
		{
			if (confirm('Czy na pewno chcesz dokonać tej operacji?'))
			{
				$('form:eq(2)').submit();
			}
		});

	});
//-->
</script>

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<p>Indeksacja stron pozwala utrzymać jak najnowszy indeks wyszukiwarki, tak, aby rezultaty
poszukiwań były najbardziej odpowiadające aktualnym zawartościom stron. Każda aktualizacja lub
dodanie strony, a nawet jej usunięcie, powoduje dodanie strony do kolejki indeksacji.</p>

<p>Odpowiednie ustawienie konfiguracji <?= Html::a(url('adm/Scheduler'), 'zadań'); ?> pozwoli
na automatyczne uaktualnianie indeksu. Na tej stronie możesz jednocześnie uaktualnić
indeks w sposób ręczny.</p>

<?= Form::open('', array('method' => 'post')); ?>
	<table>
		<caption>Kolejka indeksacji</caption>

		<thead>
			<tr>
				<th>ID</th>
				<th>Tytuł strony</th>
				<th>Data dodania do kolejki</th>
			</tr>
		</thead>
		<tbody>
			<?php if ($queue) : ?>
			<?php foreach ($queue as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Page/Submit/' . $row['page_id']), $row['page_id']); ?></td>
				<td><?= $row['page_subject']; ?></td>
				<td><?= User::date($row['timestamp']); ?></td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="3" style="text-align: center;">Brak stron w kolejce.</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="3">
					<i>Indeksuj automatycznie z częstotliwością:</i> <?= Form::select('freq', Form::option($freqList, $model->scheduler->getFrequency('indexQueue'))) ?>
				</td>
			</tr>
		</tfoot>
	</table>
<?= Form::close(); ?>

<?php if ($isSearchEnabled) : ?>
<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Dodaj/usuń z indeksu</legend>

		<ol>
			<li>
				<label></label>
				<?= Form::radio('mode', 1, true); ?> Dodaj
				<?= Form::radio('mode', 0, false); ?> Usuń
			</li>
			<li>
				<label>ID strony:</label>
				<?= Form::text('pageId', '', array('style' => 'width: 20px')); ?>
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Dodaj/usuń'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>

<?= Form::open('', array('method' => 'post')); ?>

	<fieldset>
		<legend>Aktualizacja indeksu</legend>

		<ol>
			<li>
				<label>Akcja</label>
				<?= Form::select('action', Form::option(array('', "Indeksacja $queueCount stron", 'Usunięcie indeksu', 'Ponowna indeksacja', 'Optymalizacja indeksu'))); ?>
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Indeksuj strony'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>
<?php endif; ?>