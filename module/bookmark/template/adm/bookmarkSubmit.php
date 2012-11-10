<h1>Edycja zakładki</h1>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label>URL</label>
				<?= Form::input('url', $input->post('url', @$bookmark_url), array('size' => 100)); ?>
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Zapisz'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>

<?php if ($user) : ?>
<h2>Lista użytkowników</h2>

<table>
	<thead>
		<tr>
			<th>Nazwa użytkownika</th>
			<th>Opis</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($user as $row) : ?>
		<tr>
			<td><?= $row['user_name']; ?></td>
			<td><?= $row['bookmark_description']; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>

<?php if ($digg) : ?>
<h2>Polecali</h2>

<table>
	<thead>
		<tr>
			<th>Nazwa użytkownika</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($digg as $row) : ?>
		<tr>
			<td><?= $row['user_name']; ?></td>
			<td><?= $row['digg_value'] > 0 ? 'Polecił' : 'Nie polecił'; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>