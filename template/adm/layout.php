<!DOCTYPE html>
<html>

	<head>
		<meta charset="utf-8" />
		<?= include_title(); ?>

		<?= include_stylesheet(); ?>
		<?= include_javascript(); ?>

		<script>
			var baseUrl = '<?= Url::base(); ?>';
		</script>

	</head>

<body>

	<div id="container">

		<div id="header">
			<a href="<?= url('adm'); ?>"><img src="<?= Media::img('adm/logo.png'); ?>" id="logo" /></a>

			<div id="welcome">
				<?php if (class_exists('User', false)) : ?>
				<?= Html::a('User', User::data('name')); ?> [<b><?= $input->getIp(); ?></b>] | <?= Html::a(url('adm/Logout'), 'Wyloguj z panelu'); ?> | <?= Html::a(Url::base(), 'Strona główna'); ?>
				<?php endif; ?>
			</div>

			<ul id="menu">
				<?php foreach (Adm::getMenu() as $row) : ?>
				<li><a <?= $row['menu_focus'] ? ' class="focus"' : ''; ?> href="<?= url($row['menu_url']); ?>"><span><?= $row['menu_text']; ?></span></a></li>
				<?php endforeach; ?>
			</ul>

			<div id="submenu">
				<ul>
					<?php foreach (Adm::getCurrentSubMenu() as $row) : ?>
					<li><a <?= $row['menu_focus'] ? ' class="focus"' : ''; ?> href="<?= url($row['menu_url']); ?>"><?= $row['menu_text']; ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>

		<div id="content">
			<?php if (Config::getItem('shutdown')) : ?>
			<p class="error">UWAGA! Serwis jest wyłaczony. Możesz właczyć system korzystając z opcji <b>Wyłaczenie systemu</b></p>
			<?php endif; ?>
			<?php if (isset($message)) : ?>
			<p class="message"><?= $message; ?></p>
			<?php endif; ?>

			<?= $this->content; ?>
		</div>

		<div id="footer">
			Copyright &copy; <?= Html::a('http://4programmers.net', '4programmers.net'); ?> <?= Config::getItem('version'); ?> (<?= Text::formatBenchmark(Benchmark::elapsed()); ?>)
		</div>

	</div>
	<!-- end container -->

</body>
</html>