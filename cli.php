<?php
/* Only Command-line Execution Allowed */
if (!isset($argv[1]))
	exit;

require('/usr/share/nginx/tg.pe/database.php');
$db = new MyDB();


switch ($argv[1]) {
case 'build':
	$sql = 'CREATE TABLE main (' .
		'code TEXT NOT NULL UNIQUE, ' .
		'url TEXT NOT NULL, ' .
		'author TEXT NOT NULL, ' .
		'created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ' .
		'modified_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL)';
	$stmt = $db->pdo->prepare($sql);
	$stmt->execute();
	break;

case 'dump':
	$sql = "SELECT * FROM main";
	$stmt = $db->pdo->prepare($sql);
	$stmt->execute();
	while ($data = $stmt->fetch())
		printf("%-5s %-30s %10s  %s\n", $data['code'], $data['url'], $data['author'], $data['created_at']);
	break;

default:
	echo "Unknown argument: {$argv[1]}";
}
