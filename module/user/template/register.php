<script type="text/javascript">
<!--
	var error = new Array();

	<?php if ($form->hasErrors()) : ?>
	<?php foreach ($form->getErrors() as $field => $errors) : ?>
	<?php if ($field !== 'hash') : ?>
	error['<?= $field; ?>'] = '<?= addslashes($errors[0]); ?>';
	<?php endif; ?>
	<?php endforeach; ?>
	<?php endif; ?>

	$(document).ready(function()
	{
		$(':input[name=random]').change(function()
		{
			if ($(this).is(':checked'))
			{
				$(':input[name=password], :input[name=password_c]').parent('li').hide();
			}
			else
			{
				$(':input[name=password], :input[name=password_c]').parent('li').show();
			}
		});

		$(':input[name=random]').change();

		for (field in error)
		{
			$('input[name=' + field + ']').setError(error[field]);
		}

		<?php if (!$hasAccount) : ?>
		$('input[type=text], input[type=password]').bind('focus blur', function()
		{
			$(this).toggleClass('set-focus');

			if ($(this).attr('title') && !$('.box-error').is(':visible'))
			{
				$('.box-hint-content').text($(this).attr('title'));

				$('.box-hint').toggle();
				var p = $(this).position();

				$('.box-hint').css('top', p.top);
				$('.box-hint').css('left', p.left + $(this).outerWidth() + 30);
			}
		});
		<?php endif; ?>

		$('input[name=name]').focus();
		$('#spambot').attr('value', "<?= $input->post->token(@$token); ?>");
		$('#spambot').parent('li').hide();

		$('form a').bind('click', function()
		{
			$('#userName').val($(this).text());
		});

		$('form[name=register-form]').submit(function()
		{
			$(':submit').attr('disabled', 'disabled');
		});

	});
//-->
</script>

<style type="text/css">
.set-focus
{
		border:			1px solid #abd043;
}
</style>

<?= $page->getContent(); ?>

<?php if (isset($account)) : ?>
<p class="error">System wykrył, iż utworzyłeś już konto o loginie <strong><?= $account['user_name']; ?></strong></p>
<?php endif; ?>

<div class="box">
	<div class="box-hint" style="display: none; position: absolute;">
		<div class="box-hint-top"><div></div></div>

		<div class="box-hint-content">

		</div>

		<div class="box-hint-bottom"><div></div></div>
	</div>

	<?= $form; ?>
</div>