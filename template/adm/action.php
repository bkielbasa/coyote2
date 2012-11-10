<h1>Akcje</h1>

<p>Akcje umożliwiają wykonywanie pewnych, określonych zadań po przypisaniu do właściwego triggera. Np. istnieje możliwość 
utworzenia akcji, która będzie wyświetlała wiadomość o poprawnej rejestracji użytkownika. Następnie taką akcję 
można przypisać do triggera, który zostanie wywołany po rejestracji nowego konta. W ten sposób możemy reagować 
na pewne czynności bez konieczności pisania nowego kodu zdarzeniowego dla triggera.<p>

<?= Form::open('adm/Action/Submit', array('method' => 'get')); ?>
	<fieldset>
		<legend>Dodaj nową akcję</legend>

		<div><b>Akcja</b> <?= Form::select('class', Form::option($actions, 1)); ?> </div>
		<div><b>&nbsp;</b> <?= Form::submit('', 'Dodaj akcję', array('class' => 'button')); ?></div>
	</fieldset>
</form>

<?php if ($action) : ?>
<?= Form::open('', array('method' => 'post')); ?>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>Akcja</th>
				<th>Nazwa akcji</th>
				<th>Usuń</th>
			</tr>
		</thead>
		<tbody>
			<?php load_helper('array'); ?>
			<?php foreach ($action as $row) : ?>
			<tr>
				<td><?= Html::a(url('adm/Action/Submit/' . $row['action_id']), $row['action_id']); ?></td>
				<td><?= element($actions, $row['action_class']); ?></td>
				<td><?= Html::a(url('adm/Action/Submit/' . $row['action_id']), $row['action_name']); ?></td>
				<td><?= Form::checkbox('delete[]', $row['action_id'], false); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?= Form::submit('del', 'Usuń zaznaczone', array('class' => 'button')); ?>
</form>
<?php endif; ?>