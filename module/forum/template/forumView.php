<script type="text/javascript">
<!--

	var hash = '<?= $hash; ?>';
	var preventAjax = false;

	var currMode = '<?= $viewMode; ?>';
	var defaultMode = '<?= $viewMode; ?>';

	var hashChange = function()
	{
		if (jQuery.inArray(window.location.hash, ['#all', '#unanswered', '#votes', '#unread']) > -1)
		{
			if (('#' + currMode) != window.location.hash)
			{
				$('#body .f-menu-top a[href$=' + window.location.hash + ']').trigger('click');
			}
		}
		else if (window.location.hash == '')
		{
			if (currMode != defaultMode)
			{
				//$('a[href$=#' + defaultMode + ']').trigger('click');
			}
		}
	};

	$(document).ready(function()
	{
		$.posting.init({currentUrl: '<?= url($page->getLocation()); ?>', forumId: '<?= $page->getForumId(); ?>'});

		$(window).hashchange(hashChange);
		$(window).hashchange();

		$('#body').delegate('select[name=forum]', 'change', function()
		{
			window.location.href = $(this).val();
		});
		$('select[name=page]').change(function()
		{
			window.location.href = '<?= url($page->getLocation()); ?>?page=' + $(this).val();
		});

		$('#search-top input[name=q][autocomplete=off]').autocomplete({url: '<?= url(Path::connector('forumSearch')); ?>'});
		$('#search-top').mouseenter(function()
		{
			$(this).addClass('search-holder');
		});
	});
//-->
</script>

<a style="display: none;" title="Strona główna forum" href="<?= url('@forum'); ?>" data-shortcut="g+i">Strona główna forum</a>
<a style="display: none;" title="Strona glówna kategorii forum" href="<?= url($page->getLocation()); ?>" data-shortcut="g+f">Kategoria forum</a>

<div id="page">
	<?= $page->getContent(); ?>
</div>

<?php if ($forum) : ?>
<?php include('_partialCategory.php'); ?>
<?php endif; ?>

<div id="body">
	<ul class="f-menu-top">
		<?= Topic::buildForumMenu($viewMode); ?>

		<?php if ($isWriteable) : ?>
 		<li id="submit-top" title="Napisz nowy temat w tym dziale (skrót: n)"><a href="<?= url($page->getLocation()) . '?mode=submit'; ?>" data-shortcut="n"><span>Nowy temat</span></a></li>
		<?php endif; ?>

		<li id="search-top">
			<?= Form::open(Path::connector('forumSearch')); ?>
				<fieldset>
					<?= Form::text('q', '', array('autocomplete' => 'off', 'placeholder' => 'Szukaj na forum...')); ?><a title="Szukaj na forum" href="<?= url(Path::connector('forumSearch')); ?>" class="search-submit-button"></a>
				</fieldset>
			<?= Form::close(); ?>
		</li>
	</ul>
	<div style="clear: both;"></div>

	<?php include('_partialTopicList.php'); ?>

	<?php if (($topic) && (count($topic) > 10)) : ?>
	<ul class="f-menu-bottom">
		<li id="submit-bottom" title="Napisz nowy temat w tym dziale (skrót: n)"><a href="<?= url($page->getLocation()) . '?mode=submit'; ?>"><span>Nowy temat</span></a></li>
	</ul>
	<?php endif; ?>
</div>

<div style="overflow: hidden; margin-top: 10px">
	<a id="feed-button" href="<?= url($page->getLocation()); ?>?export=atom" title="Eksportuj do: Atom">atom</a>
	<p style="float: right;">Ilość tematów na strone <?= Form::select('page', Form::option($pageList, $perPage)); ?></p>
</div>

<div id="user-tags">
	<strong title="Wpisz intersujące Cię tagi, aby odznaczyć tematy na liście">Tagi: <span title="Kliknij, aby edytować tagi"></span></strong>
	<?php foreach ($userTags as $tag) : ?>
	<?= Form::hidden('userTags[]', $tag); ?>
	<?php endforeach; ?>

	<div>
		<?php if ($tags) : ?>
		<?php foreach ($tags as $tag => $weight) : ?>
		<?= Html::a(url($page->getLocation()) . '?tag=' . urlencode($tag), $tag); ?> × <?= $weight; ?>
		<?php endforeach; ?>
		<?php else : ?>
		<cite>(Brak tagów. Kliknij, aby dodać)</cite>
		<?php endif; ?>
	</div>

</div>

<div id="users-online">
	<div><?= count($usersOnline) + $anonymousUsersOnline; ?> użytkownik(ów) przegląda to forum (<?= $anonymousUsersOnline; ?> gości)</div>

	<p><?= implode(', ', $usersOnline); ?></p>
</div>