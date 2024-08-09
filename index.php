<?php
require_once('database.php');

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

if (!preg_match('#^/([\w_-]+)(\.([a-z]+))?(/(qr))?$#', $path, $matches1))
	error(400, 'Link invalid');

$code = $matches1[1];
$ext = $matches1[3];
$qr = $matches1[5];

# Retrive $code from database
$db = new MyDB();

if (!$data = $db->findByCode($code))
	error(404, 'Link not found');

$url = $data['url'];
if ($url == 'https://tg.pe/') {
	error(404, 'Page removed');
}

# For crawlers and /lnk.png pages, skip the HTML page
if (preg_match("#(TelegramBot|TwitterBot|PlurkBot|facebookexternalhit|ZXing|okhttp|jptt|Mo PTT|curl|Wget)#i", $_SERVER['HTTP_USER_AGENT'] ?? '')
	|| (substr($url, -strlen($ext)) == $ext && in_array($ext, ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'webp']))) {
	header("Location: $url");
	exit;
}

if ($qr == 'qr') {  # For /lnk/qr page
	echo <<<EOF
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nosnippet">
	<style>
		body {
			background-color: #f8f8f8;
		}
		img.qr {
			display: block;
			width: auto;
			height: auto;
			max-width: 98vw;
			max-height: 98vh;
			margin: auto;
		}
	</style>
</head>
<body>
	<img class="qr" src="https://quickchart.io/qr?text=https://tg.pe/$code&size=1200&captionFontSize=72&ecLevel=L&caption=https://tg.pe/$code&captionFontFamily=Courier+New">
</body>
</html>
EOF;
	exit();
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
