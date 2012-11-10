<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title><?php echo $error ?></title>

<style type="text/css">
body
{
	margin:			0;
	font-family:	Verdana;
	background-color:		#f6f3f0;
	font-size:		12px;
}

h1
{
	font-size:		1.4em;
}

#wrap
{
	width:			60%;
	padding:		10px;
	margin:			40px auto 0 auto;
	background-color:		white;
	border:			1px solid #e5e2de;
	border-bottom:	2px solid #e5e2de;
}

#bottom
{
	width:			62%;
	margin:			8px auto 0 auto;
	font-family:	Arial;
	color:			black;
}

#wrap hr
{
	border-top:		1px solid #e5e2de;
	line-height:	1px;
	height:			1px;
}

#detail
{
	background:		#dfdfdf;
	border:			1px solid #999;
	padding:		5px;
}

</style>
</head>

<body>
	<div id="wrap">
		<h1>Page not found</h1>
		<hr>
		<p><?= $description; ?></p>
		<div id="detail">Request file does not exists: <code><?= $this->getMessage(); ?></code></div>
	</div>
	<div id="bottom">
		Copyright &copy; 2003-2009 Coyote Group
	</div>
</body>
</html>