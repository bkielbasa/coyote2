<script type="text/javascript">
<!--
	editAreaLoader.init({
		id : 'mediaContent',
		syntax: '<?= in_array($mediaSuffix, array('php', 'js', 'css', 'html')) ? $mediaSuffix : 'php'; ?>',
		start_highlight: true,
		language: 'pl',
		allow_resize: 'both',
		toolbar: 'search, go_to_line, |, undo, redo, |, select_font, |, syntax_selection, |, change_smooth_selection, highlight, reset_highlight',
		syntax_selection_allow: 'css,html,js,php'
	});
//-->
</script>

<h1>Edycja pliku <?= implode('/', $anchor); ?></h1>

<?php if (!is_writeable($path)) : ?>
<p class="note">UWAGA! Plik nie ma praw do zapisu! Nie można zapisać pliku.</p>
<?php endif; ?>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label>Nazwa pliku:</label>
				<?= Form::input('name', $input->post('name', basename($path)), array('size' => 50)); ?>
			</li>
			<li>
				<?= Form::textarea('content', $input->post('content', htmlspecialchars($content)), array('id' => 'mediaContent', 'cols' => 160, 'rows' => 30, 'style' => 'width: 98%')); ?>
			</li>
			<li>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
				<?= Form::button('', 'Anuluj', array('class' => 'cancel-button', 'onclick' => 'history.go(-1)')); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>