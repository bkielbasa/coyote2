<script type="text/javascript">

	$(document).ready(function()
	{
		$('.digg, .bury').bind('click', function()
		{
			bookmarkId = $(this).parent().parent().parent('div.bookmark').attr('id').replace('bookmark-', '');
			
			if ($(this).attr('class') == 'digg')
			{		
				var currRank = $(this).next();
	
				$.post('<?= url(Path::connector('bookmark')); ?>', {id: bookmarkId, value: 'digg'}, 
					function(data)
					{
						$(currRank).text(data);											
					}
				);
			}
			else
			{
				var currRank = $(this).prev();

				$.post('<?= url(Path::connector('bookmark')); ?>', {id: bookmarkId, value: 'bury'}, 
					function(data)
					{
						$(currRank).text(data);
					}
				);
			}
		}
		);
	}
	);

</script>

<?php foreach ($bookmark as $row) : ?>
<div class="bookmark" id="bookmark-<?= $row['bookmark_id']; ?>">
	<h2><a href="<?= $row['bookmark_url']; ?>"><?= $row['page_subject']; ?></a></h2>
	<?php if ($row['rank_time']) : ?>
	<span>Ostatnio polecane <strong><?= User::date($row['rank_time']); ?></strong></span>
	<?php endif; ?>

	<?php if ($module->isPluginEnabled('comment')) : ?>
	<a href="<?= url($row['location_text']); ?>#box-comment" class="comment"><?= $row['bookmark_comment']; ?> komentarzy</a>
	<?php endif; ?>

	<div>
		<div>
			<a title="Poleć innym ten link (kliknij ponownie, aby cofnąć)" class="digg"></a>

			<span title="Ilość punktów w rankingu polecanych stron"><?= $row['bookmark_rank']; ?></span>

			<a title="Ta strona nie jest godna polecenia (kliknij ponownie, aby cofnąć)" class="bury"></a>
		</div>

		<p>
			<a href="<?= url($row['location_text']); ?>"><?= Text::limit(Text::plain($row['text_content']), 455); ?></a>
		</p>
		
		<small>Host: <a href="<?= url(Path::connector('bookmark')); ?>?host=<?= $row['bookmark_host']; ?>"><?= $row['bookmark_host']; ?></a></small>
		<a class="add" href="<?= url(Path::connector('bookmark') . '/Add?url=' . $row['bookmark_url']); ?>">Dodaj do swojej listy zakładek</a>
		
	</div>
</div>
<?php endforeach; ?>