<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<div id="page">
	<div id="page-header" style="overflow: hidden; border-bottom: none;">
		<div id="page-sort">
			<ul>
				<?php foreach ($sort as $arr) : ?>
				<li <?= $input->get->sort == $arr[2] ? ' class="active"' : '';?>><a title="<?= $arr[1]; ?>" href="<?= $baseUrl; ?>?sort=<?= $arr[2]; ?>"><span><?= $arr[0]; ?></span></a></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div id="page-tools">
			<ul>
				<li><a href="<?= url(Path::connector('bookmark')); ?>/Add">Dodaj zakładkę</a></li>
				<li><a href="<?= url('@user?controller=Bookmark'); ?>">Moje zakładki</a></li>
			</ul>
		</div>
	</div>
	<?= $page->getContent(); ?>

	<?php if ($pagination->getTotalPages() > 1) : ?>
	<div class="pagination">Strona <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></div>
	<?php endif; ?>

	<?= $bookmark; ?>

	<?php if ($pagination->getTotalPages() > 1) : ?>
	<div class="pagination">Strona <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></div>
	<?php endif; ?>
</div>