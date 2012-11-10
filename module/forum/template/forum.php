<script type="text/javascript">
<!--

	var preventAjax = false;

	var currMode = '<?= $viewMode; ?>';
	var defaultMode = '<?= $viewMode; ?>';

	var hashChange = function()
	{
		if (jQuery.inArray(window.location.hash, ['#all', '#unanswered', '#votes', '#category']) > -1)
		{
			if (('#' + currMode) != window.location.hash)
			{
				$('a[href$=' + window.location.hash + ']').trigger('click');
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
		$.posting.init({currentUrl: '<?= url($page->getLocation()); ?>'});

		$(window).hashchange(hashChange);
		$(window).hashchange();

		$('select[name=forum]').live('change', function()
		{
			window.location.href = $(this).val();
		});

		$('#search-top input[name=q][autocomplete=off]').autocomplete({url: '<?= url(Path::connector('forumSearch')); ?>'});
	});
//-->
</script>

<a style="display: none;" title="Strona główna forum" href="<?= url($page->getLocation()); ?>" data-shortcut="g+i">Strona główna forum</a>

<div id="body">
	<ul class="f-menu-top">
		<?= Topic::buildMainMenu($viewMode); ?>

		<li id="search-top" class="search-holder" style="margin-right: 0">
			<?= Form::open(Path::connector('forumSearch')); ?>
				<fieldset>
					<?= Form::text('q', '', array('autocomplete' => 'off', 'placeholder' => 'Szukaj na forum...')); ?><a title="Szukaj na forum" href="<?= url(Path::connector('forumSearch')); ?>" class="search-submit-button"></a>
				</fieldset>
			<?= Form::close(); ?>
		</li>
	</ul>
	<div style="clear: both;"></div>

	<?php if ($viewMode == 'category') : ?>
	<?php include('_partialCategory.php'); ?>
	<?php else : ?>
	<?php include('_partialTopicList.php'); ?>
	<?php endif; ?>
</div>

<div id="user-tags">
	<strong title="Wpisz intersujące Cię tagi, aby oznaczyć tematy na liście">Tagi: <span title="Kliknij, aby edytować tagi"></span></strong>
	<?php foreach ($userTags as $tag) : ?>
	<?= Form::hidden('userTags[]', $tag); ?>
	<?php endforeach; ?>

	<div>
		<?php if ($tags) : ?>
		<?php foreach ($tags as $tag => $weight) : ?>
		<?= Html::a(url($page->getLocation()) . '?view=all&tag=' . urlencode($tag), $tag); ?> × <?= $weight; ?>
		<?php endforeach; ?>
		<?php else : ?>
		<cite>(Brak tagów. Kliknij, aby dodać)</cite>
		<?php endif; ?>
	</div>

</div>

<div id="users-online">
	<div><?= count($usersOnline) + $anonymousUsersOnline; ?> użytkownik(ów) przegląda forum (<?= $anonymousUsersOnline; ?> gości)</div>

	<p><?= implode(', ', $usersOnline); ?></p>
</div>