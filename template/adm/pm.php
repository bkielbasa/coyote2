<script type="text/javascript">

tinyMCE.init({
	language: "pl",
	mode : "textareas",
	theme : "advanced",

	theme_advanced_buttons1 : "bold,italic,underline|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontsizeselect",
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,outdent,indent,blockquote,image,cleanup,code,|preview|sub,sup,|charmap,advhr,fullscreen",
	theme_advanced_toolbar_location : "top", 
	theme_advanced_toolbar_align : "left", 
	theme_advanced_statusbar_location : "bottom", 
	theme_advanced_resizing : false
});

</script>

<h1>Napisz wiadomość</h1>

<p>Na tej stronie możesz wysłać wiadomość do danych grup użytkowników.</p>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<legend>Tworzenie wiadomości prywatnej</legend>

		<ol>
			<li>
				<label>Wyślij do grup:</label>
				<fieldset>
				<?php foreach ($group as $groupId => $groupName) : ?>
					<ol>
						<li><?= Form::checkbox('group[]', $groupId, (bool)in_array($groupId, (array)$input->post->group)); ?> <?= $groupName; ?></li>
					</ol>
				<?php endforeach; ?>
				</fieldset>
			</li>

			<li>
				<label>Temat <em>*</em></label>
				<?= Form::input('subject', $input->post('subject'), array('style' => 'width: 400px')); ?>
				<ul><?= $filter->formatMessages('subject'); ?></ul>
			</li>
			<li>
				<?= Form::textarea('message', $input->post('message'), array('cols' => 110, 'rows' => 20, 'style' => 'width: 98%')); ?>
				<ul><?= $filter->formatMessages('message'); ?></ul>
			</li>
			<li>
				<?= Form::submit('', 'Wyślij wiadomość'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>