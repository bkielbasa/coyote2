<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8"/>
		<?= $output->getTitle(); ?>

		<?= $output->getStylesheet(); ?>
		<?= $output->getJavascript(); ?>

	</head>

<body>

	<div id="container">		

		<div id="header">
			<img src="<?= Media::img('adm/logo.png'); ?>" id="logo" />

		</div>		

		<div id="content">
			<?= $this->content; ?>
		</div>

		<div id="footer">
			Copyright &copy; <?= Html::a('http://4programmers.net', '4programmers.net'); ?> <?= Config::getItem('version'); ?>
		</div>

	</div>
	<!-- end container -->

</body>
</html>