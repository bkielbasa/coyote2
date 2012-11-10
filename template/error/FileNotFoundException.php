<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8"/>
		<title><?php echo $error ?></title>

		<link rel="stylesheet" type="text/css" href="<?= Url::base(); ?>template/adm/main.css"  />

		<style type="text/css">
		#detail
		{
			background:		#dfdfdf;
			border:			1px solid #999;
			padding:		5px;
		}
		</style>
	
	</head>

<body>

	<div id="container">		

		<div id="header" style="overflow: hidden;">
			<img src="<?= Url::base(); ?>template/adm/img/logo.png" id="logo" />
		</div>

		<div id="content">
			<p><?= $description; ?></p>
			<div id="detail"><code><?= $this->getMessage(); ?></code></div>
		</div>

		<div id="bottom">
			<div class="left"></div>
			<div class="right"></div>
		</div>		

		<div id="footer">
			Copyright &copy; 4programmers.net
		</div>

	</div>
</body>
</html>