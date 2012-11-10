<script type="text/javascript">

$(document).ready(function()
{
	if ($('input[name=antispam]').length)
	{
		$('input[name=postkey]').val($('input[name=antispam]').val()).parent().hide();
	}
});

</script>

<div id="page" style="overflow: hidden">
	<div id="page-header">
		<h1><?= $page->getTitle() ? $page->getTitle() : $page->getSubject(); ?></h1>
	</div>

	<?php if ($pastebin) : ?>
	<div id="pastebin-recent">
		<h3><?= __('Ostatnie wpisy'); ?></h3>

		<ul>
			<?php foreach ($pastebin as $row) : ?>
			<li><?= Html::a(url('@page_' . $page->getId() . '?id=' . $row['pastebin_id']), ($row['user_id'] > User::ANONYMOUS ? $row['user_name'] : def($row['pastebin_username'], __('Anonim')))); ?><small><?= Time::diff($row['pastebin_time']); ?> temu</small></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>

	<div style="overflow: hidden;">
		<?= $page->getContent(); ?>

		<?php if (isset($pastebin_id)) : ?>

		<script type="text/javascript">
			SyntaxHighlighter.all();
		</script>

		<pre class="brush: <?= strtolower($pastebin_syntax); ?>;">
<?= $pastebin_content; ?>
		</pre>
		<?php endif; ?>

		<?= $form; ?>
	</div>
</div>