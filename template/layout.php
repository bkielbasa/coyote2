<!DOCTYPE html>
<html lang="pl">
	<head>
		<meta charset="utf-8" />

		<?= $output->getHttpMeta(); ?>
		<?= $output->getMeta(); ?>
		<?= $output->getTitle(); ?>
		<?= $output->getStylesheet(); ?>

		<link rel="icon" type="image/png" href="<?= Url::base(); ?>template/img/favicon.png" />

		<?= $output->getJavascript(); ?>
	</head>
	<body>

	<noscript>
		<div id="fixed-message">
			Brak obsługi JavaScript może spowodować, iż pewne elementy strony nie będą działać poprawnie
		</div>
	</noscript>

	<div id="container">

		<div id="header">

			<div id="header-logo">
				<a id="logo" title="4programmers.net - Programowanie" href="<?= url('@home'); ?>"></a>
				<?= Region::display('header'); ?>
			</div>
			<!-- end logo -->
		</div>
		<!-- end header -->

		<div id="breadcrumb">
			<?php echo Breadcrumb::display(); ?>
		</div>

		<div id="content">
			<?= $this->content; ?>
			<?= Region::display('content'); ?>
		</div>

		<?= Region::display('footer'); ?>

		<div id="copyright">
			<?= Text::evalCode(htmlspecialchars_decode(Config::getItem('site.copyright'))); ?>
		</div>
	</div>

	</body>
</html>