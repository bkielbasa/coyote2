<script type="text/javascript">
<!--

	$(document).ready(function()
	{
		var toggle = false;

		$('#page-menu').bind('click', function()
		{
			$(this).animate(
				{
					right:		(toggle ? '0' : '223px')
				}, 200
			);
			$('#page-tools').animate(
				{
					right:		(toggle ? '-223px' : '0')
				}, 200
			);

			toggle = !toggle;
		});
	});
//-->
</script>

<div id="page">
	<div id="page-menu">
		<span title="Pokaż/schowaj menu"></span>
	</div>

	<div id="page-tools">
		<ul>
			<?php if ($module->wiki('enableWiki', $page->getId())) : ?>
			<li class="page-edit"><a title="Edytuj ten artykuł" href="<?= url('Edit/' . $page->getLocation()); ?>">Edycja</a></li>
			<?php elseif (Auth::get('a_')) : ?>
			<li class="page-edit"><a title="Edytuj ten artykuł" href="<?= url('Edit/' . $page->getLocation()); ?>">Edycja (strona zablokowana)</a></li>
			<?php endif; ?>
			<li class="page-add"><a title="Napisz nowy artykuł w tym dziale" href="<?= url('Write/' . $page->getLocation()); ?>">Napisz nowy artykuł w tym dziale</a></li>
			<?php if ($load->model('watch')->isWatched($page->getId(), $module->getId('wiki'))) : ?>
			<li class="page-watch"><a title="Przestań obserwować ten artykuł" href="<?= url('Watch/' . $page->getLocation()); ?>">Zaprzestań obserwacji</a></li>
			<?php else : ?>
			<li class="page-watch"><a title="Obseruj ten artykuł" href="<?= url('Watch/' . $page->getLocation()); ?>">Obserwuj</a></li>
			<?php endif; ?>
			<li class="page-history"><a title="Pokaż historię edycji zmian tego artykułu" href="<?= url('History/' . $page->getLocation()); ?>">Historia i autorzy</a></li>
		</ul>
	</div>

	<?php if (@$children) : ?>
	<div id="page-children">

		<h3>Strony w tej kategorii</h3>

		<ul>
			<?php foreach ($children as $row) : ?>
			<li <?= $row['page_depth'] > $page->getDepth() + 1 ? 'style="margin-left: ' . (($row['page_depth'] - $page->getDepth()) * 5) . 'px"' : ''; ?>>
				<a title="<?= $row['page_title'] ? $row['page_title'] : $row['page_subject']; ?>" href="<?= url($row['location_text']); ?>"><?= Text::limit($row['page_subject'], 33); ?></a>
				<small>Ostatnia aktualizacja: <?= User::date($row['page_edit_time']); ?></small>
			</li>
			<?php endforeach; ?>
		</ul>

	</div>
	<?php endif; ?>

	<div id="page-header">
		<h1><?= $page->getTitle() ? $page->getTitle() : $page->getSubject(); ?></h1>
	</div>

	<?php if (isset($session->message)) : ?>
	<p class="message"><?= $session->getAndDelete('message'); ?></p>
	<?php endif; ?>

	<div>
		<?= $page->getContent(); ?>
	</div>

	<br style="clear: both;" />
</div>