<?= Form::open($u_confirm, array('method' => 'post')); ?>
	<fieldset id="message">
		<?= @$s_hidden_data; ?>
		
		<h1><?= $message_title; ?></h1>
		<hr />
		<p><?= $message_text; ?></p>
		<div>
			<?= Form::submit('yes', 'Tak'); ?>
			<?= Form::submit('no', 'Nie'); ?>
		</div>			
		
	</fieldset>
<?= Form::close(); ?>
