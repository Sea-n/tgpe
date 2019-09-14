<?php
/* This file hard-linked from sean.taipei/telegram/tgpe.php to tg.pe/telegram.php */
if (!isset($TG))
	exit;

require('/usr/share/nginx/tg.pe/config.php');
require('/usr/share/nginx/tg.pe/database.php');
$db = new MyDB();


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
	case 'my':
		$data = $db->findByAuthor("TG{$TG->FromID}");
		if (count($data) == 0) {
			$TG->sendMsg([
				'parse_mode' => 'HTML',
				'text' => $msg_help
			]);
			break;
		}

		$text = "You have <b>" . count($data) . "</b> shorten URLs.\n";
		for ($i=0; $i<count($data) && strlen($text)<1000; $i++) {
			if (mb_strlen($data[$i]['url']) > 40)
				$url = mb_substr($data[$i]['url'], 0, 30) . '...';
			else
				$url = $data[$i]['url'];
			$url = $TG->enHTML($url);

			if (!($i%5))
				$text .= "\n";
			$text .= ($i+1) . ". https://tg.pe/{$data[$i]['code']}\n";
			$text .= "<code>$url</code>\n\n";
		}
		$TG->sendMsg([
			'text' => $text,
			'parse_mode' => 'HTML'
		]);
		break;
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
$code = $matches['code'] ?? '';
$url = $matches['url'];
$author = "TG{$TG->FromID}";

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
	if (!in_array($TG->FromID, TG_ADMINS)) {
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
$error = $db->insert($code, $url, $author);

if ($error[0] === '00000')
	$TG->sendMsg([
		'text' => "Success!\n\nhttps://tg.pe/$code"
	]);
else
	$TG->sendMsg([
		'text' => "ERROR: Something went Wrong, please contact @S_ean\n\n" .
		"Code: $code\n" .
		"URL: $url\n" .
		"Author: $author\n\n" .
		"PDO Error Info:\n" .
		json_encode($error, JSON_PRETTY_PRINT)
	]);
