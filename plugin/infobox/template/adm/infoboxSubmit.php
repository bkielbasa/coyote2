<script type="text/javascript">

	tinyMCE.init({
		language: "pl",
		mode : "none",
		theme : "advanced",
		elements : "ajaxfilemanager",
		file_browser_callback : "ajaxfilemanager",
		plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true
	});

	function ajaxfilemanager(field_name, url, type, win)
	{
		var ajaxfilemanagerurl = "<?= url('template/js/tinymce/plugins/ajaxfilemanager/ajaxfilemanager.php'); ?>";

		var view = 'detail';
		switch (type)
		{
			case "image":
				view = 'thumbnail';
				break;
			case "media":
				break;
			case "flash":
				break;
			case "file":
				break;
			default:
				return false;
		}

		tinyMCE.activeEditor.windowManager.open(
		{
			url: "<?= url('template/js/tinymce/plugins/ajaxfilemanager/ajaxfilemanager.php?view='); ?>" + view,
			width: 782,
			height: 440,
			inline : "yes",
			close_previous : "no"
		},
		{
			window : win,
			input : field_name
		});
	}

	tinyMCE.execCommand('mceToggleEditor', false, 'infobox_content');

</script>

<h1>Edycja komunikatu</h1>

<?= Form::open('', array('method' => 'post')); ?>
	<fieldset>
		<ol>
			<li>
				<label>Tytuł komunikatu <em>*</em></label>
				<?= Form::input('title', $input->post('title', @$infobox_title)); ?>
				<ul><?= $filter->formatErrors('title'); ?></ul>
			</li>
			<li>
				<label>Komunikat aktywny</label>
				<?= Form::checkbox('enable', 1, $input->post('enable', @$infobox_enable)); ?>
			</li>
			<li>
				<label>Data aktywności</label>
				<?= Form::input('lifetime', $input->post('lifetime', @$infobox_lifetime), array('style' => 'width: 50px')); ?> dni
			</li>
			<li>
				<label>Priorytet</label>
				<?= Form::select('priority', Form::option(@$priority, $input->post('priority', @$infobox_priority))); ?>
			</li>
			<li>
				<label>Treść <em>*</em></label>
				<?= Form::textarea('content', $input->post('content', @$infobox_content), array('cols' => 85, 'rows' => 20, 'id' => 'infobox_content')); ?>
				<ul><?= $filter->formatErrors('content'); ?></ul>
			</li>
			<li>
				<label></label>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
			</li>
		</ol>
	</fieldset>
<?= Form::close(); ?>