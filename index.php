<?php
require_once('database.php');
require_once('safety.php');

$uri = $_SERVER['REQUEST_URI']; // $_SERVER['REQUEST_URI'] = string(5) "/path?query=xx"

$path = preg_replace('#\?.*#', '', $uri);

if (preg_match("#fbclid=#", $uri)) {
	header("Location: $path");
	exit("Removing fbclid...");
}

if ($path == '/') { // index homepage
	include('web.php');
	exit;
}

if ($path == '/show-demo') { // design playground (noindex)
	include('show-demo.php');
	exit;
}

if (!preg_match('#^/([\w_-]+)(\.([a-z]+))?(/(qr|show))?$#', $path, $matches1))
	error(400, 'Link invalid');

$code = $matches1[1];
$ext = $matches1[3] ?? '';
$action = $matches1[5] ?? '';

/* /xxx/qr is the legacy QR-only page; redirect to /xxx/show which now
   contains a richer QR + URL display. */
if ($action === 'qr') {
	header("Location: /$code/show", true, 301);
	exit;
}

# Retrive $code from database
$db = new MyDB();

if (!$data = $db->findByCode($code))
	error(404, 'Link not found');

$url = $data['url'];
if ($url == 'https://tg.pe/') {
	error(404, 'Page removed');
}

# Safety re-check for links not verified within 7 days
$lastCheck = $data['last_safety_check_at'];
if ($lastCheck === null || strtotime($lastCheck) < time() - 7 * 86400) {
	$threatTypes = findSafeBrowsingThreats($url);
	if ($threatTypes !== null && count($threatTypes) > 0) {
		$db->deleteBySafetyCheck($code);
		$reasons = implode(', ', formatSafeBrowsingReasons($threatTypes));
		error_log("safety_recheck_blocked: code={$code}, url={$url}, threats=" . implode('|', $threatTypes));
		notifyAdmin(
			"Warning: safety re-check blocked URL\n\n" .
			"Code: <code>{$code}</code>\n" .
			"URL: {$url}\n" .
			"Reason: <code>{$reasons}</code>"
		);
		error(404, 'Link not found');
	} else if ($threatTypes !== null) {
		$db->updateLastSafetyCheckAt($code);
	}
	// null = timeout or API failure → fail-open, last_safety_check_at unchanged
}

if ($action !== 'show')
	$db->incrementClickCount($code);

# For crawlers and /lnk.png pages, skip the HTML page
if (preg_match("#(TelegramBot|TwitterBot|PlurkBot|facebookexternalhit|ZXing|okhttp|jptt|Mo PTT|curl|Wget)#i", $_SERVER['HTTP_USER_AGENT'] ?? '')
	|| (substr($url, -strlen($ext)) == $ext && in_array($ext, ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'webp']))) {
	header("Location: $url");
	exit;
}

if ($action == 'show') {
	include('show.php');
	exit;
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
if (!in_array($code, ['bot', 'dev', 'repo']))
	echo '<meta name="robots" content="noindex,nosnippet">';
?>
</head>
<body>
	<p>Redirecting to <a id="url" href="<?= $url ?>"><?= $url ?></a>....</p>
	<script>
		window.onload = function() {
			var target = document.getElementById('url').href;
			window.location.replace(target);
		}
	</script>
</body>
</html>
<?php

function notifyAdmin(string $text): void {
	if (!defined('TG_BOT_TOKEN') || TG_BOT_TOKEN === '')
		return;
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://api.telegram.org/bot' . TG_BOT_TOKEN . '/sendMessage',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => json_encode([
			'chat_id' => TG_ADMINS[0],
			'parse_mode' => 'HTML',
			'text' => $text,
		]),
		CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
		CURLOPT_TIMEOUT => 5,
	]);
	curl_exec($curl);
	curl_close($curl);
}

function error(int $code, string $msg) {
	switch ($code) {
	case 400:
		header('HTTP/2 400 Bad Request');
		break;
	case 404:
		header("HTTP/2 404 Not Found");
		break;
	}

	if (strlen($_SERVER['HTTP_USER_AGENT'] ?? '') < 20)
		exit($msg); // Message Only for bots

	echo <<<EOF
<html>
<head>
	<meta http-equiv="refresh" content="5;url=https://tg.pe/" />
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
