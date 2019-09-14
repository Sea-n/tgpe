<?php
require('database.php');

$code = substr($_SERVER['REQUEST_URI'], 1); // $_SERVER['REQUEST_URI'] = string(5) "/path"

$code = preg_replace('#\?.*#', '', $code);

if ($code == '') { // index homepage
	include('web.php');
	exit;
}

if (!preg_match('#^[\w_-]+$#', $code))
	error(400, 'Code invalid');

$db = new MyDB();

if (!$data = $db->findByCode($code))
	error(404, 'Code not found');

header("Location: {$data['url']}");


function error(int $code, string $msg) {
	switch ($code) {
	case 400:
		header('HTTP/2 400 Bad Request');
		break;
	case 404:
		header("HTTP/2 404 Not Found");
		break;
	}

	if (strlen($_SERVER['HTTP_USER_AGENT']) < 20)
		exit($msg); // Message Only for bots

	echo <<<EOF
<html>
<head>
	<meta http-equiv="refresh" content="10;url=https://tg.pe/" />
</head>
<body>
	<center>
		<h1>$msg</h1>
		<hr>
		<p>Please check your URL again.</p>
		<p>Here is <a href="https://tg.pe/">tg.pe</a> URL Shortener, provided by <a href="https://tg.pe/dev">@SeanChannel</a></p>
	</center>
</body>
EOF;
	exit;
}
