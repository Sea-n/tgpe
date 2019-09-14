<?php
if (isset($_POST['url'])) {
	require('database.php');
	$db = new MyDB();
	$error = []; // Default no error

	$url = (string) $_POST['url'];
	if ($code = $db->findCodeByUrl($url))
		$error[] = "Already Exists."; // Prevent re-create


	if (!check_cf_ip($_SERVER['REMOTE_ADDR'] ?? '1.1.1.1'))
		$error[] = "Please don't hack me";

	$author = "WEB{$_SERVER['HTTP_CF_CONNECTING_IP']}{$_SERVER["HTTP_CF_IPCOUNTRY"]}";
	$data = $db->findByAuthor($author);
	if (count($data) >= 3)
		$error[] = "You can only create 3 links in web version";


	if (!preg_match('#^https?://(?P<domain>[^\n\s@%/]+\.[^\n\s@%/]+)(?:/[^\n\s]*)?$#i', $url, $matches))
		$error[] = "Please send a Vaild URL.";

	if (!filter_var($url, FILTER_VALIDATE_URL))
		$error[] = "URL invalid.";

	if (strpos($url, "fbclid="))
		$error[] = "Please remove fbclid before sharing URLs.";

	$domain = $matches['domain'] ?? 'url broken';
	if (strtolower(substr($domain, -5)) == 'tg.pe')
		$error[] = 'Short enough';


	if (count($error) === 0) {
		$code = $db->allocateCode('x', 4);
		$result = $db->insert($code, $url, $author);
		if ($result[0] !== '00000')
			$error[] = $result[2];
	}
} ?>
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>tg.pe URL Shortener by.Sean</title>
	<link rel="icon" type="image/png" href="/logo-192.png" sizes="192x192">
	<link rel="icon" type="image/png" href="/logo-128.png" sizes="128x128">
	<link rel="icon" type="image/png" href="/logo-64.png" sizes="64x64">
	<link rel="icon" type="image/png" href="/logo.png" sizes="680x680">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta name="keywords" content="url shortener, tgpe">
	<meta name="description" content="Shortest Shortener">
	<meta property="og:title" content="tg.pe URL Shortener">
	<meta property="og:url" content="https://tg.pe/">
	<meta property="og:image" content="/logo.png">
	<meta property="og:image:secure_url" content="/logo.png">
	<meta property="og:image:type" content="image/png">
	<meta property="og:image:width" content="680">
	<meta property="og:image:height" content="680">
	<meta property="og:type" content="website">
	<meta property="og:description" content="Shortest Shortener">
	<meta property="og:site_name" content="URL Shortener by.Sean">
</head>
<center><img src="logo.png" style="height: 40vh; margin-top: 40px;">
	<h1>URL Shortener</h1>
	<h2>Shorten Your URL: <a href="https://tg.pe/bot">tg.pe/bot</a></h2>
	<div id="gen">
		<big>Limited Online Version</big>
<?php if (!isset($_POST['url'])) { ?>
		<form method="POST" action="/web">
			<p>Your URL: <input name="url" size="30" placeholder="https://www.sean.taipei/"><br>
			<span style="color: darkgray;">Custom Short Link: https://tg.pe/<input name="code" size="4" disabled="1" placeholder="x123"><br>
			<input type="submit" value="Shorten!"></p>
		</form>
<?php }
	else if (!empty($code))
		echo "<p>Your Link: <a href='https://tg.pe/$code' target='_blank'>https://tg.pe/$code</a></p>";

	else if (count($error))
		echo "<p style='color: red;'>ERROR: {$error[0]}</p>";
?>
		<small>Note: Online version only allow random short link starts with <code>x</code>.<br>
		Use Telegram Bot to unlimited access.</small>
	</div>
	<br>

	<footer>
		<p>Source Code: <a href="https://github.com/Sea-n/tgpe">Sea-n/tgpe</a><br>
		Developed by <a href="https://www.sean.taipei/">Sean</a>.</p>
	</footer>

	<style>
		body {
			font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
			line-height: 1.4;
		}
		a {
			color: #337ab7;
			text-decoration: none;
		}
		footer {
			padding: 10px 15px;
			background-color: #f5f5f5;
			border-top: 1px solid #ddd;
			border-bottom-right-radius: 3px;
			border-bottom-left-radius: 3px;
		}
	</style>
</center>
<?php
function check_cf_ip(string $addr) {
	$range = [
		"173.245.48.0" => 20,
		"103.21.244.0" => 22,
		"103.22.200.0" => 22,
		"103.31.4.0" => 22,
		"141.101.64.0" => 18,
		"108.162.192.0" => 18,
		"190.93.240.0" => 20,
		"188.114.96.0" => 20,
		"197.234.240.0" => 22,
		"198.41.128.0" => 17,
		"162.158.0.0" => 15,
		"104.16.0.0" => 12,
		"172.64.0.0" => 13,
		"131.0.72.0" => 22
	];

	foreach ($range as $base => $cidr)
		if (ip2long($addr)>>(32-$cidr)
		=== ip2long($base)>>(32-$cidr))
			return true;

	return false;
}
