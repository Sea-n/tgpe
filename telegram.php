<?php
/* This file hard-linked from sean.taipei/telegram/tgpe.php to tg.pe/telegram.php */

require('/usr/share/nginx/tg.pe/database.php');
$db = new MyDB();

/* Command-line Execuate */
switch ($argv[1] ?? '') {
case 'build':
	$sql = 'CREATE TABLE main (' .
		'url TEXT NOT NULL, ' .
		'code TEXT NOT NULL UNIQUE, ' .
		'author INTEGER NOT NULL, ' .
		'created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ' .
		'modified_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL)';
	$stmt = $db->pdo->prepare($sql);
	$stmt->execute();
	exit;
case 'dump':
	$sql = "SELECT * FROM main";
	$stmt = $db->pdo->prepare($sql);
	$stmt->execute();
	while ($data = $stmt->fetch())
		printf("%-5s %-30s %10s  %s\n", $data['code'], $data['url'], $data['author'], $data['created_at']);
	exit;
}
if (!isset($TG))
	exit;


/* Message Texts */
$msg_help = <<<EOF
Hello, just send me URL.

You can also specific your short code (>= 3 char)

For instance:
<pre>https://t.me/tgpebot bot</pre>
EOF;


/* Allow Text in both message and photo caption */
$text = $TG->data['message']['text'] ?? $TG->data['message']['photo']['caption'] ?? '';

if (empty($text)) {
	if ($TG->ChatID > 0) # Private Message
		$TG->sendMsg([
			'text' => $msg_help
		]);
	exit;
}


/* Handle commands */
if (preg_match('#^[/!](?<cmd>\w+)(?:@' . $TG->botName . ')?(?:\s+(?<args>.+))?$#', $text, $matches)) {
	$cmd = strtolower($matches['cmd']);
	$args = $matches['args'] ?? '';
	switch ($cmd) {
	case 'start':
	case 'help':
	default:
		$TG->sendMsg([
			'parse_mode' => 'HTML',
			'text' => $msg_help
		]);
		break;
	}

	exit;
}


if (strpos($text, '.') !== false // Looks like URL
	&& substr($text, 0, 4) !== 'http') // Not start with HTTP or HTTPS
	$text = "https://$text"; // Prepend HTTPS scheme

/* Vaildate URL */
if (!preg_match('#^(?P<url>https?://[^\n\s@%]+\.[^\n\s@%]+(?:/[^\n\s]*)?)(?:[\n\s]+(?P<code>[a-zA-Z0-9]+))?$#', $text, $matches)) {
	if ($TG->ChatID > 0) # Private Message
		$TG->sendMsg([
			'text' => $msg_help
		]);
	exit;
}
$url = $matches['url'];
$code = $matches['code'] ?? '';
$author = $TG->FromID;

if (!filter_var($url, FILTER_VALIDATE_URL)) {
	$TG->sendMsg([
		'text' => 'Please Send a Vaild URL.'
	]);
	exit;
}


if (strlen($code) >= 3) { /* Check Code Existance */
	if ($data = $db->findByCode($code)) {
		$TG->sendMsg([
			'text' => "Already Exist: https://tg.pe/$code\n\n" .
			"Original URL: {$data['url']}"
		]);
		exit;
	}
} else if (strlen($code) === 0) { /* Allocate 3-char not-exists code */
	if ($code = $db->findCodeByUrl($url)) {
		$TG->sendMsg([
			'text' => "Success!\n\nhttps://tg.pe/$code"
		]);
		exit;
	} else
		$code = $db->allocateCode($url);
} else { /* 1 or 2 char only allow admins */
	if (!in_array($TG->FromID, [
		218892893, # Jerry
		109780439  # Sean
	])) {
		$TG->sendMsg([
			'text' => "ERROR: Code should be at least 3 chars"
		]);
		exit;
	}
}

if (strpos($url, "fbclid=")) {
	$TG->sendMsg([
		'parse_mode' => 'HTML',
		'text' => 'Hey! Please remove <b>fbclid</b> before sharing URLs.'
	]);
	exit;
}
/* Both $url and $code should be clean */


/* Create Record */
$sql = "INSERT INTO main(url, code, author) VALUES (:url, :code, :author)";
$stmt = $db->pdo->prepare($sql);
$r = $stmt->execute([
	':url' => $url,
	':code' => $code,
	':author' => $author
]);

if ($stmt->errorCode() === '00000')
	$TG->sendMsg([
		'text' => "Success!\n\nhttps://tg.pe/$code"
	]);
else
	$TG->sendMsg([
		'text' => "ERROR: Something went Wrong, please contact @S_ean\n\n" .
		"URL: $url\n" .
		"Code: $code\n" .
		"Author: $author\n\n" .
		"PDO Error Info:\n" .
		json_encode($stmt->errorInfo(), JSON_PRETTY_PRINT)
	]);
