<?php

$config['*'] = array(

	'layout'	=> '../layout',
	'meta'		=> array(
		'description'			=> '%site.description%',
		'keywords'				=> '%site.keywords%'
	),
	'httpmeta'	=> array(
		'Content-Language'		=> 'pl',
		'Content-Type'			=> 'application/xhtml+xml; charset=utf-8'
	),
	'stylesheet'	=> 'css/main,../module/user/template/css/user',
	'javascript'	=> 'jquery-1.7.1.min,profile'
);

$config['confirm'] = array(

	'title'			=> 'Weryfikacja adresu e-mail :: %site.title%'
);

$config['pm*'] = array(

	'title'			=> 'Wiadomości prywatne :: %site.title%',
	'javascript'	=> 'window,wikieditor/jquery.wikieditor,jquery.autocomplete,../../module/user/template/js/pm'
);

$config['pmSubmit'] = array(

	'title'			=> 'Napisz nową wiadomość prywatną :: %site.title%'
);

$config['index'] = array(

	'title'			=> 'Panel użytkownika :: %site.title%',
	'javascript'	=> 'error'
);

$config['notify'] = array(

	'title'			=> 'Powiadomienia :: %site.title%',
	'javascript'	=> 'hashchange'
);

$config['profile'] = array(

	'title'			=> 'Profil użytkownika %user.name% :: %site.title%'
);

$config['report'] = array(

	'title'			=> 'Raportuj stronę :: %site.title%',
	'meta'			=> array('robots' => 'noindex,nofollow')
);

$config['password*'] = array(

	'title'			=> 'Przypominanie hasła :: %site.title%',
	'javascript'	=> 'error'
);

$config['psw'] = array(

	'title'			=> 'Formularz zmiany hasła :: %site.title%'
);

$config['security'] = array(

	'title'			=> 'Ustawienia bezpieczeństwa :: %site.title%'
);

$config['visit'] = array(

	'title'			=> 'Ostatnie wizyty :: %site.title%'
);

$config['info'] = array(

	'title'			=> 'Informacje o profilu :: %site.title%'
);

$config['watch'] = array(

	'title'			=> 'Lista obserwowanych :: %site.title%',
	'javascript'	=> 'window'
);
?>