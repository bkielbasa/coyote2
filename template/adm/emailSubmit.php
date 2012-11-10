<script type="text/javascript">

	tinyMCE.init({
		language: "pl",
		mode : "none",
		theme : "advanced",
		plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager",

		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
		theme_advanced_toolbar_location : "top", 
		theme_advanced_toolbar_align : "left", 
		theme_advanced_statusbar_location : "bottom", 
		theme_advanced_resizing : true
	});

	function toggleEditor(itemId)
	{
		tinyMCE.execCommand('mceToggleEditor', false, 'mailText');
	}

	<?php if ($input->post('format', @$email_format) == Email_Model::HTML) : ?>
	tinyMCE.execCommand('mceToggleEditor', false, 'mailText');
	<?php endif; ?>


</script>
<h1>Edycja szablonu e-mail</h1>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label title="Nazwa szablonu identyfikowana przez system">Nazwa szablonu <em>*</em></label>
				<?= Form::input('name', $input->post('name', @$email_name)); ?>
				<ul><?= $filter->formatMessages('name'); ?></ul>
			</li>
			<li>
				<label title="Opis szablonu (jego użycie). Pola nie jest istotne w procesie wysyłania e-maili">Opis szablonu</label>
				<?= Form::textarea('description', $input->post('description', @$email_description), array('cols' => 60, 'rows' => 5)); ?>
			</li>
			<li>
				<label>Temat e-maila <em>*</em></label>
				<?= Form::input('subject', $input->post('subject', @$email_subject), array('size' => 80)); ?>
				<ul><?= $filter->formatMessages('subject'); ?></ul>
			</li>
			<li>
				<label title="Treść może być w formacie czystego tekstu lub HTML">Format e-maila</label>
				<?= Form::select('format', Form::option($format, $input->post('format', @$email_format)), array('onchange' => 'toggleEditor(this)')); ?>
			</li>
			<li>
				<label>Treść e-maila <em>*</em></label>
				<?= Form::textarea('text', $input->post('text', @$email_text), array('id' => 'mailText', 'cols' => 100, 'rows' => 30, 'style' => 'width: 600px')); ?>
				<ul><?= $filter->formatMessages('text'); ?></ul>
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>