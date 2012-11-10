<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8"/>
		<title>You're banned!</title>

		<link rel="stylesheet" type="text/css" href="<?= Url::base(); ?>template/adm/main.css"  />

	</head>

	<style type="text/css">
	#detail
	{
		background:		#dfdfdf;
		border:			1px solid #999;
		padding:		5px;
	}
	</style>

<body>

	<div id="container">		

		<div id="header" style="overflow: hidden;">
			<img src="<?= Url::base(); ?>template/adm/img/logo.png" id="logo" />
		</div>

		<div id="content">
			<h1>You're banned!</h1>
			<p>Not possible to access.</p>

			<div id="detail">Reason: <code><?= $ban_reason; ?></code><br /><br />Expired date: <?= $expired; ?></div>
			<p><?= $ban_ip; ?> / <?= $u1_name; ?> / <?= $ban_email; ?> by <?= $u2_name; ?></p>

			<?php if (time() > $ban_flood && $u2_email) : ?>
			<div id="note">
				<hr />
				<p>You can send a note to <b><?= $u2_name; ?></b>:</p>
				<?= Form::open('', array('method' => 'post')); ?>

				<?php if (!$u1_email) : ?>
				<?= Form::input('email', 'your@email'); ?><br /><br />
				<?php endif; ?>			
				<textarea name="message" style="width: 100%; height: 100px;"></textarea>
				<?= Form::submit('submit', 'Send'); ?>		
			</div>
			<?php endif; ?>

		</div>

		<div id="footer">
			Copyright &copy; <?= date('Y'); ?> 4programmers.net
		</div>

	</div>
</body>
</html>