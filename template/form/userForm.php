<?php foreach ($form->getElements() as $element) : ?>
<li>
	<label <?= $element->getDescription() ? 'title="' . $element->getDescription() . '"' : ''; ?>>
		<?= $element->getLabel(); ?> <?= $element->isRequired() ? '<em>*</em>' : ''; ?>
	</label>

	<?= $element->display(); ?>
	<?php if ($element->getConfig('hint')) : ?>
	<br /><small><?= $element->getConfig('hint'); ?></small>
	<?php endif; ?>
	<?php if ($element->getMessages() && $form->isMessagesEnabled()) : ?>
	<ul>
		<?php foreach ($element->getMessages() as $message) : ?>
		<li><?= $message; ?></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
</li>
<?php endforeach; ?>
