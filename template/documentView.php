<div id="page">	
	<?php if (@$children) : ?>
	<div id="page-children">

		<h3>Strony w tej kategorii</h3>

		<ul>
			<?php foreach ($children as $row) : ?>
			<li <?= $row['page_depth'] > $page->getDepth() + 1 ? 'style="margin-left: ' . (($row['page_depth'] - $page->getDepth()) * 5) . 'px"' : ''; ?>>
				<a title="<?= $row['page_title'] ? $row['page_title'] : $row['page_subject']; ?>" href="<?= url($row['location_text']); ?>"><?= Text::limit($row['page_subject'], 33); ?></a>
				<small>Ostatnia aktualizacja: <?= User::formatDate($row['page_time']); ?></small>
			</li>
			<?php endforeach; ?>

		</ul>
		
	</div>
	<?php endif; ?>
		
	<div id="page-header">
		<h1><?= $page->getTitle() ? $page->getTitle() : $page->getSubject(); ?></h1>
	</div>

	<div>		
		<?= $page->getContent(); ?>	
	</div>
	<br style="clear: both;" />

</div>