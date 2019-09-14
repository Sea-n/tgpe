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

$author = HTTP_API_TOKENS[ $_POST['token'] ?? 'guest' ];


$db = new MyDB();

if (!isset($_POST['url']))
	exit('No url');
$url = $_POST['url'];


if (isset($_POST['code'])) {
	$code = $_POST['code'];
	if ($data = $db->findByCode($code)) {
		echo json_encode([
			'ok' => false,
			'longUrl' => $data['url'],
			'message' => 'Code already exists.'
		]);
		exit;
	}
} else if ($data = $db->findCodeByUrl($url)) {
	echo json_encode([
		'ok' => true,
		'shortLink' => $data,
		'message' => 'Link already exists.'
	], JSON_PRETTY_PRINT);
	exit;
} else if (isset($_POST['prefix']))
	$code = $db->allocateCode($_POST['prefix'], $_POST['len'] ?? 3);
else
	$code = $db->allocateCode();

$error = $db->insert($code, $url, $author);

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
		'message' => $error[2],
	], JSON_PRETTY_PRINT);
