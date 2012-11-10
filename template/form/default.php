<?= Form::open($form->getAction(), $form->getAttributes()); ?>
<fieldset>
	<ol>
		<?php foreach ($form->getElements() as $element) : ?>
		<li>
			<label><?= $element->getLabel(); ?> <?= $element->getConfig('require') ? '<em>*</em>' : ''; ?></label>
			<?= $element->display(); ?>

			<?php if ($element->getMessages() && $form->isMessagesEnabled()) : ?>
			<ul>
				<?php foreach ($element->getMessages() as $message) : ?>
				<li><?= $message; ?></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ol>
</fieldset>
<?= Form::close(); ?>