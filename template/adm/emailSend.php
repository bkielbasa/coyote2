<h1>Wyślij e-mail</h1>

<fieldset style="background: #f8f8f8; padding: 10px">
	<legend><?= $email_subject; ?></legend>

	<?php if ($email_format == Email_Model::PLAIN) : ?>
	<?= nl2br($email_text); ?>
	<?php else : ?>
	<?= $email_text; ?>
	<?php endif; ?>
</fieldset>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label>Adres e-mail <em>*</em></label>
				<?= Form::input('email', $input->post('email')); ?>
				<ul><?= $filter->formatMessages('email'); ?></ul>
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Wyślij'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>