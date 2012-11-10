<script type="text/javascript">
<!--

	$(document).ready(function()
	{
		$('.box-menu li a').not('[href]').bind('click', function()
		{
			index = $('.box-menu li a').index(this);
			$('.box-menu li').removeClass('focus');

			$(this).parent('li').addClass('focus');
			$('fieldset').toggle();
		});

		$('.delete').bind('click', function()
		{
			element = this;
			id = $(this).attr('href').replace('#bookmark-', '');
			
			$.confirm(
			{
				windowTitle: 'Usuwanie zakładki',
				windowMessage: 'Czy chcesz usunąć tę zakładkę?',
				onYesClick: function()			
				{
					$.post('<?= url('@user?controller=Bookmark'); ?>',
					{
						'delete': id
					},
					function(data)
					{
						$(element).parent().parent().remove();
						$.closeWindow();						
					});
				}
			});
		});
	});
//-->
</script>

<?php if (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php endif; ?>

<div class="box" style="margin-top: 40px">
	<ul class="box-menu">
		<li><a href="<?= url('@user'); ?>">Konfiguracja profilu</a></li>
		<li><a href="<?= url('@user'); ?>#info">Informacje o profilu</a></li>
		<li><a href="<?= url('@user?controller=Pm'); ?>">Wiadomości prywatne</a></li>

		<?php include('module/user/template/ucp/_partialUserMenu.php'); ?>
	</ul>

	<?php if ($pagination->getTotalPages() > 1) : ?>
	<div class="pagination">Strona <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></div>
	<?php endif; ?>

	<?php if ($bookmark) :?>
	<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
		
	<?php foreach ($bookmark as $row) : ?>
	<div class="bookmark">

		<div style="overflow: hidden;">
			<h2><a href="<?= $row['bookmark_url']; ?>"><?= $row['page_subject']; ?></a></h2>
			<a title="Usuń zakładkę" href="#bookmark-<?= $row['bookmark_id']; ?>" class="delete"></a>
		</div>

		<div>
			<p>
				<a href="<?= url($row['location_text']); ?>"><?= $row['bookmark_description']; ?></a>
			</p>			
		</div>
	</div>
	<?php endforeach; ?>

	<?= Form::close(); ?>
	<?php else : ?>

	<p style="text-align: center;">Brak zakładek.</p>
	<?php endif; ?>

	<?php if ($pagination->getTotalPages() > 1) : ?>
	<div class="pagination">Strona <?= $pagination; ?> z <?= $pagination->getTotalPages(); ?></div>
	<?php endif; ?>
</div>