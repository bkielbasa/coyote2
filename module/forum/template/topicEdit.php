<script type="text/javascript">
<!--

	var hash = '<?= $hash; ?>';
	var preventAjax = true;

	$(document).ready(function()
	{
		$.posting.init({currentUrl: '<?= url($page->getLocation()); ?>'});
		$('.page').loadWikiEditor();
	});
//-->
</script>

<a style="display: none;" title="Strona główna forum" href="<?= url('@forum'); ?>" data-shortcut="g+i">Strona główna forum</a>

<div id="body">
	<ul class="f-menu-top">
		<li id="thread-button"><a href="<?= url($page->getLocation()); ?>">Treść wątku</a></li>

 		<li id="reply-top" class="focus"><a><span>Edycja</span></a></li>
	</ul>
	<div style="clear: both;"></div>

	<div class="page">
		<?= $form; ?>
	</div>
</div>