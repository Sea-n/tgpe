<?php
/* This file hard-linked from sean.taipei/telegram/tgpe.php to tg.pe/telegram.php */
if (!isset($TG))
	exit;

require_once('/usr/share/nginx/tg.pe/config.php');
require_once('/usr/share/nginx/tg.pe/database.php');
$db = new MyDB();


/* Message Texts */
$msg_help = <<<EOF
Hello, just send me URL.

You can also specific your short code.

<b>Usage</b>
For instance, send me following text:
<pre>https://t.me/tgpebot bot</pre>

Note: You can use <b>a-z</b>, <b>A-Z</b> and <b>0-9</b>.
Minimum length is <b>3</b> characters.


<b>Commands</b>
/my - Show all your links.
/help - Show this message.

<b>About</b>
Developer: @SeanChannel
Source Code: tg.pe/repo
EOF;

if (strpos($TG->data['message']['from']['language_code'], 'zh') !== false)
	$msg_help = <<<EOF
安安，請直接傳網址給我

<b>【使用方式】</b>
如果要自訂短網址，可參考以下範例：
<pre>https://t.me/tgpebot bot</pre>

注意：目前僅接受英數字（A-Z, a-z, 0-9）組合、最短 3 個字

<b>【指令列表】</b>
/my - 顯示您建立的連結清單
/help - 顯示此訊息

<b>【關於】</b>
開發者： @SeanChannel
原始碼： tg.pe/repo
EOF;


/* Allow Text in both message and photo caption */
$text = $TG->data['message']['text'] ?? $TG->data['message']['photo']['caption'] ?? '';

if (empty($text)) {
	if ($TG->ChatID > 0) # Private Message
		$TG->sendMsg([
			'parse_mode' => 'HTML',
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
		$author = "TG{$TG->FromID}";
		$data = $db->findByAuthor($author);
		if (count($data) == 0) {
			$TG->sendMsg([
				'parse_mode' => 'HTML',
				'text' => $msg_help
			]);
			break;
		}

		$text = "You have <b>" . count($data) . "</b> shorten URLs.\n";
		for ($i=0; $i<count($data) && strlen($text)<4000; $i++) {
			if (mb_strlen($data[$i]['url']) > 40)
				$url = mb_substr($data[$i]['url'], 0, 25) . '...' . mb_substr($data[$i]['url'], -5);
			else
				$url = $data[$i]['url'];
			$url = $TG->enHTML($url);

			if (!($i%5))
				$text .= "\n";
			$text .= ($i+1) . ". tg.pe/{$data[$i]['code']}  ";
			$text .= "(<code>$url</code>)\n";
		}
		$TG->sendMsg([
			'text' => $text,
			'parse_mode' => 'HTML',
			'disable_web_page_preview' => true
		]);
		break;

	case 'b':
	case 'ban':
	case 'ban_user':
		/* Admin only */
		if (!in_array($TG->FromID, TG_ADMINS)) {
			$TG->sendMsg([
				'text' => "Permission denied."
			]);
			break;
		}

		/* Find user ID, args -> replied tag */
		$uid = $args;
		if (!preg_match('#^TG\d{8,10}$#', $uid)) {
			if (preg_match('/#(TG\d{8,10})\D/', $TG->data['message']['reply_to_message']['text'], $matches)) {
				$uid = $matches[1];
			} else {
				$TG->sendMsg([
					'text' => "Format error."
				]);
				break;
			}
		}

		/* Check status and ban */
		$check = $db->isUserBanned($uid);
		if ($check != false) {
			$TG->sendMsg([
				'parse_mode' => 'HTML',
				'text' => "User #$uid already banned at <code>$check</code>."
			]);
			break;
		}

		$cnt = $db->banUser($uid);
		$TG->sendMsg([
			'text' => "Banned #$uid.\nRemoved $cnt links."
		]);
		break;

	case 'unban_user':
		if (!in_array($TG->FromID, TG_ADMINS)) {
			$TG->sendMsg([
				'text' => "Permission denied."
			]);
			break;
		}
		$uid = $args;
		if (preg_match('#^TG\d{8,10}$#', $uid)) {
			$check = $db->isUserBanned($uid);
			if ($check == false) {
				$TG->sendMsg([
					'text' => "User #$uid not banned."
				]);
				break;
			}

			$status = $db->getUserStatus($uid);
			$db->unbanUser($uid);

			$TG->sendMsg([
				'parse_mode' => 'HTML',
				'text' => "User #$uid now unbanned.\n" .
						"Was banned at <code>$check</code>\n\n" .
						"Link count: {$status['not_deleted_count']} active / {$status['deleted_count']} removed\n" .
						"First link created at: <code>{$status['earliest_date']}</code>\n" .
						"Last link created at: <code>{$status['latest_date']}</code>"
			]);
			break;
		}
		$TG->sendMsg([
			'text' => "Format error."
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
	&& strtolower(substr($text, 0, 4)) !== 'http') // Not start with HTTP or HTTPS
	$text = "https://$text"; // Prepend HTTPS scheme

/* Vaildate URL */
if (!preg_match('#^(?P<url>(?P<scheme>https?)://(?P<domain>[^\n\s@%/]+\.[^\n\s@%/]+)(?<path>/[^\n\s]*)?)(?:[\n\s]+(?P<code>[a-zA-Z0-9]+))?$#iu', $text, $matches)) {
	if ($TG->ChatID > 0) # Private Message
		$TG->sendMsg([
			'parse_mode' => 'HTML',
			'text' => $msg_help
		]);
	exit;
}

$scheme = $matches['scheme'];
$url = $matches['url'];
$domain = $matches['domain'];
$code = $matches['code'] ?? '';
$author = "TG{$TG->FromID}";

# php8.1-intl IDN function
if (idn_to_ascii($domain) !== $domain) {
	$domain = idn_to_ascii($domain);
	$path = $matches['path'] ?? '/';
	$url = "$scheme://$domain$path";
}

if (!filter_var($url, FILTER_VALIDATE_URL)) {
	$TG->sendMsg([
		'text' => 'Please Send a Vaild URL.'
	]);
	exit;
}

if (strtolower(substr($domain, -5)) == 'tg.pe') {
	$TG->sendMsg([
		'text' => 'This URL is short enough.'
	]);
	exit;
}


if (strlen($code) > 16) { /* Check Code Length */
	$TG->sendMsg([
		'text' => "Code too long"
	]);
	exit;
} else if (strlen($code) >= 3) { /* Check Code Existance */
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
			'text' => "Success!\n\nhttps://tg.pe/$code",
			'reply_markup' => [
				'inline_keyboard' => [
					[
						[
							'text' => 'Screen Message',
							'url' => "https://www.sean.taipei/sm#t=tg.pe%2F$code"
						]
					],
					[
						[
							'text' => 'QR code',
							'url' => "https://tg.pe/$code/qr"
						]
					]
				]
			]
		]);
		exit;
	} else
		$code = $db->allocateCode();
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
		'text' => "Hey! Please remove <b>fbclid</b> before sharing URLs.\n\n" .
		"fbclid is used for Facebook interaction tracking against user privacy.\n\n" .
		"You can install this <a href='https://addons.mozilla.org/en-US/firefox/addon/facebook-tracking-removal/'>add-on</a> to <b>auto remove</b> Facebook tracking ID.",
		'reply_markup' => [
			'inline_keyboard' => [
				[
					[
						'text' => 'Firefox',
						'url' => 'https://addons.mozilla.org/en-US/firefox/addon/facebook-tracking-removal/'
					],
					[
						'text' => 'Chrome',
						'url' => 'https://chrome.google.com/webstore/detail/tracking-ad-removal-for-f/ldeofbdmhnnocclkaddcnamhbhanaiaj'
					]
				]
			]
		]
	]);
	exit;
}
/* Both $url and $code should be clean */

