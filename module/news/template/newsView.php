<div id="page">
	<div id="page-header">
		<h1><?= $page->getSubject(); ?></h1>

	</div>
	
	<?php if ($page->getThumbnail()) : ?>
	<img id="thumbnail-120" alt="Miniatura" width="120" height="120" src="<?= url($store . '120-' . $page->getThumbnail()); ?>" />
	<?php endif; ?>

	<div style="overflow: hidden">
		<?= $page->getContent(); ?>
		
		<ul id="news-info">
			<li><label>Host:</label> <?= Html::a(url(Path::connector('newsHome') . '?host=' . $page->getHost()), $page->getHost()); ?></li>
			<li><label>Ostatnio polecany:</label> <?= User::date($recentTime); ?></li>
			<li><label>Dodany przez:</label> <?= Html::a(url('@profile?id=' . $userId), $userName); ?></li>
			<li><label>URL:</label> <?= Html::a($page->getUrl()); ?></li>
			<li><label>Głosy:</label> <?= $page->getRate(); ?></li>
		</ul>
	</div>
</div>
<br style="clear: both;" />