<?= Form::open('', array('method' => 'get')); ?>
	<fieldset>
		<legend>Ustawienia dostępu dla forum</legend>

		<ol>
			<li>
				<label>Forum</label> 
				<?= Form::select('f', Form::option($forum, $forumId)); ?>
			</li>
			<li>
				<label>Grupa</label>
				<?= Form::select('g', Form::option($group, $groupId)); ?>
			</li>
			<li>
				<label>&nbsp;</label>
				<?= Form::submit('', 'Ustaw prawa dostępu'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>

<?php if ($forumId && $groupId) : ?>
<?= Form::open('', array('method' => 'post')); ?>
	<table>	
		<caption>Uprawnienia dostępu</caption>

		<thead>
			<th>Opcja</th>
			<th>Opis</th>
			<th>Wartość</th>
		</thead>
		<tbody>
		<?php foreach ($options as $optionId => $row) : ?>
			<tr>
				<td><?= $row['option_text']; ?></td>
				<td><?= $row['option_label']; ?></td>
				<td><?= Form::select('option[' . $optionId . ']', Form::option(array('Nie', 'Tak'), $input->post['option'][$optionId] ? $input->post['option'][$optionId] : (isset($groupAuth[$optionId]) ? $groupAuth[$optionId] : $row['option_default']))); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?= Form::submit('', 'Zapisz ustawienia'); ?>	
<?= Form::close(); ?>
<?php endif; ?>