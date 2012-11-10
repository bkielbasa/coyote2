
<?= Form::open($u_confirm, array('method' => 'post')); ?>
<?php 
			if ($s_hidden_data) :
				echo $s_hidden_data;
			endif;
			?>		
	<fieldset>
		<legend><?= $message_title; ?></legend>
		
		<div style="padding: 10px;">
			<?= $message_text; ?>

			<p><?= Form::submit('yes', 'Tak'); ?> <?= Form::submit('no', 'Nie', array('class' => 'delete-button')); ?></p>
		</div>	
		
	</fieldset>
</form>