<script type="text/javascript">
<!--

	var hash = '<?= $hash; ?>';
	var preventAjax = true;

	$(document).ready(function()
	{
		$.posting.init({currentUrl: '<?= url($page->getLocation()); ?>', topicId: <?= $page->getTopicId(); ?>});
		$('.page').loadWikiEditor();

		<?php if (User::$id == User::ANONYMOUS) : ?>
		$('input[name=postkey]').val($('input[name=antispam]').val()).parent().hide();
		<?php endif; ?>
	});
//-->
</script>

<?php if ((time() - $page->getLastPostTime()) > Time::YEAR) : ?>
<p class="note">
	Ostatni post w tym wątku datowany jest na <strong><?= User::date($page->getTime()); ?></strong>.<br />
 	Problem lub informacje w nim zawarte mogą być nieaktualne. Zastanów się, czy na pewno chcesz dodać nowy post w tym wątku?
</p>
<?php endif; ?>

<a style="display: none;" title="Strona główna forum" href="<?= url('@forum'); ?>" data-shortcut="g+i">Strona główna forum</a>

<div id="body">
	<ul class="f-menu-top">
		<li id="thread-button" class="prevent"><a href="<?= url($page->getLocation()); ?>">Treść wątku</a></li>

 		<li id="reply-top" class="focus" title="Napisz odpowiedź w tym temacie"><a><span>Odpowiedz</span></a></li>
	</ul>
	<div style="clear: both;"></div>

	<div class="page">
		<?= $form; ?>
	</div>
</div>