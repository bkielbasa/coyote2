<h1>Lista zalogowanych użytkowników</h1>

<p>
	W serwisie przebywa obecnie <strong><?= $totalItems; ?></strong> <?= Declination::__($totalItems, array('użytkownik', 'użytkowników', 'użytkowników')); ?>.
	W tym <strong><?= $totalRobots; ?></strong> <?= Declination::__($totalRobots, array('robotów sieciowych', 'robot sieciowy', 'robotów sieciowych')); ?>
	i <strong><?= $totalItems - $totalRobots; ?></strong> <?= Declination::__($totalItems - $totalRobots, array('człowiek', 'ludzi', 'ludzi')); ?>
</p>

<?php if (Config::getItem('session.max')) : ?>
<p>
	Najwięcej użytkowników (<strong><?= Config::getItem('session.max'); ?></strong>) było: <?= User::date(Config::getItem('session.max_time')); ?>.
	Ostatnie wywołanie <acronym title="Garbage Collector">GC</acronym>: <strong><?= User::date(Config::getItem('session.last_gc')); ?></strong>
</p>
<?php endif; ?>

<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>

<table>
	<caption>Lista zalogowanych użytkowników</caption>
	<thead>
		<tr>
			<?= Sort::displayTh('user_name', 'Nazwa użytkownika'); ?>
			<?= Sort::displayTh('session_start', 'Data logowania'); ?>
			<?= Sort::displayTh('session_stop', 'Ostatnia aktywność'); ?>
			<?= Sort::displayTh('session_ip', 'IP'); ?>
			<?= Sort::displayTh('session_page', 'Strona'); ?>
			<?= Sort::displayTh('session_browser', 'User-agent'); ?>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($user as $row) : ?>
		<tr>
			<td><?= $row['session_user_id'] > User::ANONYMOUS ? Html::a(url('adm/User/Submit/' . $row['session_user_id']), $row['user_name']) : ($row['session_robot'] ? $row['session_robot'] : $row['user_name']); ?></td>
			<td><?= User::formatDate($row['session_start']); ?></td>
			<td><?= User::formatDate($row['session_stop']); ?></td>
			<td><abbr class="whois-ip"><?= $row['session_ip']; ?></abbr></td>
			<td><?= $row['session_page'] == '/User/ajax/session' ? 'Bezczynny' : Html::a(url(ltrim($row['session_page'], '/')), $row['session_page']); ?></td>
			<td><?= $row['session_browser']; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?>