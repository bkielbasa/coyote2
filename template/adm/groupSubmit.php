<h1><?= @$group_name; ?></h1>

<p>Tutaj możesz utworzyć nową grupę lub edytować już istniejącą. Jeżeli chcesz wypisać lub przypisać danego użytkownika do grupy, możesz to zrobić na zakładce <?= Html::a(url('adm/User'), 'Uzytkownicy'); ?>.</p>

<?= Form::open('adm/Group/Submit/' . @$group_id, array('method' => 'post')); ?>
	<fieldset>
		<legend>Edycja grupy <?= @$group_name; ?></legend>

		<ol>
			<li>
				<label>Nazwa grupy</label>
				<?= Form::input('name', $input->post('name', @$group_name)); ?>
				<ul><?= $filter->formatMessages('name', 'li'); ?></ul>
			</li>
			<li>
				<label title="Podaj nazwę użytkownika, który będzie liderem grupy">Lider grupy <em>*</em></label>
				<?= Form::input('leader', $input->post('leader', @$leader)); ?>
				<ul><?= $filter->formatMessages('leader', 'li'); ?></ul>
			</li>
			<li>
				<label>Opis</label>
				<?= Form::textarea('desc', $input->post('desc', @$group_desc), array('cols' => 65, 'rows' => 10)); ?>
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Zapisz zmiany'); ?> <?= @$group_name ? Form::button('', 'Uprawnienia grupy', array('class' => 'group-button', 'onclick' => 'window.location.href = \'' . url('adm/Auth/Submit/' . $group_id) . '\'')) : ''; ?>
			</li>
		</ol>
	</fieldset>
</form>

<?php if (isset($user)) : ?>
<h2>Członkowie grupy</h2>
<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>

<table>
	<caption>Użytkownicy <?= isset($count) ? '(' . $count . ')' : ''; ?></caption>

	<thead>
		<tr>
			<?= Sort::displayTh('user_id', 'ID'); ?>
			<?= Sort::displayTh('user_name', 'Nazwa użytkownika'); ?>
			<?= Sort::displayTh('session_stop', 'Status'); ?>
			<?= Sort::displayTh('user_active', 'Aktywny'); ?>
			<?= Sort::displayTh('user_confirm', 'Potwierdzony e-mail'); ?>
			<?= Sort::displayTh('user_regdate', 'Data rejestracji'); ?>
			<?= Sort::displayTh('user_lastvisit', 'Data aktywności'); ?>
			<?= Sort::displayTh('user_ip', 'IP'); ?>
			<?= Sort::displayTh('user_ip_login', 'IP użyte przy logowaniu'); ?>
		</tr>
	</thead>
	<tbody>
	<?php load_helper('array'); ?>
	<?php if ($user) : ?>
	<?php foreach ($user as $row) : ?>
		<tr>
			<td><?= Html::a(url('adm/User/Submit/' . $row['user_id']), $row['user_id']); ?></td>
			<td><?= Html::a(url('adm/User/Submit/' . $row['user_id']), $row['user_name']); ?></td>
			<td><?= $row['session_id'] ? 'Online' : 'Offline'; ?></td>
			<td><?= element(array('Nie', 'Tak'), $row['user_active']); ?></td>
			<td><?= element(array('Nie', 'Tak'), $row['user_confirm']); ?></td>
			<td><?= User::formatDate($row['user_regdate']); ?></td>
			<td><?= User::formatDate($row['session_id'] ? $row['session_stop'] : $row['user_lastvisit']); ?></td>
			<td><abbr class="whois-ip"><?= $row['user_ip']; ?></abbr></td>
			<td><span title="<?= $row['user_ip_login']; ?>"><?= Text::limit($row['user_ip_login'], 40); ?></span></td>
		</tr>
	<?php endforeach; ?>
	<?php else: ?>
		<tr>
			<td colspan="9" style="text-align: center;">Brak użytkowników spełniających kryteria.</td>
		</tr>
	<?php endif;?>
	</tbody>
</table>

Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?>
<?php endif; ?>