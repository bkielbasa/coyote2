<?php $selected = 'infobox-icon'; include(Config::getBasePath() . 'module/user/template/ucp/_partialUserMenu.php'); ?>

<div id="box-infobox" class="box">
	<?php if ($pagination->getTotalPages() > 1) : ?>
	<p style="margin: 10px auto; text-align: center">Strona <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></p>
	<?php endif; ?>

	<?php if (!empty($infobox)) : ?>
	<?= $infobox['infobox_content']; ?>
	<?php else : ?>
	<p style="margin: 10px auto; text-align: center">Brak komunikatów informacyjnych.</p>
	<?php endif; ?>

</div>

<div style="clear: both;"></div>