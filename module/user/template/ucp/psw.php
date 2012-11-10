<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<?php $selected = 'psw-icon'; include('_partialUserMenu.php'); ?>

<div id="box-user" class="box">
	<?= $form; ?>
</div>

<div style="clear: both;"></div>