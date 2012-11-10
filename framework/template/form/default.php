<?= Form::open($form->getAction(), $form->getAttributes()); ?>
<fieldset>
	<dl>
		<?php foreach ($form->getElements() as $element) : ?>
		<dt><?= $element->getLabel(); ?></dt>
		<dd>
			<?= $element->display(); ?>
			<?php if ($element->getMessages() && $form->isMessagesEnabled()) : ?>
			<ul>
				<?php foreach ($element->getMessages() as $message) : ?>
				<li><?= $message; ?></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</dd>
		<?php endforeach; ?>
	</dl>
</fieldset>
<?= Form::close(); ?>