if ($db->isUserBanned($author)) {
	$TG->sendMsg([
		'parse_mode' => 'Markdown',
		'text' => '*You have been banned.*',
	]);
	exit;
}

if (preg_match('/(' . implode('|', $domain_blacklist) . ')$/i', $domain)) {
	$prev_cnt = count($db->findByAuthor($author));
	$TG->sendMsg([
		'chat_id' => TG_ADMINS[0],
		'parse_mode' => 'HTML',
		'text' => "Warning: blacklisted URL (prev_cnt=$prev_cnt)\n\n" .
			"URL: $url\n" .
			"Author: #$author (@{$TG->data['message']['from']['username']})",
		'link_preview_options' => [
			'url' => $url,
			'prefer_small_media' => true,
		],
	]);
	sleep(1);
	$TG->sendMsg([
		'parse_mode' => 'Markdown',
		'text' => 'Creation failed.',
	]);
	exit;
}


/* Create Record */
$error = $db->insertCode($code, $url, $author);

if ($error[0] === '00000') {
	$TG->sendMsg([
		'text' => "Success!\n\nhttps://tg.pe/$code",
		'reply_markup' => [
			'inline_keyboard' => [
				[
					[
						'text' => 'Screen Message',
						'url' => "https://www.sean.taipei/sm#t=tg.pe%2F$code"
					]
				],
				[
					[
						'text' => 'QR code',
						'url' => "https://tg.pe/$code/qr"
					]
				]
			]
		]
	]);

	$cnt = count($db->findByAuthor($author));  // Prev count + this one

	if (preg_match('/(' . implode('|', $domain_warnlist) . ')$/i', $domain))
		$TG->sendMsg([
			'chat_id' => TG_ADMINS[0],
			'parse_mode' => 'HTML',
			'text' => "Warning: high-risk URL (cnt=$cnt)\n\n" .
				"URL: $url\n" .
				"Code: <code>$code</code>\n" .
				"Author: #$author (@{$TG->data['message']['from']['username']})",
			'link_preview_options' => [
				'url' => $url,
				'prefer_small_media' => true,
			],
		]);
	else if ($cnt <= 3)
		$TG->sendMsg([
			'chat_id' => TG_ADMINS[0],
			'parse_mode' => 'HTML',
			'text' => "Notice: New user create their first {$cnt} links\n\n" .
				"URL: $url\n" .
				"Code: <code>$code</code>\n" .
				"Author: #$author (@{$TG->data['message']['from']['username']})",
			'link_preview_options' => [
				'url' => $url,
				'prefer_small_media' => true,
			],
		]);
} else {
	$TG->sendMsg([
		'chat_id' => TG_ADMINS[0],
		'text' => "ERROR: Something went Wrong:\n\n" .
		"URL: $url\n" .
		"Code: $code\n" .
		"Author: #$author (@{$TG->data['message']['from']['username']})\n\n" .
		"PDO Error Info:\n" .
		json_encode($error, JSON_PRETTY_PRINT)
	]);

	$TG->sendMsg([
		'text' => "ERROR: Something went Wrong, please contact @S_ean\n\n" .
		"URL: $url\n" .
		"Code: $code\n" .
		"Author: $author\n\n" .
		"PDO Error Info:\n" .
		json_encode($error, JSON_PRETTY_PRINT)
	]);
}
