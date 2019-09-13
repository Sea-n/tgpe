<?php
$code = substr($_SERVER['REQUEST_URI'], 1); // $_SERVER['REQUEST_URI'] = string(5) "/path"

if ($code == '') { // index homepage
	include('home.html');
	exit;
}

if (!preg_match('#^[\w_-]+$#', $code))
	exit('ERROR: Code Invalid');


$db = new PDO('sqlite:/usr/share/nginx/tg.pe/sqlite.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT * FROM main WHERE code = :code";
$stmt = $db->prepare($sql);
$stmt->execute([
	':code' => $code
]);

if (!$data = $stmt->fetch())
	exit('ERROR: Code not found');

header("Location: {$data['url']}");
