<script type="text/javascript">
<!--
	var error = new Array();

	<?php if ($form->hasErrors()) : ?>
	<?php foreach ($form->getErrors() as $field => $errors) : ?>
	<?php if ($field !== $salt) : ?>
	error['<?= $field; ?>'] = '<?= addslashes($errors[0]); ?>';
	<?php endif; ?>
	<?php endforeach; ?>
	<?php endif; ?>

	$(document).ready(function()
	{
		for (field in error)
		{
			$('input[name=' + field + ']').setError(error[field]);
		}		

		$('input[name=name]').focus();
	}
	);
//-->
</script>

<?php echo $page->getContent(); ?>

<?php if ($form->hasErrors($salt)) : ?>
<p class="note">System wykrył, iż Twój klucz do logowania jest nieaktualny. Prosimy o ponowne wejście na stronę.<p>
<?php elseif ($form->hasErrors()) : ?>
<p class="error">Logowanie zostało przeprowadzone nieprawidłowo. Być może chcesz skorzystać z opcji <a href="<?= url('@user?controller=Password'); ?>">przypominania hasła</a>?</p>
<?php endif; ?>

<?= $form; ?>