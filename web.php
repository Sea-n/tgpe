<?php
if (isset($_POST['url'])) {
	require('database.php');
	$db = new MyDB();
	$error = []; // Default no error

	$url = (string) $_POST['url'];
	if ($code = $db->findCodeByUrl($url))
		$error[] = "Already Exists."; // Prevent re-create


	$ip_addr = $_SERVER['REMOTE_ADDR'];
	$author = "WEB{$ip_addr}{$_SERVER["HTTP_CF_IPCOUNTRY"]}";
	$data = $db->findByAuthor($author);
	if ($_SERVER["HTTP_CF_IPCOUNTRY"] != 'TW' && count($data) >= 1) {
		$error[] = "You can only create 1 links in web version";
	}

	if ($_SERVER["HTTP_CF_IPCOUNTRY"] == 'TW' && count($data) >= 3) {
		$last = strtotime(end($data)['created_at']);
		if (time() - $last <= 10 * 60)
			$error[] = "You can only create 3 links in web version";
	}


	if (!preg_match('#^https?://(?P<domain>[^\n\s@%/]+\.[^\n\s@%/]+)(?:/[^\n\s]*)?$#i', $url, $matches))
		$error[] = "Please send a Vaild URL.";

	if (!filter_var($url, FILTER_VALIDATE_URL))
		$error[] = "URL invalid.";

	if (strpos($url, "fbclid="))
		$error[] = "Please remove fbclid before sharing URLs.";

	if (strpos($ip_addr, ':') !== false) {
		if (count($error) === 0 && $_SERVER["HTTP_CF_IPCOUNTRY"] != 'TW')
			$error[] = 'IPv6 source address is not supported except for Taiwan.';
	} else {  # IPv4
		$long = ip2long($ip_addr);
		$ipv4_blacklist = [
			['5.62.0.0',      '5.62.63.255'    ],  # AS198605 (Avast)
			['23.19.0.0',     '23.19.255.255'  ],  # AS395954 (LeaseWeb)
			['37.120.0.0',    '37.120.255.255' ],  # AS9009 (GlobalAX)
			['41.248.0.0',    '41.252.255.255' ],  # AS36903 (Maroc telecom)
			['45.91.20.0',    '45.91.23.255'   ],  # AS9009 (GlobalAX)
			['45.248.0.0',    '45.248.255.255' ],  # AS136557 (Host Universal)
			['54.39.0.0',     '54.39.255.255'  ],  # AS16276 (OVH)
			['78.108.176.0',  '78.108.191.255' ],  # AS62160
			['82.80.16.0',    '82.80.31.255'   ],  # AS8551
			['82.102.16.0',   '82.102.31.255'  ],  # AS9009 (GlobalAX)
			['84.17.32.0',    '84.17.63.255'   ],  # AS60068 (CDN77)
			['89.41.26.0',    '89.41.26.255'   ],  # AS9009 (GlobalAX)
			['89.187.160.0',  '89.187.191.255' ],  # AS60068 (CDN77)
			['91.132.136.0',  '91.132.139.255' ],  # AS9009 (GlobalAX)
			['92.118.13.0',   '92.118.13.255'  ],  # AS29066
			['102.100.0.0',   '102.103.255.255'],  # AS36925
			['102.136.0.0',   '102.139.255.255'],  # Alain
			['105.128.0.0',   '105.159.255.255'],  # AS36903 (Maroc telecom)
			['105.235.0.0',   '105.235.255.255'],
			['108.61.0.0',    '108.61.255.255' ],  # Vultr
			['114.79.0.0',    '114.79.63.255'  ],  # AS18004 (Wireless Indonesia)
			['129.205.113.0', '129.205.113.255'],
			['138.199.0.0',   '138.199.62.255' ],  # AS212238 (CDN77)
			['143.245.0.0',   '143.245.255.255'],  # AS60068 (CDN77)
			['154.0.23.0',    '154.0.28.255'   ],  # Rodrigue
			['154.16.0.0',    '154.16.241.255' ],  # heficed
			['156.146.58.0',  '156.146.59.255' ],
			['156.232.0.0',   '156.235.255.255'],  # mtnci
			['157.230.0.0',   '157.230.255.255'],  # AS14061 (DigitalOcean)
			['157.245.0.0',   '157.245.255.255'],  # AS14061 (DigitalOcean)
			['160.120.0.0',   '160.120.255.255'],  # Orange
			['160.154.0.0',   '160.159.255.255'],  # Orange
			['176.67.80.0',   '176.67.87.255'  ],
			['180.240.0.0',   '180.254.255.255'],  # AS17974 (Telkom Indonesia)
			['185.54.228.0',  '185.54.228.255' ],
			['185.230.124.0', '185.230.127.255'],  # AS9009 (GlobalAX)
			['185.232.20.0',  '185.232.23.255' ],  # AS9009 (GlobalAX)
			['185.246.208.0', '185.246.211.255'],  # AS60068 (CDN77)
			['188.126.64.0',  '188.126.95.255' ],  # AS42708 (Portlan)
			['192.241.128.0', '192.241.255.255'],
			['193.9.112.0',   '193.9.115.255'  ],
			['193.176.84.0',  '193.176.87.255' ],
			['196.74.0.0',    '196.74.255.255' ],
			['197.211.58.0',  '197.211.58.255' ],
			['200.25.0.0',    '200.25.127.255' ],  # AS7195 (EdgeUno)
			['206.189.0.0',   '206.189.255.255'],  # AS14061 (DigitalOcean)
			['213.87.0.0',    '213.87.255.255' ],  # MTS Net
		];
		foreach ($ipv4_blacklist as $item)
			if (ip2long($item[0]) <= $long && $long <= ip2long($item[1]))
				$error[] = "Your IP address is banned by admin. ({$item[0]} - {$item[1]})";
	}

	$domain = $matches['domain'] ?? 'url broken';
	if (preg_match('/(' . implode('|', [
		'tg.pe',
		'cf',
		'ga',
		'gq',
		'ml',
		'tk',
		's.id',
		'u.to',
		'6f.sk',
		'g6.cz',
		'gg.gg',
		'is.gd',
		'ml.tc',
		'sl.al',
		'vo.la',
		'bit.ly',
		'btc.do',
		'goo.io',
		'had.wf',
		'han.gl',
		'lrl.kr',
		'mzf.cz',
		'twr.kr',
		'cutt.ly',
		'cutt.us',
		'ism.run',
		'itiy.in',
		'kutt.it',
		'lite.al',
		'risu.io',
		'urlr.me',
		'urlz.fr',
		'web.app',
		'xsph.ru',
		'applk.io',
		'curto.me',
		'tmweb.ru',
		'reurl.cc',
		'ogtrk.net',
		'page.link',
		'swtest.ru',
		'appIe.link',
		'flazio.com',
		'onelink.me',
		'rebrand.ly',
		'soft24.net',
		'weebly.com',
		'adayline.ru',
		'tinyurl.com',
		'webador.com',
		'wixsite.com',
		'yolasite.com',
		'bigappboi.com',
		'moonfruit.com',
		'firebaseapp.com',
		'pantheonsite.io',
		'godaddysites.com',
		'sites.google.com',
		'funnylove.monster',
		'coralandherb.com.au',
	]) . ')$/i', $domain))
		$error[] = 'Domain have been banned.';

	// AbuseIPDB
	if (count($error) === 0 && $_SERVER["HTTP_CF_IPCOUNTRY"] != 'TW') {
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api.abuseipdb.com/api/v2/check?ipAddress={$ip_addr}",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				'Key: ' . ABUSEIPDB_KEY,
			],
		]);
		$abuseipdb = json_decode(curl_exec($curl), true);
		curl_close($curl);

		if ($abuseipdb['data']['abuseConfidenceScore'] ?? 0 > 75) {
			$error[] = 'Your IP address is in the AbuseIPDB.';
		}
		error_log("ip_addr={$ip_addr}, abuseConfidenceScore={$abuseipdb['data']['abuseConfidenceScore']}");
	}


	if (count($error) === 0) {
		$code = $db->allocateCode('x', 4);
		$result = $db->insert($code, $url, $author);
		if ($result[0] !== '00000')
			$error[] = $result[2];
	}
} ?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>tg.pe URL Shortener by.Sean</title>
	<link rel="icon" type="image/png" href="/logo-192.png" sizes="192x192">
	<link rel="icon" type="image/png" href="/logo-128.png" sizes="128x128">
	<link rel="icon" type="image/png" href="/logo-64.png" sizes="64x64">
	<link rel="icon" type="image/png" href="/logo.png" sizes="680x680">
	<link rel="stylesheet" href="style.css" />
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
<body>
<center>
<div class="content">
	<img src="logo_boderless.png" style="height: 40vh; margin-top: 40px;">
	<h1>URL Shortener</h1>
	<h2>Shorten Your URL: <a href="https://tg.pe/bot">tg.pe/bot</a></h2>

	<div id="gen">
		<big>Limited Online Version</big>

