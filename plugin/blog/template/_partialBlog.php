<style type="text/css">
	<!--
	@import url('<?= Url::site(); ?>plugin/blog/template/css/blog.css');
	//-->
</style>

<div id="blog">
	<?php foreach ($blog as $row) : ?>
	<div class="blog-entry">

		<div class="blog-entry-left">

			<div class="blog-entry-date">
				<?= date('d', $row['page_time']); ?>
				<span><?= date('M y', $row['page_time']); ?></span>
			</div>
		</div>

		<div class="blog-entry-right">
			<h2><?= Html::a(url($row['location_text']), $row['page_subject'], array('title' => def($row['page_title'], $row['page_subject']))); ?></h2>

			<div class="blog-entry-user">
				<?php if ($row['user_photo']) : ?>
				<img src="<?= Url::site(); ?>store/_a/<?= $row['user_photo']; ?>" width="27" height="20" />
				<?php else : ?>
				<img src="<?= Url::site(); ?>template/img/avatar.jpg" width="27" height="20" />
				<?php endif; ?>

				<?= Html::a(url('@profile?id=' . $row['user_id']), $row['user_name']); ?>  |  <a href="<?= url($row['location_text']) . '#box-comment'; ?>"><?= $row['page_comment']; ?> <?= Declination::__($row['page_comment'], array('komentarz', 'komentarze', 'komentarzy')); ?></a>
			</div>

			<div class="blog-entry-text">
				<?= Page::load((int) $row['page_id'])->getContent(); ?>
			</div>

			<div class="blog-entry-link">
				<?= Html::a(url($row['location_text']), 'Przejdź do artykułu'); ?>
			</div>

		</div>

	</div>
	<?php endforeach; ?>
</div>