
<fieldset>
	<legend><?= $message_title; ?></legend>
	
	<div style="padding: 10px;">
		<?= $message_text; ?>

		<p><?= Form::submit('ok', 'OK', array('class' => 'button', 'onclick' => 'window.location.href = \'' . $u_referer . '\'')); ?></p>
	</div>	
	
</fieldset>
