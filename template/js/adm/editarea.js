editAreaLoader.init({
		id : 'text_content',
		syntax: 'html',
		allow_toggle: false,
		start_highlight: false,
		language: 'pl',
		allow_resize: 'both',
		toolbar: 'search, go_to_line, |, undo, redo, |, select_font, |, syntax_selection, |, change_smooth_selection, highlight, reset_highlight',
		syntax_selection_allow: 'css,html,js,php,c,cpp,java,pas,perl,python,robotstxt,ruby,sql,vb,xml'
});

function getContent()
{
	return editAreaLoader.getValue('text_content');
}