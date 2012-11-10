<?php

$config['*'] = array(
	'title'			=> '%site.title% :: Panel administracyjny (%version%)',
	'layout'		=> 'layout',
	'stylesheet'		=> 'adm/main',
	'javascript'		=> 'jquery-1.7.1.min,hint,adm/whois'
);

$config['wikiSubmit'] = array(
	'javascript'		=> 'ajaxupload'
);

$config['blockSubmit'] = array(
	'javascript'		=> 'editarea/edit_area_full'
);

$config['pm'] = array(
	'javascript'		=> 'tinymce/tiny_mce'
);

$config['emailSubmit'] = array(
	'javascript'		=> 'tinymce/tiny_mce'
);

$config['media'] = array(
	'stylesheet'		=> 'adm/media'
);

$config['mediaSubmit'] = array(
	'javascript'		=> 'editarea/edit_area_full'
);

$config['page*'] = array(
	'stylesheet'		=> 'adm/page,adm/media',
	'javascript'		=> 'page'
);

$config['pageSubmit'] = array(
	'javascript'		=> 'ajaxupload'
);

$config['template*'] = array(
	'stylesheet'		=> 'adm/template',
	'javascript'		=> 'template'
);

$config['templateSubmit'] = array(
	'javascript'		=> 'editarea/edit_area_full'
);

$config['snippetSubmit'] = array(
	'javascript'		=> 'editarea/edit_area_full'
);
?>