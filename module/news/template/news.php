<div id="page">
	<?= $page->getContent(); ?>
</div>

<div id="recent-news">
	<h3>Ostatnie nowości</h3>
		
	<ul>
		<?php foreach ($recent as $row) : ?>
		<li>
			<label><strong><?= $row['news_rate']; ?></strong></label>
			<?= Html::a(url($row['location_text']), Text::limitHtml($row['page_subject'], 35), array('title' => $row['page_subject'])); ?>
		</li>
		<?php endforeach; ?>
	</ul>
</div>

<ul id="news-menu">	
	<?php foreach ($mode as $row) : ?>
	<li <?= $input->get->mode == $row[2] ? 'class="active"' : ''; ?>><a title="<?= $row[1]; ?>" href="<?= url($baseUrl . (empty($row[3]) ? '' : ('?' . $row[3]))); ?>"><?= $row[0]; ?></a></li>
	<?php endforeach; ?>
</ul>

<?php include('_partialNews.php'); ?>

<div style="clear: both;"></div>