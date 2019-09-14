<?php
require('config.php');
require('database.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header("HTTP/2 405 Method Not Allowed");
	exit;
}

if (!array_key_exists($_POST['token'] ?? 'guest', HTTP_API_TOKENS)) {
	header("HTTP/2 401 Unauthorized");
	exit;
}

if (!isset($_POST['code']))
	exit('No code');

if (!isset($_POST['url']))
	exit('No url');

$db = new MyDB();
$error = $db->insert($code, $url, 777000);

if ($error[0] === '00000')
	echo json_encode([
		'ok' => true,
		'shortLink' => $code
	], JSON_PRETTY_PRINT);
else
	echo json_encode([
		'ok' => false,
		'errorCode1' => $error[0],
		'errorCode2' => $error[1],
		'errorInfo' => $error[2],
	], JSON_PRETTY_PRINT);
