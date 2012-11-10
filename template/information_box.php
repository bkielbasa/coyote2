<fieldset id="message">
	<h1><?= $message_title; ?></h1>
	<hr />
	<p><?= $message_text; ?></p>
	<div>
		<?= Form::input('ok', 'OK', array('type' => 'button', 'onclick' => 'window.location.href = \'' . ($u_referer ? $u_referer : Url::base()) . '\'')); ?>
	</div>
</fieldset>