<script type="text/javascript">
<!--
	function selectAction(itemId)
	{
		if (itemId.value > 0)
		{
			if (itemId.value == 2)
			{
				window.location.href = '<?= url('adm/Scheduler/Submit'); ?>';
			}
			else
			{
				if (confirm('Zmiany mogą mieć wpływ na działanie systemu. Kontynuować?'))
				{
					$('form').submit();
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

<h1>Ustawienia zadań</h1>

<p>Zakładka ta umożliwia ustawianie czynności, które mają być wykonywane cyklicznie.
Konfiguracja umożliwia ustalenie klasy, metody oraz częstotliwości wykonywania
danego kodu.</p>

<p>Do prawidłowego działania, na serwerze ustawiony musi być odpowiedni wpis w aplikacji
<b>cron</b> - np.: <br /><code>wget <?= Url::site(); ?>scheduler.php?key=<?= Config::getItem('scheduler'); ?></code></p>

<?= Form::open('', array('method' => 'post')); ?>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Nazwa reguły</th>
				<th>Opis</th>
				<th>Częstotliwość</th>
				<th>Ostatnie wykonanie</th>
				<th>Aktywna</th>
				<th>Status</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($scheduler) : ?>
			<?php foreach ($scheduler as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Scheduler/Submuit/' . $row['scheduler_id']), $row['scheduler_id']); ?></td>
				<td><?= Html::a(url('adm/Scheduler/Submit/' . $row['scheduler_id']), $row['scheduler_name']); ?></td>
				<td><?= $row['scheduler_description']; ?></td>
				<td><?= !$row['scheduler_frequency'] ? 'codziennie o ' . $row['scheduler_time'] : $row['scheduler_frequency'] . ' sek'; ?></td>
				<td><?= $row['scheduler_lunch'] ? User::date($row['scheduler_lunch']) : '--'; ?></td>
				<td><?= $row['scheduler_enable'] ? 'Tak' : 'Nie'; ?></td>
				<td><?= $row['scheduler_lock'] ? 'W trakcie realizacji' : 'Czeka na wykonanie'; ?></td>
				<td class="checkbox">
					<?= Form::checkbox('delete[]', $row['scheduler_id'], false); ?>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php else : ?>
			<tr>
				<td colspan="7" style="text-align: center;">Brak reguł zdefiniowanych w bazie danych.</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="7">
					<select onchange="selectAction(this);">
						<option>akcja...</option>
						<option value="1">Usuń zaznaczone</option>
						<option value="2">Dodaj nową regułę</option>
					</select>
				</td>
				<td class="checkbox">
					<a id="selectAll" title="Zaznacz wszystkie"></a>
				</td>
			</tr>
		</tfoot>
	</table>
<?= Form::close(); ?>