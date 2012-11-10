<script type="text/javascript">
<!--
	var error = new Array();

	<?php if ($form->hasErrors()) : ?>
	<?php foreach ($form->getErrors() as $field => $messages) : ?>
	<?php if ($field != 'hash') : ?>
	error['<?= $field; ?>'] = '<?= addslashes($messages[0]); ?>';
	<?php endif; ?>
	<?php endforeach; ?>
	<?php endif; ?>

	$(document).ready(function()
	{
		for (field in error)
		{
			$(':input[name=' + field + ']').setError(error[field]);
		}		

		$('input[name=name]').focus();
	}
	);
//-->
</script>

<?php if (isset($session->error)) : ?>
<p class="error"><?= $session->getAndDelete('error'); ?></p>
<?php elseif (isset($session->message)) : ?>
<p class="message"><?= $session->getAndDelete('message'); ?></p>
<?php else : ?>
<p class="note">Aby skorzystać z opcji przypominania hasła, prosimy wpisać swój login oraz e-mail podany w panelu użytkownika.<br />Jeżeli nie podałeś adresu e-mail w panelu użytkownika, nie możesz skorzystać z opcji przypominania hasła</p>
<?php endif; ?>

<div class="box-header">
	Formularz przypominania hasła
</div>

<div class="box" style="margin-top: 0">
	<?= $form; ?>
</div>