<?php
if (!isset($_POST['url'])) {
	echo <<<EOF
		<form method="POST" action="/web">
			<p>Your URL:<br>
			<span class='input'>
				<input name="url" id="url" size="30" placeholder="https://www.sean.taipei/">
				<span></span>
			</span>
			<br>
			<span style="color: darkgray;">Custom Short Link: https://tg.pe/<input name="code" size="4" disabled="1" placeholder="x123"><br>
			<button class="button" type="submit">Shorten!</button>
			</p>
		</form>

		<script>
			var url = document.getElementById("url");
			url.focus();
		</script>
EOF;
} else if (!empty($code)) {
	echo <<<EOF
<p>Your Link: <input id="link" value="https://tg.pe/$code" size="14"><button id="copyButton" onclick="copyLink()">Copy</button></p>
<script>
function copyLink() {
	var copyText = document.getElementById("link");
	copyText.select();
	copyText.setSelectionRange(0, 99);
	document.execCommand("copy");

	var copyButton = document.getElementById("copyButton");
	copyButton.innerHTML = "Copied!";
	copyText.setSelectionRange(0, 0);

	setTimeout(() => {
		copyButton.innerHTML = "Copy";
	}, 2000);
}
</script>
EOF;
} else if (count($error)) {
	echo <<<EOF
<p style='color: red;'>ERROR: {$error[0]}</p>
<p>Goto <a href='/'>Homepage</a>.</p>
EOF;
}
?>
		<small>Note: Online version only allow random short link starts with <code>x</code>.<br>
		Use Telegram Bot to get unlimited access for free.</small>
	</div>
	<br>
</div>
<div class="footer">
	<footer id="footer">
		<p>Source Code: <a href="https://github.com/Sea-n/tgpe">Sea-n/tgpe</a><br>
		Developed by <a href="https://www.sean.taipei/">Sean</a>.</p>
	</footer>
</div>
</center>
</body>
</html>
