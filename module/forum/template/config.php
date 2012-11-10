<?php

$config['forum*'] = array(
		'stylesheet'			=> '../module/forum/template/css/forum',
		'javascript'			=> '../../module/forum/template/js/common,shortcut'
);

$config['forum'] = array(
		'javascript'			=> 'hashchange'
);

$config['forumView'] = array(

		'javascript'			=> '../../module/forum/template/js/common,hashchange'
);

$config['forumSearch'] = array(

		'javascript'            => 'jquery.autocomplete'
);

$config['topicView'] = array(
		'stylesheet'			=> '../module/forum/template/css/forum',
		'javascript'			=> 'window,../../module/forum/template/js/common,../../module/forum/template/js/posting,shortcut'
);

$config['forumSubmit'] = array(
		'javascript'			=> '../../module/forum/template/js/posting,error,window,wikieditor/jquery.wikieditor.js',
		'stylesheet'			=> 'js/wikieditor/wikieditor',
		'meta' 					=> array('robots' => 'noindex,nofollow')
);

$config['topicSubmit'] = array(
		'javascript'			=> '../../module/forum/template/js/common,error,window,wikieditor/jquery.wikieditor.js,../../module/forum/template/js/posting,shortcut',
		'stylesheet'			=> '../module/forum/template/css/forum,js/wikieditor/wikieditor',
		'meta' 					=> array('robots' => 'noindex,nofollow')
);

$config['topicEdit'] = array(
		'javascript'			=> '../../module/forum/template/js/common,error,window,wikieditor/jquery.wikieditor.js,../../module/forum/template/js/posting,shortcut',
		'stylesheet'			=> '../module/forum/template/css/forum,js/wikieditor/wikieditor',
		'meta' 					=> array('robots' => 'noindex,nofollow')
);

$config['_partial*'] = array(
		'layout'				=> false
);

$config['post*'] = array(
		'stylesheet'			=> '../module/forum/template/css/forum',
		'javascript'			=> '../../module/forum/template/js/posting'
);

?>