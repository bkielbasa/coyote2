<?php

$config['*'] = array(
	'title'		=> '%site.title%',
	'layout'	=> 'layout',
	'meta'		=> array(
		'description'			=> '%site.description%',
		'keywords'				=> '%site.keywords%'
	),
	'stylesheet'	=> array('css/main', 'css/page'),
	'javascript'	=> 'jquery-1.7.1.min,profile'
);

$config['homepage'] = array(

	'stylesheet'	=> 'css/homepage'
);

$config['index'] = array(

	'stylesheet'	=> 'css/homepage'
);

$config['foo'] = array('layout' => false, 'stylesheet' => array('-css/main', 'css/main-new'));

?>