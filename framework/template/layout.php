<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
<head>
<meta http-equiv="Content-Language" content="pl" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<?= include_base(); ?>

<?= include_meta(); ?>
<?= include_title(); ?>
<?= include_stylesheet(); ?>
<?= include_javascript(); ?>
</head>

<body>
<div id="container">
	<div id="topBg"></div>
	
	<a href="<?= url('@home'); ?>"><img alt="Logo" id="logo" src="<?= Url::site(); ?>template/img/logo.gif" /></a>

	<ul id="menu">
		<li><a href="<?= url('Index/Manual'); ?>"><span>Manual</span></a></li>
		<li><a href="<?= url('Index/API'); ?>"><span>API Doc</span></a></li>
	</ul>

	<div id="content">
		<?= $this->content; ?>		
	</div>
</div>
<div id="bottom">
	<div class="left"></div>
	<div class="right"></div>
</div>
<div id="footer">
	Copyright &copy; 2003-<?= date('Y'); ?> Coyote Group v. <?= Core::version(); ?> <br />
	Czas generowania strony: <?= Benchmark::elapsed(); ?> sek.
</div>
</body>
</html>