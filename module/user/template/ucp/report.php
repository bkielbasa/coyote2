<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<p class="note">Ta strona służy jedynie do raportowania aktów wandalizmów, wulgaryzmów czy innego nieprawidłowego zachowania łamiącego regulamin.<br /> Prosimy <b>nie</b> używać jej do zgłaszania błędów, podziękowań itp.</p>

<div class="box-header">
	Raportowanie strony "<?= $page_subject; ?>"
</div>

<div class="box" style="margin-top: 0;">
	<?= $form; ?>
</div>