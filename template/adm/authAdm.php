<h1>Uprawnienia modułów</h1>

<p>Na tej stronie możesz ustalić jakie uprawnienia wymagane są, aby możliwy był dostęp do danego modułu w <strong>panelu administracyjnym</strong>.</p>

<?= Form::open('', array('method' => 'post')); ?>
	<table>
		<caption>Uprawnienia modułów</caption>

		<thead>
			<tr>
				<th>Moduł</th>
				<th>Uprawnienie</th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($menu as $row) : ?>
			<tr>
				<td><b><?= $row['row']['menu_text']; ?></b></td>
				<td><?= Form::select('auth[' . $row['row']['menu_id'] . ']', Form::option($acl, $row['row']['menu_auth'])); ?></td>
			</tr>
				<?php foreach ((array)@$row['subcat'] as $row2) : ?>
				<tr>
					<td style="padding-left: 30px;"><?= $row2['menu_text']; ?></td>
					<td><?= Form::select('auth[' . $row2['menu_id'] . ']', Form::option($acl, $row2['menu_auth'])); ?></td>
				</tr>
				<?php endforeach ; ?>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?= Form::submit('', 'Zapisz zmiany', array('class' => 'button')); ?>
</form>