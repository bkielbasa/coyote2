<?php if ($filter->getMessages()) : ?>
<p class="error"><?= $filter->formatMessages('content', 'b'); ?></p>
<?php endif; ?>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<?= Form::textarea('content', $input->post->content($comment_content), array('cols' => 110, 'rows' => 10)); ?>
			</li>
			<li>
				<?= Form::submit('', 'Zapisz komentarz'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>