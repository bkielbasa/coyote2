<script type="text/javascript">
	$(function()
	{
		// suxx...
		$('#box-user-menu li a.focus').parent('li').prev('li').children('a').css('border-bottom', 'none');
		$('.box:first').css('min-height', $('#box-user-menu ul').height());
	});
</script>

<div id="box-user-menu">
	<ul>
		<?= Ucp::loadMenu($selected); ?>
	</ul>
</div>