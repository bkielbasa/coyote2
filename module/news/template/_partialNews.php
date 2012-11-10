<?php if (User::$id == User::ANONYMOUS) : ?>
<script src="<?= Url::site(); ?>template/js/window.js" type="text/javascript"></script>
<?php endif; ?>
<script type="text/javascript">
<!--

	var pageLocation = '<?= url($location); ?>';

	<?php if (User::$id == User::ANONYMOUS) : ?>
	$(document).ready(function()
	{
		$('#news .top, #news .bottom').unbind();
		$('#news .top, #news .bottom').click(function()
		{
			$.windowAlert({windowTitle: 'Zaloguj się!', windowMessage: 'Zaloguj się, aby oddać głos na ten wpis!'});
		});

	});
	<?php endif; ?>

//-->
</script>

<div id="news">
	<div id="news-form">
		<?= Form::open(url($location . '/Submit'), array('method' => 'get')); ?>
			<fieldset>
				<?= Form::text('url', 'Dodaj link do katalogu nowości...', array('style' => 'width: 180px')); ?>
				<?= Form::submit('', ''); ?>
			</fieldset>
		<?= Form::close(); ?>
	</div>

	<?php foreach ($news as $row) : ?>
	<div class="news" id="news-<?= $row['news_id']; ?>">
		<h3><a href="<?= url($row['news_url']); ?>"><?= $row['page_subject']; ?></a></h3>

		<span>
			<?php if ($row['vote']) : ?>
			Ostatnio polecane <strong><?= User::date($row['vote']); ?></strong>
			<?php endif; ?>
		</span>
		<?php if (isset($row['news_comment'])) : ?>
		<a href="<?= url($row['location_text']); ?>#box-comment" class="comment"><?= $row['news_comment']; ?> <?= Declination::__($row['news_comment'], array('komentarz', 'komentarze', 'komentarzy')); ?></a>
		<?php endif; ?>

		<div>
			<div>
				<a title="Kliknij, jeżeli uważasz, że ten wpis jest wartościowy" class="top"></a>

				<strong title="Liczba głosów oddanych na ten wpis"><?= $row['news_rate']; ?></strong>

				<a title="Kliknij, jeżeli uważasz, że ten wpis nie jest wartościowy" class="bottom"></a>
			</div>

			<p>
				<?php if ($row['news_thumbnail']) : ?>
				<a href="<?= url($row['location_text']); ?>"><img alt="Miniatura" src="<?= url($store . $row['news_thumbnail']); ?>" width="50" height="50" /></a>
				<?php endif; ?>

				<a class="host" href="<?= url($location . '?host=' . $row['news_host']); ?>"><?= $row['news_host']; ?></a> -
				<a href="<?= url($row['location_text']); ?>"><?= Text::limitHtml(News::plain(Text::plain($row['text_content'])), $maxLength - strlen($row['news_host'])); ?></a>
			</p>
		</div>
	</div>
	<?php endforeach; ?>

	<?php if (isset($pagination)) : ?>
	<?php if ($pagination->getTotalPages() > 1) : ?>
	<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>
	<?php endif; ?>
	<?php endif; ?>
</div>