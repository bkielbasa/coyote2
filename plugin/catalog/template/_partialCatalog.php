<style type="text/css">
<!--
@import url('<?= Url::site(); ?>plugin/catalog/template/css/catalog.css');
//-->
</style>

<div id="catalog">

	<?php if ($category) : ?>
	<div id="folder">
		<ol>
			<?php foreach ($category as $row) : ?>
			<li>
				<a href="<?= url($row['location_text']); ?>"><?= def($row['page_title'], $row['page_subject']); ?> (<?= (int) $row['location_children']; ?>)</a>
			</li>
			<?php endforeach; ?>
		</ol>
	</div>
	<?php endif; ?>

	<?php if ($children) : ?>
	<script type="text/javascript">
	<!--

	var baseUrl = '<?= url($url); ?>';

	$(document).ready(function()
	{
		$('input[name=page]').bind(($.browser.opera ? "keypress" : "keydown"), function(e)
		{
			keyCode = e.keyCode || window.event.keyCode;

			if (keyCode == 13)
			{
				e.preventDefault();

				window.location.href = baseUrl + 'start=' + ((parseInt($(this).val()) -1) * 10) + '#catalog';
			}
		});
	});
	//-->
	</script>

	<div class="catalog-nav">
		<p class="left">
			<a title="Pierwsza strona" class="button resultset-first<?= !$firstPage ? '-disable' : ''; ?>" <?= $firstPage ? 'href="' . url($url . 'start=' . ($firstPage -1) * 10) . '#catalog"' : ''; ?>>Pierwsza strona</a>
			<a title="Poprzednia strona" class="button resultset-prev<?= !$previousPage ? '-disable' : ''; ?>" <?= $previousPage ? 'href="' . url($url . 'start=' . ($previousPage -1) * 10) . '#catalog"' : ''; ?>>Poprzednia strona</a>
		</p>

		<p class="center">
			Strona <?= Form::text('page', $currentPage, array('maxlength' => 3, 'style' => 'width: 20px')); ?> z <strong><?= $totalPages; ?></strong>
		</p>

		<p class="right">
			<a title="Kolejna strona" class="button resultset-next<?= $nextPage ? '' : '-disable';?>" <?= $nextPage ? 'href="' . url($url . 'start=' . ($nextPage -1) * 10) . '#catalog"' : ''; ?>>Następna strona</a>
			<a title="Ostatnia strona" class="button resultset-last<?= $lastPage ? '' : '-disable';?>" <?= $lastPage ? 'href="' . url($url . 'start=' . ($lastPage -1) * 10) . '#catalog"' : ''; ?>>Ostatnia strona</a>
		</p>
	</div>

	<?php foreach ($children as $row) : ?>
	<div class="catalog">
		<h3><a title="<?= $row['page_title'] ? $row['page_title'] : $row['page_subject']; ?>" href="<?= url($row['location_text']); ?>"><?= $row['page_subject']; ?></a></h3>

		<span>
			Data dodania <strong><?= User::date($row['page_time']); ?></strong>
		</span>

		<?php if (isset($row['page_comment'])) : ?>
		<a href="<?= url($row['location_text']); ?>#box-comment" class="comment"><?= $row['page_comment']; ?> <?= Declination::__($row['page_comment'], array('komentarz', 'komentarze', 'komentarzy')); ?></a>
		<?php endif; ?>

		<div>
			<p>
				<a href="<?= url($row['location_text']); ?>"><?= preg_replace('#{{.*}}#', '', Text::limitHtml(Text::plain($row['text_content']), 250)); ?></a>
			</p>
		</div>
	</div>
	<?php endforeach; ?>

	<div class="catalog-nav">
		<p class="left">
			<a title="Pierwsza strona" class="button resultset-first<?= !$firstPage ? '-disable' : ''; ?>" <?= $firstPage ? 'href="' . url($url . 'start=' . ($firstPage -1) * 10) . '#catalog"' : ''; ?>>Pierwsza strona</a>
			<a title="Poprzednia strona" class="button resultset-prev<?= !$previousPage ? '-disable' : ''; ?>" <?= $previousPage ? 'href="' . url($url . 'start=' . ($previousPage -1) * 10) . '#catalog"' : ''; ?>>Poprzednia strona</a>
		</p>

		<p class="center">
			Strona <?= Form::text('page', $currentPage, array('maxlength' => 3, 'style' => 'width: 20px')); ?> z <strong><?= $totalPages; ?></strong>
		</p>

		<p class="right">
			<a title="Kolejna strona" class="button resultset-next<?= $nextPage ? '' : '-disable';?>" <?= $nextPage ? 'href="' . url($url . 'start=' . ($nextPage -1) * 10) . '#catalog"' : ''; ?>>Następna strona</a>
			<a title="Ostatnia strona" class="button resultset-last<?= $lastPage ? '' : '-disable';?>" <?= $lastPage ? 'href="' . url($url . 'start=' . ($lastPage -1) * 10) . '#catalog"' : ''; ?>>Ostatnia strona</a>
		</p>
	</div>

	<?php endif; ?>

</div>